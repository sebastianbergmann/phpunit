<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use PharIo\Version\VersionConstraintParser;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\CodeCoverageException;
use PHPUnit\Framework\InvalidCoversTargetException;
use PHPUnit\Framework\InvalidDataProviderException;
use PHPUnit\Framework\SelfDescribing;
use PHPUnit\Framework\SkippedTestError;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Warning;
use PHPUnit\Runner\Version;
use SebastianBergmann\Environment\OperatingSystem;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Test
{
    /**
     * @var int
     */
    public const UNKNOWN = -1;

    /**
     * @var int
     */
    public const SMALL = 0;

    /**
     * @var int
     */
    public const MEDIUM = 1;

    /**
     * @var int
     */
    public const LARGE = 2;

    /**
     * @var string
     *
     * @todo This constant should be private (it's public because of TestTest::testGetProvidedDataRegEx)
     */
    public const REGEX_DATA_PROVIDER = '/@dataProvider\s+([a-zA-Z0-9._:-\\\\x7f-\xff]+)/';

    /**
     * @var string
     */
    private const REGEX_TEST_WITH = '/@testWith\s+/';

    /**
     * @var string
     */
    private const REGEX_EXPECTED_EXCEPTION = '(@expectedException\s+([:.\w\\\\x7f-\xff]+)(?:[\t ]+(\S*))?(?:[\t ]+(\S*))?\s*$)m';

    /**
     * @var string
     */
    private const REGEX_REQUIRES_VERSION = '/@requires\s+(?P<name>PHP(?:Unit)?)\s+(?P<operator>[<>=!]{0,2})\s*(?P<version>[\d\.-]+(dev|(RC|alpha|beta)[\d\.])?)[ \t]*\r?$/m';

    /**
     * @var string
     */
    private const REGEX_REQUIRES_VERSION_CONSTRAINT = '/@requires\s+(?P<name>PHP(?:Unit)?)\s+(?P<constraint>[\d\t \-.|~^]+)[ \t]*\r?$/m';

    /**
     * @var string
     */
    private const REGEX_REQUIRES_OS = '/@requires\s+(?P<name>OS(?:FAMILY)?)\s+(?P<value>.+?)[ \t]*\r?$/m';

    /**
     * @var string
     */
    private const REGEX_REQUIRES_SETTING = '/@requires\s+(?P<name>setting)\s+(?P<setting>([^ ]+?))\s*(?P<value>[\w\.-]+[\w\.]?)?[ \t]*\r?$/m';

    /**
     * @var string
     */
    private const REGEX_REQUIRES = '/@requires\s+(?P<name>function|extension)\s+(?P<value>([^\s<>=!]+))\s*(?P<operator>[<>=!]{0,2})\s*(?P<version>[\d\.-]+[\d\.]?)?[ \t]*\r?$/m';

    /**
     * @var array
     */
    private static $annotationCache = [];

    /**
     * @var array
     */
    private static $hookMethods = [];

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public static function describe(\PHPUnit\Framework\Test $test): array
    {
        if ($test instanceof TestCase) {
            return [\get_class($test), $test->getName()];
        }

        if ($test instanceof SelfDescribing) {
            return ['', $test->toString()];
        }

        return ['', \get_class($test)];
    }

    public static function describeAsString(\PHPUnit\Framework\Test $test): string
    {
        if ($test instanceof SelfDescribing) {
            return $test->toString();
        }

        return \get_class($test);
    }

    /**
     * @throws CodeCoverageException
     *
     * @return array|bool
     */
    public static function getLinesToBeCovered(string $className, string $methodName)
    {
        $annotations = self::parseTestMethodAnnotations(
            $className,
            $methodName
        );

        if (!self::shouldCoversAnnotationBeUsed($annotations)) {
            return false;
        }

        return self::getLinesToBeCoveredOrUsed($className, $methodName, 'covers');
    }

    /**
     * Returns lines of code specified with the @uses annotation.
     *
     * @throws CodeCoverageException
     */
    public static function getLinesToBeUsed(string $className, string $methodName): array
    {
        return self::getLinesToBeCoveredOrUsed($className, $methodName, 'uses');
    }

    public static function requiresCodeCoverageDataCollection(TestCase $test): bool
    {
        $annotations = $test->getAnnotations();

        // If there is no @covers annotation but a @coversNothing annotation on
        // the test method then code coverage data does not need to be collected
        if (isset($annotations['method']['coversNothing'])) {
            return false;
        }

        // If there is at least one @covers annotation then
        // code coverage data needs to be collected
        if (isset($annotations['method']['covers'])) {
            return true;
        }

        // If there is no @covers annotation but a @coversNothing annotation
        // then code coverage data does not need to be collected
        if (isset($annotations['class']['coversNothing'])) {
            return false;
        }

        // If there is no @coversNothing annotation then
        // code coverage data may be collected
        return true;
    }

    /**
     * @throws Exception
     */
    public static function getRequirements(string $className, string $methodName): array
    {
        try {
            $reflector = new \ReflectionClass($className);
        } catch (\ReflectionException $e) {
            throw new Exception(
                $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }

        $requires   = [
            '__OFFSET' => [
                '__FILE' => \realpath($reflector->getFileName()),
            ],
        ];

        $requires = self::parseRequirements((string) $reflector->getDocComment(), $reflector->getStartLine(), $requires);

        try {
            $reflector = new \ReflectionMethod($className, $methodName);
        } catch (\ReflectionException $e) {
            throw new Exception(
                $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }

        return self::parseRequirements((string) $reflector->getDocComment(), $reflector->getStartLine(), $requires);
    }

    public static function parseRequirements(string $docComment, int $offset = 0, array $requires = []): array
    {
        // Split docblock into lines and rewind offset to start of docblock
        $lines = \preg_split('/\r\n|\r|\n/', $docComment);
        $offset -= \count($lines);

        foreach ($lines as $line) {
            if (\preg_match(self::REGEX_REQUIRES_OS, $line, $matches)) {
                $requires[$matches['name']]             = $matches['value'];
                $requires['__OFFSET'][$matches['name']] = $offset;
            }

            if (\preg_match(self::REGEX_REQUIRES_VERSION, $line, $matches)) {
                $requires[$matches['name']] = [
                    'version'  => $matches['version'],
                    'operator' => $matches['operator'],
                ];
                $requires['__OFFSET'][$matches['name']] = $offset;
            }

            if (\preg_match(self::REGEX_REQUIRES_VERSION_CONSTRAINT, $line, $matches)) {
                if (!empty($requires[$matches['name']])) {
                    $offset++;

                    continue;
                }

                try {
                    $versionConstraintParser = new VersionConstraintParser;

                    $requires[$matches['name'] . '_constraint'] = [
                        'constraint' => $versionConstraintParser->parse(\trim($matches['constraint'])),
                    ];
                    $requires['__OFFSET'][$matches['name'] . '_constraint'] = $offset;
                } catch (\PharIo\Version\Exception $e) {
                    throw new Warning($e->getMessage(), $e->getCode(), $e);
                }
            }

            if (\preg_match(self::REGEX_REQUIRES_SETTING, $line, $matches)) {
                if (!isset($requires['setting'])) {
                    $requires['setting'] = [];
                }
                $requires['setting'][$matches['setting']]                 = $matches['value'];
                $requires['__OFFSET']['__SETTING_' . $matches['setting']] = $offset;
            }

            if (\preg_match(self::REGEX_REQUIRES, $line, $matches)) {
                $name = $matches['name'] . 's';

                if (!isset($requires[$name])) {
                    $requires[$name] = [];
                }

                $requires[$name][]                                                = $matches['value'];
                $requires['__OFFSET'][$matches['name'] . '_' . $matches['value']] = $offset;

                if ($name === 'extensions' && !empty($matches['version'])) {
                    $requires['extension_versions'][$matches['value']] = [
                        'version'  => $matches['version'],
                        'operator' => $matches['operator'],
                    ];
                }
            }

            $offset++;
        }

        return $requires;
    }

    /**
     * Returns the missing requirements for a test.
     *
     * @throws Warning
     */
    public static function getMissingRequirements(string $className, string $methodName): array
    {
        $required = static::getRequirements($className, $methodName);
        $missing  = [];
        $hint     = null;

        if (!empty($required['PHP'])) {
            $operator = empty($required['PHP']['operator']) ? '>=' : $required['PHP']['operator'];

            if (!\version_compare(\PHP_VERSION, $required['PHP']['version'], $operator)) {
                $missing[] = \sprintf('PHP %s %s is required.', $operator, $required['PHP']['version']);
                $hint      = $hint ?? 'PHP';
            }
        } elseif (!empty($required['PHP_constraint'])) {
            $version = new \PharIo\Version\Version(self::sanitizeVersionNumber(\PHP_VERSION));

            if (!$required['PHP_constraint']['constraint']->complies($version)) {
                $missing[] = \sprintf(
                    'PHP version does not match the required constraint %s.',
                    $required['PHP_constraint']['constraint']->asString()
                );
                $hint = $hint ?? 'PHP_constraint';
            }
        }

        if (!empty($required['PHPUnit'])) {
            $phpunitVersion = Version::id();

            $operator = empty($required['PHPUnit']['operator']) ? '>=' : $required['PHPUnit']['operator'];

            if (!\version_compare($phpunitVersion, $required['PHPUnit']['version'], $operator)) {
                $missing[] = \sprintf('PHPUnit %s %s is required.', $operator, $required['PHPUnit']['version']);
                $hint      = $hint ?? 'PHPUnit';
            }
        } elseif (!empty($required['PHPUnit_constraint'])) {
            $phpunitVersion = new \PharIo\Version\Version(self::sanitizeVersionNumber(Version::id()));

            if (!$required['PHPUnit_constraint']['constraint']->complies($phpunitVersion)) {
                $missing[] = \sprintf(
                    'PHPUnit version does not match the required constraint %s.',
                    $required['PHPUnit_constraint']['constraint']->asString()
                );
                $hint = $hint ?? 'PHPUnit_constraint';
            }
        }

        if (!empty($required['OSFAMILY']) && $required['OSFAMILY'] !== (new OperatingSystem)->getFamily()) {
            $missing[] = \sprintf('Operating system %s is required.', $required['OSFAMILY']);
            $hint      = $hint ?? 'OSFAMILY';
        }

        if (!empty($required['OS'])) {
            $requiredOsPattern = \sprintf('/%s/i', \addcslashes($required['OS'], '/'));

            if (!\preg_match($requiredOsPattern, \PHP_OS)) {
                $missing[] = \sprintf('Operating system matching %s is required.', $requiredOsPattern);
                $hint      = $hint ?? 'OS';
            }
        }

        if (!empty($required['functions'])) {
            foreach ($required['functions'] as $function) {
                $pieces = \explode('::', $function);

                if (\count($pieces) === 2 && \method_exists($pieces[0], $pieces[1])) {
                    continue;
                }

                if (\function_exists($function)) {
                    continue;
                }

                $missing[] = \sprintf('Function %s is required.', $function);
                $hint      = $hint ?? 'function_' . $function;
            }
        }

        if (!empty($required['setting'])) {
            foreach ($required['setting'] as $setting => $value) {
                if (\ini_get($setting) != $value) {
                    $missing[] = \sprintf('Setting "%s" must be "%s".', $setting, $value);
                    $hint      = $hint ?? '__SETTING_' . $setting;
                }
            }
        }

        if (!empty($required['extensions'])) {
            foreach ($required['extensions'] as $extension) {
                if (isset($required['extension_versions'][$extension])) {
                    continue;
                }

                if (!\extension_loaded($extension)) {
                    $missing[] = \sprintf('Extension %s is required.', $extension);
                    $hint      = $hint ?? 'extension_' . $extension;
                }
            }
        }

        if (!empty($required['extension_versions'])) {
            foreach ($required['extension_versions'] as $extension => $req) {
                $actualVersion = \phpversion($extension);

                $operator = empty($req['operator']) ? '>=' : $req['operator'];

                if ($actualVersion === false || !\version_compare($actualVersion, $req['version'], $operator)) {
                    $missing[] = \sprintf('Extension %s %s %s is required.', $extension, $operator, $req['version']);
                    $hint      = $hint ?? 'extension_' . $extension;
                }
            }
        }

        if ($hint && isset($required['__OFFSET'])) {
            \array_unshift($missing, '__OFFSET_FILE=' . $required['__OFFSET']['__FILE']);
            \array_unshift($missing, '__OFFSET_LINE=' . $required['__OFFSET'][$hint] ?? 1);
        }

        return $missing;
    }

    /**
     * Returns the expected exception for a test.
     *
     * @return array|false
     *
     * @deprecated
     * @codeCoverageIgnore
     */
    public static function getExpectedException(string $className, string $methodName)
    {
        try {
            $reflector = new \ReflectionMethod($className, $methodName);
        } catch (\ReflectionException $e) {
            throw new Exception(
                $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }

        $docComment = (string) $reflector->getDocComment();
        $docComment = (string) \substr($docComment, 3, -2);

        if (\preg_match(self::REGEX_EXPECTED_EXCEPTION, $docComment, $matches)) {
            $annotations = self::parseTestMethodAnnotations(
                $className,
                $methodName
            );

            $class         = $matches[1];
            $code          = null;
            $message       = '';
            $messageRegExp = '';

            if (isset($matches[2])) {
                $message = \trim($matches[2]);
            } elseif (isset($annotations['method']['expectedExceptionMessage'])) {
                $message = self::parseAnnotationContent(
                    $annotations['method']['expectedExceptionMessage'][0]
                );
            }

            if (isset($annotations['method']['expectedExceptionMessageRegExp'])) {
                $messageRegExp = self::parseAnnotationContent(
                    $annotations['method']['expectedExceptionMessageRegExp'][0]
                );
            }

            if (isset($matches[3])) {
                $code = $matches[3];
            } elseif (isset($annotations['method']['expectedExceptionCode'])) {
                $code = self::parseAnnotationContent(
                    $annotations['method']['expectedExceptionCode'][0]
                );
            }

            if (\is_numeric($code)) {
                $code = (int) $code;
            } elseif (\is_string($code) && \defined($code)) {
                $code = (int) \constant($code);
            }

            return [
                'class' => $class, 'code' => $code, 'message' => $message, 'message_regex' => $messageRegExp,
            ];
        }

        return false;
    }

    /**
     * Returns the provided data for a method.
     *
     * @throws Exception
     */
    public static function getProvidedData(string $className, string $methodName): ?array
    {
        try {
            $reflector = new \ReflectionMethod($className, $methodName);
        } catch (\ReflectionException $e) {
            throw new Exception(
                $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }

        $docComment = (string) $reflector->getDocComment();

        $data = self::getDataFromDataProviderAnnotation($docComment, $className, $methodName);

        if ($data === null) {
            $data = self::getDataFromTestWithAnnotation($docComment);
        }

        if ($data === []) {
            throw new SkippedTestError;
        }

        if ($data !== null) {
            foreach ($data as $key => $value) {
                if (!\is_array($value)) {
                    throw new Exception(
                        \sprintf(
                            'Data set %s is invalid.',
                            \is_int($key) ? '#' . $key : '"' . $key . '"'
                        )
                    );
                }
            }
        }

        return $data;
    }

    /**
     * @throws Exception
     */
    public static function getDataFromTestWithAnnotation(string $docComment): ?array
    {
        $docComment = self::cleanUpMultiLineAnnotation($docComment);

        if (\preg_match(self::REGEX_TEST_WITH, $docComment, $matches, \PREG_OFFSET_CAPTURE)) {
            $offset            = \strlen($matches[0][0]) + $matches[0][1];
            $annotationContent = \substr($docComment, $offset);
            $data              = [];

            foreach (\explode("\n", $annotationContent) as $candidateRow) {
                $candidateRow = \trim($candidateRow);

                if ($candidateRow[0] !== '[') {
                    break;
                }

                $dataSet = \json_decode($candidateRow, true);

                if (\json_last_error() !== \JSON_ERROR_NONE) {
                    throw new Exception(
                        'The data set for the @testWith annotation cannot be parsed: ' . \json_last_error_msg()
                    );
                }

                $data[] = $dataSet;
            }

            if (!$data) {
                throw new Exception('The data set for the @testWith annotation cannot be parsed.');
            }

            return $data;
        }

        return null;
    }

    public static function parseTestMethodAnnotations(string $className, ?string $methodName = ''): array
    {
        if (!isset(self::$annotationCache[$className])) {
            try {
                $class = new \ReflectionClass($className);
            } catch (\ReflectionException $e) {
                throw new Exception(
                    $e->getMessage(),
                    (int) $e->getCode(),
                    $e
                );
            }

            $traits      = $class->getTraits();
            $annotations = [];

            foreach ($traits as $trait) {
                $annotations = \array_merge(
                    $annotations,
                    self::parseAnnotations((string) $trait->getDocComment())
                );
            }

            self::$annotationCache[$className] = \array_merge(
                $annotations,
                self::parseAnnotations((string) $class->getDocComment())
            );
        }

        $cacheKey = $className . '::' . $methodName;

        if ($methodName !== null && !isset(self::$annotationCache[$cacheKey])) {
            try {
                $method      = new \ReflectionMethod($className, $methodName);
                $annotations = self::parseAnnotations((string) $method->getDocComment());
            } catch (\ReflectionException $e) {
                $annotations = [];
            }

            self::$annotationCache[$cacheKey] = $annotations;
        }

        return [
            'class'  => self::$annotationCache[$className],
            'method' => $methodName !== null ? self::$annotationCache[$cacheKey] : [],
        ];
    }

    public static function getInlineAnnotations(string $className, string $methodName): array
    {
        try {
            $method = new \ReflectionMethod($className, $methodName);
        } catch (\ReflectionException $e) {
            throw new Exception(
                $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }

        $code        = \file($method->getFileName());
        $lineNumber  = $method->getStartLine();
        $startLine   = $method->getStartLine() - 1;
        $endLine     = $method->getEndLine() - 1;
        $methodLines = \array_slice($code, $startLine, $endLine - $startLine + 1);
        $annotations = [];

        foreach ($methodLines as $line) {
            if (\preg_match('#/\*\*?\s*@(?P<name>[A-Za-z_-]+)(?:[ \t]+(?P<value>.*?))?[ \t]*\r?\*/$#m', $line, $matches)) {
                $annotations[\strtolower($matches['name'])] = [
                    'line'  => $lineNumber,
                    'value' => $matches['value'],
                ];
            }

            $lineNumber++;
        }

        return $annotations;
    }

    public static function parseAnnotations(string $docBlock): array
    {
        $annotations = [];
        // Strip away the docblock header and footer to ease parsing of one line annotations
        $docBlock = (string) \substr($docBlock, 3, -2);

        if (\preg_match_all('/@(?P<name>[A-Za-z_-]+)(?:[ \t]+(?P<value>.*?))?[ \t]*\r?$/m', $docBlock, $matches)) {
            $numMatches = \count($matches[0]);

            for ($i = 0; $i < $numMatches; ++$i) {
                $annotations[$matches['name'][$i]][] = (string) $matches['value'][$i];
            }
        }

        return $annotations;
    }

    public static function getBackupSettings(string $className, string $methodName): array
    {
        return [
            'backupGlobals' => self::getBooleanAnnotationSetting(
                $className,
                $methodName,
                'backupGlobals'
            ),
            'backupStaticAttributes' => self::getBooleanAnnotationSetting(
                $className,
                $methodName,
                'backupStaticAttributes'
            ),
        ];
    }

    public static function getDependencies(string $className, string $methodName): array
    {
        $annotations = self::parseTestMethodAnnotations(
            $className,
            $methodName
        );

        $dependencies = $annotations['class']['depends'] ?? [];

        if (isset($annotations['method']['depends'])) {
            $dependencies = \array_merge(
                $dependencies,
                $annotations['method']['depends']
            );
        }

        return \array_unique($dependencies);
    }

    public static function getGroups(string $className, ?string $methodName = ''): array
    {
        $annotations = self::parseTestMethodAnnotations(
            $className,
            $methodName
        );

        $groups = [];

        if (isset($annotations['method']['author'])) {
            $groups = $annotations['method']['author'];
        } elseif (isset($annotations['class']['author'])) {
            $groups = $annotations['class']['author'];
        }

        if (isset($annotations['class']['group'])) {
            $groups = \array_merge($groups, $annotations['class']['group']);
        }

        if (isset($annotations['method']['group'])) {
            $groups = \array_merge($groups, $annotations['method']['group']);
        }

        if (isset($annotations['class']['ticket'])) {
            $groups = \array_merge($groups, $annotations['class']['ticket']);
        }

        if (isset($annotations['method']['ticket'])) {
            $groups = \array_merge($groups, $annotations['method']['ticket']);
        }

        foreach (['method', 'class'] as $element) {
            foreach (['small', 'medium', 'large'] as $size) {
                if (isset($annotations[$element][$size])) {
                    $groups[] = $size;

                    break 2;
                }
            }
        }

        return \array_unique($groups);
    }

    public static function getSize(string $className, ?string $methodName): int
    {
        $groups = \array_flip(self::getGroups($className, $methodName));

        if (isset($groups['large'])) {
            return self::LARGE;
        }

        if (isset($groups['medium'])) {
            return self::MEDIUM;
        }

        if (isset($groups['small'])) {
            return self::SMALL;
        }

        return self::UNKNOWN;
    }

    public static function getProcessIsolationSettings(string $className, string $methodName): bool
    {
        $annotations = self::parseTestMethodAnnotations(
            $className,
            $methodName
        );

        return isset($annotations['class']['runTestsInSeparateProcesses']) || isset($annotations['method']['runInSeparateProcess']);
    }

    public static function getClassProcessIsolationSettings(string $className, string $methodName): bool
    {
        $annotations = self::parseTestMethodAnnotations(
            $className,
            $methodName
        );

        return isset($annotations['class']['runClassInSeparateProcess']);
    }

    public static function getPreserveGlobalStateSettings(string $className, string $methodName): ?bool
    {
        return self::getBooleanAnnotationSetting(
            $className,
            $methodName,
            'preserveGlobalState'
        );
    }

    public static function getHookMethods(string $className): array
    {
        if (!\class_exists($className, false)) {
            return self::emptyHookMethodsArray();
        }

        if (!isset(self::$hookMethods[$className])) {
            self::$hookMethods[$className] = self::emptyHookMethodsArray();

            try {
                foreach ((new \ReflectionClass($className))->getMethods() as $method) {
                    if ($method->getDeclaringClass()->getName() === Assert::class) {
                        continue;
                    }

                    if ($method->getDeclaringClass()->getName() === TestCase::class) {
                        continue;
                    }

                    $methodComment = $method->getDocComment();

                    if ($methodComment) {
                        if ($method->isStatic()) {
                            if (\strpos($methodComment, '@beforeClass') !== false) {
                                \array_unshift(
                                    self::$hookMethods[$className]['beforeClass'],
                                    $method->getName()
                                );
                            }

                            if (\strpos($methodComment, '@afterClass') !== false) {
                                self::$hookMethods[$className]['afterClass'][] = $method->getName();
                            }
                        }

                        if (\preg_match('/@before\b/', $methodComment) > 0) {
                            \array_unshift(
                                self::$hookMethods[$className]['before'],
                                $method->getName()
                            );
                        }

                        if (\preg_match('/@after\b/', $methodComment) > 0) {
                            self::$hookMethods[$className]['after'][] = $method->getName();
                        }
                    }
                }
            } catch (\ReflectionException $e) {
            }
        }

        return self::$hookMethods[$className];
    }

    public static function isTestMethod(\ReflectionMethod $method): bool
    {
        if (\strpos($method->getName(), 'test') === 0) {
            return true;
        }

        $annotations = self::parseAnnotations((string) $method->getDocComment());

        return isset($annotations['test']);
    }

    /**
     * @throws CodeCoverageException
     */
    private static function getLinesToBeCoveredOrUsed(string $className, string $methodName, string $mode): array
    {
        $annotations = self::parseTestMethodAnnotations(
            $className,
            $methodName
        );

        $classShortcut = null;

        if (!empty($annotations['class'][$mode . 'DefaultClass'])) {
            if (\count($annotations['class'][$mode . 'DefaultClass']) > 1) {
                throw new CodeCoverageException(
                    \sprintf(
                        'More than one @%sClass annotation in class or interface "%s".',
                        $mode,
                        $className
                    )
                );
            }

            $classShortcut = $annotations['class'][$mode . 'DefaultClass'][0];
        }

        $list = $annotations['class'][$mode] ?? [];

        if (isset($annotations['method'][$mode])) {
            $list = \array_merge($list, $annotations['method'][$mode]);
        }

        $codeList = [];

        foreach (\array_unique($list) as $element) {
            if ($classShortcut && \strncmp($element, '::', 2) === 0) {
                $element = $classShortcut . $element;
            }

            $element = \preg_replace('/[\s()]+$/', '', $element);
            $element = \explode(' ', $element);
            $element = $element[0];

            if ($mode === 'covers' && \interface_exists($element)) {
                throw new InvalidCoversTargetException(
                    \sprintf(
                        'Trying to @cover interface "%s".',
                        $element
                    )
                );
            }

            $codeList = \array_merge(
                $codeList,
                self::resolveElementToReflectionObjects($element)
            );
        }

        return self::resolveReflectionObjectsToLines($codeList);
    }

    /**
     * Parse annotation content to use constant/class constant values
     *
     * Constants are specified using a starting '@'. For example: @ClassName::CONST_NAME
     *
     * If the constant is not found the string is used as is to ensure maximum BC.
     */
    private static function parseAnnotationContent(string $message): string
    {
        if (\defined($message) && (\strpos($message, '::') !== false && \substr_count($message, '::') + 1 === 2)) {
            $message = \constant($message);
        }

        return $message;
    }

    /**
     * @throws InvalidDataProviderException
     */
    private static function getDataFromDataProviderAnnotation(string $docComment, string $className, string $methodName): ?iterable
    {
        if (\preg_match_all(self::REGEX_DATA_PROVIDER, $docComment, $matches)) {
            $result = [];

            foreach ($matches[1] as $match) {
                $dataProviderMethodNameNamespace = \explode('\\', $match);
                $leaf                            = \explode('::', \array_pop($dataProviderMethodNameNamespace));
                $dataProviderMethodName          = \array_pop($leaf);

                if (empty($dataProviderMethodNameNamespace)) {
                    $dataProviderMethodNameNamespace = '';
                } else {
                    $dataProviderMethodNameNamespace = \implode('\\', $dataProviderMethodNameNamespace) . '\\';
                }

                if (empty($leaf)) {
                    $dataProviderClassName = $className;
                } else {
                    $dataProviderClassName = $dataProviderMethodNameNamespace . \array_pop($leaf);
                }

                try {
                    $dataProviderClass = new \ReflectionClass($dataProviderClassName);

                    $dataProviderMethod = $dataProviderClass->getMethod(
                        $dataProviderMethodName
                    );
                } catch (\ReflectionException $e) {
                    throw new Exception(
                        $e->getMessage(),
                        (int) $e->getCode(),
                        $e
                    );
                }

                if ($dataProviderMethod->isStatic()) {
                    $object = null;
                } else {
                    $object = $dataProviderClass->newInstance();
                }

                if ($dataProviderMethod->getNumberOfParameters() === 0) {
                    $data = $dataProviderMethod->invoke($object);
                } else {
                    $data = $dataProviderMethod->invoke($object, $methodName);
                }

                if ($data instanceof \Traversable) {
                    $origData = $data;
                    $data     = [];

                    foreach ($origData as $key => $value) {
                        if (\is_int($key)) {
                            $data[] = $value;
                        } elseif (\array_key_exists($key, $data)) {
                            throw new InvalidDataProviderException(
                                \sprintf(
                                    'The key "%s" has already been defined in the data provider "%s".',
                                    $key,
                                    $match
                                )
                            );
                        } else {
                            $data[$key] = $value;
                        }
                    }
                }

                if (\is_array($data)) {
                    $result = \array_merge($result, $data);
                }
            }

            return $result;
        }

        return null;
    }

    private static function cleanUpMultiLineAnnotation(string $docComment): string
    {
        //removing initial '   * ' for docComment
        $docComment = \str_replace("\r\n", "\n", $docComment);
        $docComment = \preg_replace('/' . '\n' . '\s*' . '\*' . '\s?' . '/', "\n", $docComment);
        $docComment = (string) \substr($docComment, 0, -1);

        return \rtrim($docComment, "\n");
    }

    private static function emptyHookMethodsArray(): array
    {
        return [
            'beforeClass' => ['setUpBeforeClass'],
            'before'      => ['setUp'],
            'after'       => ['tearDown'],
            'afterClass'  => ['tearDownAfterClass'],
        ];
    }

    private static function getBooleanAnnotationSetting(string $className, ?string $methodName, string $settingName): ?bool
    {
        $annotations = self::parseTestMethodAnnotations(
            $className,
            $methodName
        );

        if (isset($annotations['method'][$settingName])) {
            if ($annotations['method'][$settingName][0] === 'enabled') {
                return true;
            }

            if ($annotations['method'][$settingName][0] === 'disabled') {
                return false;
            }
        }

        if (isset($annotations['class'][$settingName])) {
            if ($annotations['class'][$settingName][0] === 'enabled') {
                return true;
            }

            if ($annotations['class'][$settingName][0] === 'disabled') {
                return false;
            }
        }

        return null;
    }

    /**
     * @throws InvalidCoversTargetException
     */
    private static function resolveElementToReflectionObjects(string $element): array
    {
        $codeToCoverList = [];

        if (\function_exists($element) && \strpos($element, '\\') !== false) {
            try {
                $codeToCoverList[] = new \ReflectionFunction($element);
            } catch (\ReflectionException $e) {
                throw new Exception(
                    $e->getMessage(),
                    (int) $e->getCode(),
                    $e
                );
            }
        } elseif (\strpos($element, '::') !== false) {
            [$className, $methodName] = \explode('::', $element);

            if (isset($methodName[0]) && $methodName[0] === '<') {
                $classes = [$className];

                foreach ($classes as $className) {
                    if (!\class_exists($className) &&
                        !\interface_exists($className) &&
                        !\trait_exists($className)) {
                        throw new InvalidCoversTargetException(
                            \sprintf(
                                'Trying to @cover or @use not existing class or ' .
                                'interface "%s".',
                                $className
                            )
                        );
                    }

                    try {
                        $methods = (new \ReflectionClass($className))->getMethods();
                    } catch (\ReflectionException $e) {
                        throw new Exception(
                            $e->getMessage(),
                            (int) $e->getCode(),
                            $e
                        );
                    }

                    $inverse    = isset($methodName[1]) && $methodName[1] === '!';
                    $visibility = 'isPublic';

                    if (\strpos($methodName, 'protected')) {
                        $visibility = 'isProtected';
                    } elseif (\strpos($methodName, 'private')) {
                        $visibility = 'isPrivate';
                    }

                    foreach ($methods as $method) {
                        if ($inverse && !$method->$visibility()) {
                            $codeToCoverList[] = $method;
                        } elseif (!$inverse && $method->$visibility()) {
                            $codeToCoverList[] = $method;
                        }
                    }
                }
            } else {
                $classes = [$className];

                foreach ($classes as $className) {
                    if ($className === '' && \function_exists($methodName)) {
                        try {
                            $codeToCoverList[] = new \ReflectionFunction(
                                $methodName
                            );
                        } catch (\ReflectionException $e) {
                            throw new Exception(
                                $e->getMessage(),
                                (int) $e->getCode(),
                                $e
                            );
                        }
                    } else {
                        if (!((\class_exists($className) || \interface_exists($className) || \trait_exists($className)) &&
                            \method_exists($className, $methodName))) {
                            throw new InvalidCoversTargetException(
                                \sprintf(
                                    'Trying to @cover or @use not existing method "%s::%s".',
                                    $className,
                                    $methodName
                                )
                            );
                        }

                        try {
                            $codeToCoverList[] = new \ReflectionMethod(
                                $className,
                                $methodName
                            );
                        } catch (\ReflectionException $e) {
                            throw new Exception(
                                $e->getMessage(),
                                (int) $e->getCode(),
                                $e
                            );
                        }
                    }
                }
            }
        } else {
            $extended = false;

            if (\strpos($element, '<extended>') !== false) {
                $element  = \str_replace('<extended>', '', $element);
                $extended = true;
            }

            $classes = [$element];

            if ($extended) {
                $classes = \array_merge(
                    $classes,
                    \class_implements($element),
                    \class_parents($element)
                );
            }

            foreach ($classes as $className) {
                if (!\class_exists($className) &&
                    !\interface_exists($className) &&
                    !\trait_exists($className)) {
                    throw new InvalidCoversTargetException(
                        \sprintf(
                            'Trying to @cover or @use not existing class or ' .
                            'interface "%s".',
                            $className
                        )
                    );
                }

                try {
                    $codeToCoverList[] = new \ReflectionClass($className);
                } catch (\ReflectionException $e) {
                    throw new Exception(
                        $e->getMessage(),
                        (int) $e->getCode(),
                        $e
                    );
                }
            }
        }

        return $codeToCoverList;
    }

    private static function resolveReflectionObjectsToLines(array $reflectors): array
    {
        $result = [];

        foreach ($reflectors as $reflector) {
            if ($reflector instanceof \ReflectionClass) {
                foreach ($reflector->getTraits() as $trait) {
                    $reflectors[] = $trait;
                }
            }
        }

        foreach ($reflectors as $reflector) {
            $filename = $reflector->getFileName();

            if (!isset($result[$filename])) {
                $result[$filename] = [];
            }

            $result[$filename] = \array_merge(
                $result[$filename],
                \range($reflector->getStartLine(), $reflector->getEndLine())
            );
        }

        foreach ($result as $filename => $lineNumbers) {
            $result[$filename] = \array_keys(\array_flip($lineNumbers));
        }

        return $result;
    }

    /**
     * Trims any extensions from version string that follows after
     * the <major>.<minor>[.<patch>] format
     */
    private static function sanitizeVersionNumber(string $version)
    {
        return \preg_replace(
            '/^(\d+\.\d+(?:.\d+)?).*$/',
            '$1',
            $version
        );
    }

    private static function shouldCoversAnnotationBeUsed(array $annotations): bool
    {
        if (isset($annotations['method']['coversNothing'])) {
            return false;
        }

        if (isset($annotations['method']['covers'])) {
            return true;
        }

        if (isset($annotations['class']['coversNothing'])) {
            return false;
        }

        return true;
    }
}
