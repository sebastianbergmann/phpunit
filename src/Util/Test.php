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
use function sprintf;
use function strncmp;
use function strpos;
use function strtolower;
use function trim;
use function version_compare;
use PHPUnit\Framework\CodeCoverageException;
use PHPUnit\Framework\ExecutionOrderDependency;
use PHPUnit\Framework\InvalidCoversTargetException;
use PHPUnit\Framework\SelfDescribing;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Warning;
use PHPUnit\Runner\Version;
use PHPUnit\Util\Annotation\Registry;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use SebastianBergmann\CodeUnit\CodeUnitCollection;
use SebastianBergmann\CodeUnit\InvalidCodeUnitException;
use SebastianBergmann\CodeUnit\Mapper;
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
     * @var array
     */
    private static $hookMethods = [];

    /**
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
     *
     * @psalm-param class-string $className
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
     *
     * @psalm-param class-string $className
     */
    public static function getLinesToBeUsed(string $className, string $methodName): array
    {
        return self::getLinesToBeCoveredOrUsed($className, $methodName, 'uses');
    }

    public static function requiresCodeCoverageDataCollection(TestCase $test): bool
    {
        $annotations = self::parseTestMethodAnnotations(
            get_class($test),
            $test->getName(false)
        );

        // If there is no @covers annotation but a @coversNothing annotation on
        // the test method then code coverage data does not need to be collected
        if (isset($annotations['method']['coversNothing'])) {
            // @see https://github.com/sebastianbergmann/phpunit/issues/4947#issuecomment-1084480950
            // return false;
        }

        // If there is at least one @covers annotation then
        // code coverage data needs to be collected
        if (isset($annotations['method']['covers'])) {
            return true;
        }

        // If there is no @covers annotation but a @coversNothing annotation
        // then code coverage data does not need to be collected
        if (isset($annotations['class']['coversNothing'])) {
            // @see https://github.com/sebastianbergmann/phpunit/issues/4947#issuecomment-1084480950
            // return false;
        }

        // If there is no @coversNothing annotation then
        // code coverage data may be collected
        return true;
    }

    /**
     * @throws Exception
     *
     * @psalm-param class-string $className
     */
    public static function getRequirements(string $className, string $methodName): array
    {
        return self::mergeArraysRecursively(
            Registry::getInstance()->forClassName($className)->requirements(),
            Registry::getInstance()->forMethod($className, $methodName)->requirements()
        );
    }

    /**
     * Returns the missing requirements for a test.
     *
     * @throws Exception
     * @throws Warning
     *
     * @psalm-param class-string $className
     */
    public static function getMissingRequirements(string $className, string $methodName): array
    {
        $required = self::getRequirements($className, $methodName);
        $missing  = [];
        $hint     = null;

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
     * Returns the provided data for a method.
     *
     * @throws Exception
     *
     * @psalm-param class-string $className
     */
    public static function getProvidedData(string $className, string $methodName): ?array
    {
        return Registry::getInstance()->forMethod($className, $methodName)->getProvidedData();
    }

    /**
     * @psalm-param class-string $className
     */
    public static function parseTestMethodAnnotations(string $className, ?string $methodName = ''): array
    {
        $registry = Registry::getInstance();

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
     */
    public static function getInlineAnnotations(string $className, string $methodName): array
    {
        return Registry::getInstance()->forMethod($className, $methodName)->getInlineAnnotations();
    }

    /** @psalm-param class-string $className */
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

    /** @psalm-param class-string $className */
    public static function getGroups(string $className, ?string $methodName = ''): array
    {
        $annotations = self::parseTestMethodAnnotations(
            $className,
            $methodName
        );

        $groups = [];

        if (isset($annotations['method']['author'])) {
            $groups[] = $annotations['method']['author'];
        } elseif (isset($annotations['class']['author'])) {
            $groups[] = $annotations['class']['author'];
        }

        if (isset($annotations['class']['group'])) {
            $groups[] = $annotations['class']['group'];
        }

        if (isset($annotations['method']['group'])) {
            $groups[] = $annotations['method']['group'];
        }

        if (isset($annotations['class']['ticket'])) {
            $groups[] = $annotations['class']['ticket'];
        }

        if (isset($annotations['method']['ticket'])) {
            $groups[] = $annotations['method']['ticket'];
        }

        foreach (['method', 'class'] as $element) {
            foreach (['small', 'medium', 'large'] as $size) {
                if (isset($annotations[$element][$size])) {
                    $groups[] = [$size];

                    break 2;
                }
            }
        }

        foreach (['method', 'class'] as $element) {
            if (isset($annotations[$element]['covers'])) {
                foreach ($annotations[$element]['covers'] as $coversTarget) {
                    $groups[] = ['__phpunit_covers_' . self::canonicalizeName($coversTarget)];
                }
            }

            if (isset($annotations[$element]['uses'])) {
                foreach ($annotations[$element]['uses'] as $usesTarget) {
                    $groups[] = ['__phpunit_uses_' . self::canonicalizeName($usesTarget)];
                }
            }
        }

        return array_unique(array_merge([], ...$groups));
    }

    /** @psalm-param class-string $className */
    public static function getSize(string $className, ?string $methodName): int
    {
        $groups = array_flip(self::getGroups($className, $methodName));

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

    /** @psalm-param class-string $className */
    public static function getProcessIsolationSettings(string $className, string $methodName): bool
    {
        $annotations = self::parseTestMethodAnnotations(
            $className,
            $methodName
        );

        return isset($annotations['class']['runTestsInSeparateProcesses']) || isset($annotations['method']['runInSeparateProcess']);
    }

    /** @psalm-param class-string $className */
    public static function getClassProcessIsolationSettings(string $className, string $methodName): bool
    {
        $annotations = self::parseTestMethodAnnotations(
            $className,
            $methodName
        );

        return isset($annotations['class']['runClassInSeparateProcess']);
    }

    /** @psalm-param class-string $className */
    public static function getPreserveGlobalStateSettings(string $className, string $methodName): ?bool
    {
        return self::getBooleanAnnotationSetting(
            $className,
            $methodName,
            'preserveGlobalState'
        );
    }

    /** @psalm-param class-string $className */
    public static function getHookMethods(string $className): array
    {
        if (!class_exists($className, false)) {
            return self::emptyHookMethodsArray();
        }

        if (!isset(self::$hookMethods[$className])) {
            self::$hookMethods[$className] = self::emptyHookMethodsArray();

            try {
                foreach ((new Reflection)->methodsInTestClass(new ReflectionClass($className)) as $method) {
                    $docBlock = Registry::getInstance()->forMethod($className, $method->getName());

                    if ($method->isStatic()) {
                        if ($docBlock->isHookToBeExecutedBeforeClass()) {
                            array_unshift(
                                self::$hookMethods[$className]['beforeClass'],
                                $method->getName()
                            );
                        }

                        if ($docBlock->isHookToBeExecutedAfterClass()) {
                            self::$hookMethods[$className]['afterClass'][] = $method->getName();
                        }
                    }

                    if ($docBlock->isToBeExecutedBeforeTest()) {
                        array_unshift(
                            self::$hookMethods[$className]['before'],
                            $method->getName()
                        );
                    }

                    if ($docBlock->isToBeExecutedAsPreCondition()) {
                        array_unshift(
                            self::$hookMethods[$className]['preCondition'],
                            $method->getName()
                        );
                    }

                    if ($docBlock->isToBeExecutedAsPostCondition()) {
                        self::$hookMethods[$className]['postCondition'][] = $method->getName();
                    }

                    if ($docBlock->isToBeExecutedAfterTest()) {
                        self::$hookMethods[$className]['after'][] = $method->getName();
                    }
                }
            } catch (ReflectionException $e) {
            }
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

        return array_key_exists(
            'test',
            Registry::getInstance()->forMethod(
                $method->getDeclaringClass()->getName(),
                $method->getName()
            )
            ->symbolAnnotations()
        );
    }

    /**
     * @throws CodeCoverageException
     *
     * @psalm-param class-string $className
     */
    private static function getLinesToBeCoveredOrUsed(string $className, string $methodName, string $mode): array
    {
        $annotations = self::parseTestMethodAnnotations(
            $className,
            $methodName
        );

        $classShortcut = null;

        if (!empty($annotations['class'][$mode . 'DefaultClass'])) {
            if (count($annotations['class'][$mode . 'DefaultClass']) > 1) {
                throw new CodeCoverageException(
                    sprintf(
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
            $list = array_merge($list, $annotations['method'][$mode]);
        }

        $codeUnits = CodeUnitCollection::fromArray([]);
        $mapper    = new Mapper;

        foreach (array_unique($list) as $element) {
            if ($classShortcut && strncmp($element, '::', 2) === 0) {
                $element = $classShortcut . $element;
            }

            $element = preg_replace('/[\s()]+$/', '', $element);
            $element = explode(' ', $element);
            $element = $element[0];

            if ($mode === 'covers' && interface_exists($element)) {
                throw new InvalidCoversTargetException(
                    sprintf(
                        'Trying to @cover interface "%s".',
                        $element
                    )
                );
            }

            try {
                $codeUnits = $codeUnits->mergeWith($mapper->stringToCodeUnits($element));
            } catch (InvalidCodeUnitException $e) {
                throw new InvalidCoversTargetException(
                    sprintf(
                        '"@%s %s" is invalid',
                        $mode,
                        $element
                    ),
                    (int) $e->getCode(),
                    $e
                );
            }
        }

        return $mapper->codeUnitsToSourceLines($codeUnits);
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

    /** @psalm-param class-string $className */
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
     * @see      http://github.com/zendframework/zf2 for the canonical source repository
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
}
