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
use const PHP_OS_FAMILY;
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
use function in_array;
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
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\CodeCoverageException;
use PHPUnit\Framework\ErrorTestCase;
use PHPUnit\Framework\ExecutionOrderDependency;
use PHPUnit\Framework\IncompleteTestCase;
use PHPUnit\Framework\InvalidCoversTargetException;
use PHPUnit\Framework\InvalidDataProviderException;
use PHPUnit\Framework\SelfDescribing;
use PHPUnit\Framework\SkippedTestCase;
use PHPUnit\Framework\SkippedTestError;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSize\TestSize;
use PHPUnit\Framework\WarningTestCase;
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
use PHPUnit\Util\Metadata\RequiresFunction;
use PHPUnit\Util\Metadata\RequiresMethod;
use PHPUnit\Util\Metadata\RequiresOperatingSystem;
use PHPUnit\Util\Metadata\RequiresOperatingSystemFamily;
use PHPUnit\Util\Metadata\RequiresPhp;
use PHPUnit\Util\Metadata\RequiresPhpExtension;
use PHPUnit\Util\Metadata\RequiresPhpunit;
use PHPUnit\Util\Metadata\RequiresSetting;
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
     */
    public static function linesToBeCovered(string $className, string $methodName)
    {
        if (!self::shouldCodeCoverageBeCollectedFor($className, $methodName)) {
            return false;
        }

        $metadataForClass = MetadataRegistry::parser()->forClass($className);
        $classShortcut    = null;

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

        foreach (MetadataRegistry::parser()->forClassAndMethod($className, $methodName) as $metadata) {
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
     */
    public static function linesToBeUsed(string $className, string $methodName): array
    {
        $metadataForClass = MetadataRegistry::parser()->forClass($className);
        $classShortcut    = null;

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

        foreach (MetadataRegistry::parser()->forClassAndMethod($className, $methodName) as $metadata) {
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
     */
    public static function shouldCodeCoverageBeCollectedFor(string $className, string $methodName): bool
    {
        if (in_array($className, [ErrorTestCase::class, IncompleteTestCase::class, SkippedTestCase::class, WarningTestCase::class], true)) {
            return false;
        }

        $metadataForClass  = MetadataRegistry::parser()->forClass($className);
        $metadataForMethod = MetadataRegistry::parser()->forMethod($className, $methodName);

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
     * @psalm-param class-string $className
     *
     * @psalm-return list<string>
     */
    public static function getMissingRequirements(string $className, string $methodName): array
    {
        $missing = [];

        foreach (MetadataRegistry::parser()->forClassAndMethod($className, $methodName) as $metadata) {
            if ($metadata->isRequiresPhp()) {
                assert($metadata instanceof RequiresPhp);

                if (!$metadata->versionRequirement()->isSatisfiedBy(PHP_VERSION)) {
                    $missing[] = sprintf(
                        'PHP %s is required.',
                        $metadata->versionRequirement()->asString()
                    );
                }
            }

            if ($metadata->isRequiresPhpExtension()) {
                assert($metadata instanceof RequiresPhpExtension);

                if (!extension_loaded($metadata->extension()) ||
                    ($metadata->hasVersionRequirement() &&
                     !$metadata->versionRequirement()->isSatisfiedBy(phpversion($metadata->extension())))) {
                    $missing[] = sprintf(
                        'PHP extension %s%s is required.',
                        $metadata->extension(),
                        $metadata->hasVersionRequirement() ? (' ' . $metadata->versionRequirement()->asString()) : ''
                    );
                }
            }

            if ($metadata->isRequiresPhpunit()) {
                assert($metadata instanceof RequiresPhpunit);

                if (!$metadata->versionRequirement()->isSatisfiedBy(Version::id())) {
                    $missing[] = sprintf(
                        'PHPUnit %s is required.',
                        $metadata->versionRequirement()->asString()
                    );
                }
            }

            if ($metadata->isRequiresOperatingSystemFamily()) {
                assert($metadata instanceof RequiresOperatingSystemFamily);

                if ($metadata->operatingSystemFamily() !== PHP_OS_FAMILY) {
                    $missing[] = sprintf(
                        'Operating system %s is required.',
                        $metadata->operatingSystemFamily()
                    );
                }
            }

            if ($metadata->isRequiresOperatingSystem()) {
                assert($metadata instanceof RequiresOperatingSystem);

                $pattern = sprintf(
                    '/%s/i',
                    addcslashes($metadata->operatingSystem(), '/')
                );

                if (!preg_match($pattern, PHP_OS)) {
                    $missing[] = sprintf(
                        'Operating system %s is required.',
                        $metadata->operatingSystem()
                    );
                }
            }

            if ($metadata->isRequiresFunction()) {
                assert($metadata instanceof RequiresFunction);

                if (!function_exists($metadata->functionName())) {
                    $missing[] = sprintf(
                        'Function %s() is required.',
                        $metadata->functionName()
                    );
                }
            }

            if ($metadata->isRequiresMethod()) {
                assert($metadata instanceof RequiresMethod);

                if (!method_exists($metadata->className(), $metadata->methodName())) {
                    $missing[] = sprintf(
                        'Method %s::%s() is required.',
                        $metadata->className(),
                        $metadata->methodName()
                    );
                }
            }

            if ($metadata->isRequiresSetting()) {
                assert($metadata instanceof RequiresSetting);

                if (ini_get($metadata->setting()) !== $metadata->value()) {
                    $missing[] = sprintf(
                        'Setting "%s" is required to be "%s".',
                        $metadata->setting(),
                        $metadata->value()
                    );
                }
            }
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
        $dataProvider = MetadataRegistry::parser()->forMethod($className, $methodName)->isDataProvider();
        $testWith     = MetadataRegistry::parser()->forMethod($className, $methodName)->isTestWith();

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
            $dependencies[] = ExecutionOrderDependency::fromDependsAnnotation($className, $value);
        }

        return array_unique($dependencies);
    }

    /**
     * @psalm-param class-string $className
     */
    public static function groups(string $className, string $methodName): array
    {
        $groups = [];

        foreach (MetadataRegistry::parser()->forClassAndMethod($className, $methodName) as $metadata) {
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
    public static function size(string $className, string $methodName): TestSize
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

                $metadata = MetadataRegistry::parser()->forMethod($className, $method->getName());

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

        $metadata = MetadataRegistry::parser()->forMethod(
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
                $object = $class->newInstanceWithoutConstructor();
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
     *
     * @throws Exception
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
