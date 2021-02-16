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

use const PHP_OS;
use const PHP_VERSION;
use function addcslashes;
use function array_flip;
use function array_key_exists;
use function array_merge;
use function array_unique;
use function array_unshift;
use function assert;
use function class_exists;
use function count;
use function explode;
use function extension_loaded;
use function function_exists;
use function get_class;
use function ini_get;
use function interface_exists;
use function is_array;
use function is_int;
use function method_exists;
use function phpversion;
use function preg_match;
use function preg_replace;
use function rtrim;
use function sprintf;
use function str_replace;
use function strpos;
use function strtolower;
use function substr;
use function trim;
use function version_compare;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\CodeCoverageException;
use PHPUnit\Framework\ExecutionOrderDependency;
use PHPUnit\Framework\InvalidCoversTargetException;
use PHPUnit\Framework\InvalidDataProviderException;
use PHPUnit\Framework\SelfDescribing;
use PHPUnit\Framework\SkippedTestError;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSize\TestSize;
use PHPUnit\Framework\Warning;
use PHPUnit\Runner\Version;
use PHPUnit\Util\Metadata\Annotation\Registry as AnnotationRegistry;
use PHPUnit\Util\Metadata\Covers;
use PHPUnit\Util\Metadata\CoversClass;
use PHPUnit\Util\Metadata\CoversFunction;
use PHPUnit\Util\Metadata\CoversMethod;
use PHPUnit\Util\Metadata\DataProvider;
use PHPUnit\Util\Metadata\Group;
use PHPUnit\Util\Metadata\Metadata;
use PHPUnit\Util\Metadata\MetadataCollection;
use PHPUnit\Util\Metadata\Registry as MetadataRegistry;
use PHPUnit\Util\Metadata\TestWith;
use PHPUnit\Util\Metadata\Uses;
use PHPUnit\Util\Metadata\UsesClass;
use PHPUnit\Util\Metadata\UsesFunction;
use PHPUnit\Util\Metadata\UsesMethod;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use SebastianBergmann\CodeUnit\CodeUnitCollection;
use SebastianBergmann\CodeUnit\InvalidCodeUnitException;
use SebastianBergmann\CodeUnit\Mapper;
use SebastianBergmann\Environment\OperatingSystem;
use Traversable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Test
{
    private static array $hookMethods = [];

    /**
     * @psalm-return array{0: string, 1: string}
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public static function describe(\PHPUnit\Framework\Test $test): array
    {
        if ($test instanceof TestCase) {
            return [get_class($test), $test->getName()];
        }

        if ($test instanceof SelfDescribing) {
            return ['', $test->toString()];
        }

        return ['', get_class($test)];
    }

    public static function describeAsString(\PHPUnit\Framework\Test $test): string
    {
        if ($test instanceof SelfDescribing) {
            return $test->toString();
        }

        return get_class($test);
    }

    /**
     * @throws CodeCoverageException
     *
     * @return array|bool
     * @psalm-param class-string $className
     *
     * @todo Avoid calling this method for methods that do not exist
     */
    public static function linesToBeCovered(string $className, string $methodName)
    {
        if (!self::shouldCodeCoverageBeCollectedFor($className, $methodName)) {
            return false;
        }

        $metadataForClass  = MetadataRegistry::reader()->forClass($className);
        $metadataForMethod = MetadataRegistry::reader()->forMethod($className, $methodName);
        $classShortcut     = null;

        if ($metadataForClass->isCoversDefaultClass()->isNotEmpty()) {
            if (count($metadataForClass->isCoversDefaultClass()) > 1) {
                throw new CodeCoverageException(
                    sprintf(
                        'More than one @coversDefaultClass annotation for class or interface "%s"',
                        $className
                    )
                );
            }

            $classShortcut = $metadataForClass->isCoversDefaultClass()->asArray()[0]->className();
        }

        $codeUnits = CodeUnitCollection::fromArray([]);
        $mapper    = new Mapper;

        foreach ($metadataForClass->mergeWith($metadataForMethod) as $metadata) {
            if ($metadata->isCoversClass() || $metadata->isCoversMethod() || $metadata->isCoversFunction()) {
                assert($metadata instanceof CoversClass || $metadata instanceof CoversMethod || $metadata instanceof CoversFunction);

                try {
                    $codeUnits = $codeUnits->mergeWith($mapper->stringToCodeUnits($metadata->asStringForCodeUnitMapper()));
                } catch (InvalidCodeUnitException $e) {
                    if ($metadata->isCoversClass()) {
                        $type = 'Class';
                    } elseif ($metadata->isCoversMethod()) {
                        $type = 'Method';
                    } else {
                        $type = 'Function';
                    }

                    throw new InvalidCoversTargetException(
                        sprintf(
                            '%s "%s" is not a valid target for code coverage',
                            $type,
                            $metadata->asStringForCodeUnitMapper()
                        ),
                        (int) $e->getCode(),
                        $e
                    );
                }
            } elseif ($metadata->isCovers()) {
                assert($metadata instanceof Covers);

                $target = $metadata->target();

                if (interface_exists($target)) {
                    throw new InvalidCoversTargetException(
                        sprintf(
                            'Trying to @cover interface "%s".',
                            $target
                        )
                    );
                }

                if ($classShortcut !== null && strpos($target, '::') === 0) {
                    $target = $classShortcut . $target;
                }

                try {
                    $codeUnits = $codeUnits->mergeWith($mapper->stringToCodeUnits($target));
                } catch (InvalidCodeUnitException $e) {
                    throw new InvalidCoversTargetException(
                        sprintf(
                            '"@covers %s" is invalid',
                            $target
                        ),
                        (int) $e->getCode(),
                        $e
                    );
                }
            }
        }

        return $mapper->codeUnitsToSourceLines($codeUnits);
    }

    /**
     * Returns lines of code specified with the @uses annotation.
     *
     * @throws CodeCoverageException
     * @psalm-param class-string $className
     *
     * @todo Avoid calling this method for methods that do not exist
     */
    public static function linesToBeUsed(string $className, string $methodName): array
    {
        $metadataForClass  = MetadataRegistry::reader()->forClass($className);
        $metadataForMethod = MetadataRegistry::reader()->forMethod($className, $methodName);
        $classShortcut     = null;

        if ($metadataForClass->isUsesDefaultClass()->isNotEmpty()) {
            if (count($metadataForClass->isUsesDefaultClass()) > 1) {
                throw new CodeCoverageException(
                    sprintf(
                        'More than one @usesDefaultClass annotation for class or interface "%s"',
                        $className
                    )
                );
            }

            $classShortcut = $metadataForClass->isUsesDefaultClass()->asArray()[0]->className();
        }

        $codeUnits = CodeUnitCollection::fromArray([]);
        $mapper    = new Mapper;

        foreach ($metadataForClass->mergeWith($metadataForMethod) as $metadata) {
            if ($metadata->isUsesClass() || $metadata->isUsesMethod() || $metadata->isUsesFunction()) {
                assert($metadata instanceof UsesClass || $metadata instanceof UsesMethod || $metadata instanceof UsesFunction);

                try {
                    $codeUnits = $codeUnits->mergeWith($mapper->stringToCodeUnits($metadata->asStringForCodeUnitMapper()));
                } catch (InvalidCodeUnitException $e) {
                    if ($metadata->isUsesClass()) {
                        $type = 'Class';
                    } elseif ($metadata->isUsesMethod()) {
                        $type = 'Method';
                    } else {
                        $type = 'Function';
                    }

                    throw new InvalidCoversTargetException(
                        sprintf(
                            '%s "%s" is not a valid target for code coverage',
                            $type,
                            $metadata->asStringForCodeUnitMapper()
                        ),
                        (int) $e->getCode(),
                        $e
                    );
                }
            } elseif ($metadata->isUses()) {
                assert($metadata instanceof Uses);

                $target = $metadata->target();

                if ($classShortcut !== null && strpos($target, '::') === 0) {
                    $target = $classShortcut . $target;
                }

                try {
                    $codeUnits = $codeUnits->mergeWith($mapper->stringToCodeUnits($target));
                } catch (InvalidCodeUnitException $e) {
                    throw new InvalidCoversTargetException(
                        sprintf(
                            '"@uses %s" is invalid',
                            $target
                        ),
                        (int) $e->getCode(),
                        $e
                    );
                }
            }
        }

        return $mapper->codeUnitsToSourceLines($codeUnits);
    }

    /**
     * @psalm-param class-string $className
     *
     * @todo Avoid calling this method for methods that do not exist
     */
    public static function shouldCodeCoverageBeCollectedFor(string $className, string $methodName): bool
    {
        $metadataForClass  = MetadataRegistry::reader()->forClass($className);
        $metadataForMethod = MetadataRegistry::reader()->forMethod($className, $methodName);

        // If there is no @covers annotation but a @coversNothing annotation on
        // the test method then code coverage data does not need to be collected
        if ($metadataForMethod->isCoversNothing()->isNotEmpty()) {
            return false;
        }

        // If there is at least one @covers annotation then
        // code coverage data needs to be collected
        if ($metadataForMethod->isCovers()->isNotEmpty() ||
            $metadataForMethod->isCoversClass()->isNotEmpty() ||
            $metadataForMethod->isCoversMethod()->isNotEmpty() ||
            $metadataForMethod->isCoversFunction()->isNotEmpty()) {
            return true;
        }

        // If there is no @covers annotation but a @coversNothing annotation
        // then code coverage data does not need to be collected
        if ($metadataForClass->isCoversNothing()->isNotEmpty()) {
            return false;
        }

        // If there is no @coversNothing annotation then
        // code coverage data may be collected
        return true;
    }

    /**
     * Returns the missing requirements for a test.
     *
     * @throws Exception
     * @throws Warning
     * @psalm-param class-string $className
     */
    public static function getMissingRequirements(string $className, string $methodName): array
    {
        $required = self::mergeArraysRecursively(
            AnnotationRegistry::getInstance()->forClassName($className)->requirements(),
            AnnotationRegistry::getInstance()->forMethod($className, $methodName)->requirements()
        );

        $missing = [];
        $hint    = null;

        if (!empty($required['PHP'])) {
            $operator = new VersionComparisonOperator(empty($required['PHP']['operator']) ? '>=' : $required['PHP']['operator']);

            if (!version_compare(PHP_VERSION, $required['PHP']['version'], $operator->asString())) {
                $missing[] = sprintf('PHP %s %s is required.', $operator->asString(), $required['PHP']['version']);
                $hint      = 'PHP';
            }
        } elseif (!empty($required['PHP_constraint'])) {
            $version = new \PharIo\Version\Version(self::sanitizeVersionNumber(PHP_VERSION));

            if (!$required['PHP_constraint']['constraint']->complies($version)) {
                $missing[] = sprintf(
                    'PHP version does not match the required constraint %s.',
                    $required['PHP_constraint']['constraint']->asString()
                );

                $hint = 'PHP_constraint';
            }
        }

        if (!empty($required['PHPUnit'])) {
            $phpunitVersion = Version::id();

            $operator = new VersionComparisonOperator(empty($required['PHPUnit']['operator']) ? '>=' : $required['PHPUnit']['operator']);

            if (!version_compare($phpunitVersion, $required['PHPUnit']['version'], $operator->asString())) {
                $missing[] = sprintf('PHPUnit %s %s is required.', $operator->asString(), $required['PHPUnit']['version']);
                $hint      = $hint ?? 'PHPUnit';
            }
        } elseif (!empty($required['PHPUnit_constraint'])) {
            $phpunitVersion = new \PharIo\Version\Version(self::sanitizeVersionNumber(Version::id()));

            if (!$required['PHPUnit_constraint']['constraint']->complies($phpunitVersion)) {
                $missing[] = sprintf(
                    'PHPUnit version does not match the required constraint %s.',
                    $required['PHPUnit_constraint']['constraint']->asString()
                );

                $hint = $hint ?? 'PHPUnit_constraint';
            }
        }

        if (!empty($required['OSFAMILY']) && $required['OSFAMILY'] !== (new OperatingSystem)->getFamily()) {
            $missing[] = sprintf('Operating system %s is required.', $required['OSFAMILY']);
            $hint      = $hint ?? 'OSFAMILY';
        }

        if (!empty($required['OS'])) {
            $requiredOsPattern = sprintf('/%s/i', addcslashes($required['OS'], '/'));

            if (!preg_match($requiredOsPattern, PHP_OS)) {
                $missing[] = sprintf('Operating system matching %s is required.', $requiredOsPattern);
                $hint      = $hint ?? 'OS';
            }
        }

        if (!empty($required['functions'])) {
            foreach ($required['functions'] as $function) {
                $pieces = explode('::', $function);

                if (count($pieces) === 2 && class_exists($pieces[0]) && method_exists($pieces[0], $pieces[1])) {
                    continue;
                }

                if (function_exists($function)) {
                    continue;
                }

                $missing[] = sprintf('Function %s is required.', $function);
                $hint      = $hint ?? 'function_' . $function;
            }
        }

        if (!empty($required['setting'])) {
            foreach ($required['setting'] as $setting => $value) {
                if (ini_get($setting) !== $value) {
                    $missing[] = sprintf('Setting "%s" must be "%s".', $setting, $value);
                    $hint      = $hint ?? '__SETTING_' . $setting;
                }
            }
        }

        if (!empty($required['extensions'])) {
            foreach ($required['extensions'] as $extension) {
                if (isset($required['extension_versions'][$extension])) {
                    continue;
                }

                if (!extension_loaded($extension)) {
                    $missing[] = sprintf('Extension %s is required.', $extension);
                    $hint      = $hint ?? 'extension_' . $extension;
                }
            }
        }

        if (!empty($required['extension_versions'])) {
            foreach ($required['extension_versions'] as $extension => $req) {
                $actualVersion = phpversion($extension);

                $operator = new VersionComparisonOperator(empty($req['operator']) ? '>=' : $req['operator']);

                if ($actualVersion === false || !version_compare($actualVersion, $req['version'], $operator->asString())) {
                    $missing[] = sprintf('Extension %s %s %s is required.', $extension, $operator->asString(), $req['version']);
                    $hint      = $hint ?? 'extension_' . $extension;
                }
            }
        }

        if ($hint && isset($required['__OFFSET'])) {
            array_unshift($missing, '__OFFSET_FILE=' . $required['__OFFSET']['__FILE']);
            array_unshift($missing, '__OFFSET_LINE=' . ($required['__OFFSET'][$hint] ?? 1));
        }

        return $missing;
    }

    /**
     * @psalm-param class-string $className
     *
     * @throws Exception
     */
    public static function providedData(string $className, string $methodName): ?array
    {
        $dataProvider = MetadataRegistry::reader()->forMethod($className, $methodName)->isDataProvider();
        $testWith     = MetadataRegistry::reader()->forMethod($className, $methodName)->isTestWith();

        if ($dataProvider->isEmpty() && $testWith->isEmpty()) {
            return self::dataProvidedByTestWithAnnotation($className, $methodName);
        }

        if ($dataProvider->isNotEmpty()) {
            $data = self::dataProvidedByMethods($dataProvider);
        } else {
            $data = self::dataProvidedByMetadata($testWith);
        }

        if ($data === []) {
            throw new SkippedTestError(
                'Skipped due to empty data set provided by data provider'
            );
        }

        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                throw new InvalidDataSetException(
                    sprintf(
                        'Data set %s is invalid.',
                        is_int($key) ? '#' . $key : '"' . $key . '"'
                    )
                );
            }
        }

        return $data;
    }

    /**
     * @psalm-param class-string $className
     */
    public static function parseTestMethodAnnotations(string $className, ?string $methodName = ''): array
    {
        $registry = AnnotationRegistry::getInstance();

        if ($methodName !== null) {
            try {
                return [
                    'method' => $registry->forMethod($className, $methodName)->symbolAnnotations(),
                    'class'  => $registry->forClassName($className)->symbolAnnotations(),
                ];
            } catch (Exception $methodNotFound) {
                // ignored
            }
        }

        return [
            'method' => null,
            'class'  => $registry->forClassName($className)->symbolAnnotations(),
        ];
    }

    /**
     * @psalm-param class-string $className
     *
     * @return ExecutionOrderDependency[]
     */
    public static function getDependencies(string $className, string $methodName): array
    {
        $annotations = self::parseTestMethodAnnotations(
            $className,
            $methodName
        );

        $dependsAnnotations = $annotations['class']['depends'] ?? [];

        if (isset($annotations['method']['depends'])) {
            $dependsAnnotations = array_merge(
                $dependsAnnotations,
                $annotations['method']['depends']
            );
        }

        // Normalize dependency name to className::methodName
        $dependencies = [];

        foreach ($dependsAnnotations as $value) {
            $dependencies[] = ExecutionOrderDependency::createFromDependsAnnotation($className, $value);
        }

        return array_unique($dependencies);
    }

    /**
     * @psalm-param class-string $className
     */
    public static function groups(string $className, ?string $methodName = ''): array
    {
        $metadataForClass  = MetadataRegistry::reader()->forClass($className);
        $metadataForMethod = MetadataRegistry::reader()->forMethod($className, $methodName);
        $groups            = [];

        foreach ($metadataForClass->mergeWith($metadataForMethod) as $metadata) {
            assert($metadata instanceof Metadata);

            if ($metadata->isGroup()) {
                assert($metadata instanceof Group);

                $groups[] = $metadata->groupName();
            }

            if ($metadata->isCoversClass() || $metadata->isCoversMethod() || $metadata->isCoversFunction()) {
                assert($metadata instanceof CoversClass || $metadata instanceof CoversMethod || $metadata instanceof CoversFunction);

                $groups[] = '__phpunit_covers_' . self::canonicalizeName($metadata->asStringForCodeUnitMapper());
            }

            if ($metadata->isCovers()) {
                assert($metadata instanceof Covers);

                $groups[] = '__phpunit_covers_' . self::canonicalizeName($metadata->target());
            }

            if ($metadata->isUsesClass() || $metadata->isUsesMethod() || $metadata->isUsesFunction()) {
                assert($metadata instanceof UsesClass || $metadata instanceof UsesMethod || $metadata instanceof UsesFunction);

                $groups[] = '__phpunit_uses_' . self::canonicalizeName($metadata->asStringForCodeUnitMapper());
            }

            if ($metadata->isUses()) {
                assert($metadata instanceof Uses);

                $groups[] = '__phpunit_uses_' . self::canonicalizeName($metadata->target());
            }
        }

        return array_unique($groups);
    }

    /**
     * @psalm-param class-string $className
     */
    public static function size(string $className, ?string $methodName): TestSize
    {
        $groups = array_flip(self::groups($className, $methodName));

        if (isset($groups['large'])) {
            return TestSize::large();
        }

        if (isset($groups['medium'])) {
            return TestSize::medium();
        }

        if (isset($groups['small'])) {
            return TestSize::small();
        }

        return TestSize::unknown();
    }

    /**
     * @psalm-param class-string $className
     */
    public static function hookMethods(string $className): array
    {
        if (!class_exists($className, false)) {
            return self::emptyHookMethodsArray();
        }

        if (isset(self::$hookMethods[$className])) {
            return self::$hookMethods[$className];
        }

        self::$hookMethods[$className] = self::emptyHookMethodsArray();

        try {
            foreach ((new ReflectionClass($className))->getMethods() as $method) {
                if ($method->getDeclaringClass()->getName() === Assert::class) {
                    continue;
                }

                if ($method->getDeclaringClass()->getName() === TestCase::class) {
                    continue;
                }

                $metadata = MetadataRegistry::reader()->forMethod($className, $method->getName());

                if ($method->isStatic()) {
                    if ($metadata->isBeforeClass()->isNotEmpty()) {
                        array_unshift(
                            self::$hookMethods[$className]['beforeClass'],
                            $method->getName()
                        );
                    }

                    if ($metadata->isAfterClass()->isNotEmpty()) {
                        self::$hookMethods[$className]['afterClass'][] = $method->getName();
                    }
                }

                if ($metadata->isBefore()->isNotEmpty()) {
                    array_unshift(
                        self::$hookMethods[$className]['before'],
                        $method->getName()
                    );
                }

                if ($metadata->isPreCondition()->isNotEmpty()) {
                    array_unshift(
                        self::$hookMethods[$className]['preCondition'],
                        $method->getName()
                    );
                }

                if ($metadata->isPostCondition()->isNotEmpty()) {
                    self::$hookMethods[$className]['postCondition'][] = $method->getName();
                }

                if ($metadata->isAfter()->isNotEmpty()) {
                    self::$hookMethods[$className]['after'][] = $method->getName();
                }
            }
        } catch (ReflectionException $e) {
        }

        return self::$hookMethods[$className];
    }

    public static function isTestMethod(ReflectionMethod $method): bool
    {
        if (!$method->isPublic()) {
            return false;
        }

        if (strpos($method->getName(), 'test') === 0) {
            return true;
        }

        $metadata = MetadataRegistry::reader()->forMethod(
            $method->getDeclaringClass()->getName(),
            $method->getName()
        );

        return $metadata->isTest()->isNotEmpty();
    }

    private static function emptyHookMethodsArray(): array
    {
        return [
            'beforeClass'   => ['setUpBeforeClass'],
            'before'        => ['setUp'],
            'preCondition'  => ['assertPreConditions'],
            'postCondition' => ['assertPostConditions'],
            'after'         => ['tearDown'],
            'afterClass'    => ['tearDownAfterClass'],
        ];
    }

    /**
     * Trims any extensions from version string that follows after
     * the <major>.<minor>[.<patch>] format.
     */
    private static function sanitizeVersionNumber(string $version)
    {
        return preg_replace(
            '/^(\d+\.\d+(?:.\d+)?).*$/',
            '$1',
            $version
        );
    }

    /**
     * Merge two arrays together.
     *
     * If an integer key exists in both arrays and preserveNumericKeys is false, the value
     * from the second array will be appended to the first array. If both values are arrays, they
     * are merged together, else the value of the second array overwrites the one of the first array.
     *
     * This implementation is copied from https://github.com/zendframework/zend-stdlib/blob/76b653c5e99b40eccf5966e3122c90615134ae46/src/ArrayUtils.php
     *
     * Zend Framework (http://framework.zend.com/)
     *
     * @see       http://github.com/zendframework/zf2 for the canonical source repository
     *
     * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
     * @license   http://framework.zend.com/license/new-bsd New BSD License
     */
    private static function mergeArraysRecursively(array $a, array $b): array
    {
        foreach ($b as $key => $value) {
            if (array_key_exists($key, $a)) {
                if (is_int($key)) {
                    $a[] = $value;
                } elseif (is_array($value) && is_array($a[$key])) {
                    $a[$key] = self::mergeArraysRecursively($a[$key], $value);
                } else {
                    $a[$key] = $value;
                }
            } else {
                $a[$key] = $value;
            }
        }

        return $a;
    }

    private static function canonicalizeName(string $name): string
    {
        return strtolower(trim($name, '\\'));
    }

    private static function dataProvidedByMethods(MetadataCollection $dataProvider): array
    {
        $result = [];

        foreach ($dataProvider as $_dataProvider) {
            assert($_dataProvider instanceof DataProvider);

            try {
                $class  = new ReflectionClass($_dataProvider->className());
                $method = $class->getMethod($_dataProvider->methodName());
                // @codeCoverageIgnoreStart
            } catch (ReflectionException $e) {
                throw new Exception(
                    $e->getMessage(),
                    (int) $e->getCode(),
                    $e
                );
                // @codeCoverageIgnoreEnd
            }

            if ($method->isStatic()) {
                $object = null;
            } else {
                $object = $class->newInstance();
            }

            if ($method->getNumberOfParameters() === 0) {
                $data = $method->invoke($object);
            } else {
                $data = $method->invoke($object, $_dataProvider->methodName());
            }

            if ($data instanceof Traversable) {
                $origData = $data;
                $data     = [];

                foreach ($origData as $key => $value) {
                    if (is_int($key)) {
                        $data[] = $value;
                    } elseif (array_key_exists($key, $data)) {
                        throw new InvalidDataProviderException(
                            sprintf(
                                'The key "%s" has already been defined by a previous data provider',
                                $key,
                            )
                        );
                    } else {
                        $data[$key] = $value;
                    }
                }
            }

            if (is_array($data)) {
                $result = array_merge($result, $data);
            }
        }

        return $result;
    }

    private static function dataProvidedByMetadata(MetadataCollection $testWith): array
    {
        $result = [];

        foreach ($testWith as $_testWith) {
            assert($_testWith instanceof TestWith);

            $result[] = $_testWith->data();
        }

        return $result;
    }

    /**
     * @psalm-param class-string $className
     */
    private static function dataProvidedByTestWithAnnotation(string $className, string $methodName): ?array
    {
        $docComment = (new ReflectionMethod($className, $methodName))->getDocComment();

        if (!$docComment) {
            return null;
        }

        $docComment = str_replace("\r\n", "\n", $docComment);
        $docComment = preg_replace('/' . '\n' . '\s*' . '\*' . '\s?' . '/', "\n", $docComment);
        $docComment = (string) substr($docComment, 0, -1);
        $docComment = rtrim($docComment, "\n");

        if (!preg_match('/@testWith\s+/', $docComment, $matches, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $offset            = strlen($matches[0][0]) + $matches[0][1];
        $annotationContent = substr($docComment, $offset);
        $data              = [];

        foreach (explode("\n", $annotationContent) as $candidateRow) {
            $candidateRow = trim($candidateRow);

            if ($candidateRow[0] !== '[') {
                break;
            }

            $dataSet = json_decode($candidateRow, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception(
                    'The data set for the @testWith annotation cannot be parsed: ' . json_last_error_msg()
                );
            }

            $data[] = $dataSet;
        }

        if (!$data) {
            throw new Exception('The data set for the @testWith annotation cannot be parsed.');
        }

        return $data;
    }
}
