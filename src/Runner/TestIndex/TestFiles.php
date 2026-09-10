<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestIndex;

use function array_key_exists;
use function array_keys;
use function assert;
use function class_exists;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\DataProvider as DataProviderMetadata;
use PHPUnit\Metadata\Parser\Registry as MetadataRegistry;
use PHPUnit\Util\Reflection;
use ReflectionClass;

/**
 * The files a test class is made of.
 *
 * These are the files that declare what the class itself is made of, see
 * Reflection::sourceFilesOf(), and the files of a class a data provider is a
 * method of: such a data provider decides what the data sets of a test are,
 * and is therefore part of what the test class is made of as well.
 *
 * PHPUnit's own files are not among them, which is what keeps a change to
 * PHPUnit itself from invalidating everything at once; that a different
 * version of PHPUnit means different data is established where that data is
 * kept.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestFiles
{
    /**
     * What a test class is made of is decided by the class, and not by which
     * of its test methods is being asked about, so the answer is remembered:
     * a test class with a hundred test methods would otherwise be walked, with
     * its parents, its traits and the metadata of every one of its methods, a
     * hundred times.
     *
     * @var array<class-string, ?non-empty-list<non-empty-string>>
     */
    private static array $files = [];

    /**
     * Returns null when a class a data provider is a method of cannot be
     * found: what the test does is then not known, and must not be treated as
     * known.
     *
     * @param ReflectionClass<TestCase> $class
     *
     * @return ?non-empty-list<non-empty-string>
     */
    public static function of(ReflectionClass $class): ?array
    {
        $className = $class->getName();

        if (array_key_exists($className, self::$files)) {
            return self::$files[$className];
        }

        self::$files[$className] = self::filesOf($class);

        return self::$files[$className];
    }

    /**
     * @param ReflectionClass<TestCase> $class
     *
     * @return ?non-empty-list<non-empty-string>
     */
    private static function filesOf(ReflectionClass $class): ?array
    {
        $files = [];

        foreach (Reflection::sourceFilesOf($class) as $file) {
            $files[$file] = true;
        }

        foreach (self::dataProvidersOf($class) as $className) {
            if (!class_exists($className)) {
                return null;
            }

            foreach (Reflection::sourceFilesOf(new ReflectionClass($className)) as $file) {
                $files[$file] = true;
            }
        }

        $files = array_keys($files);

        if ($files === []) {
            return null; // @codeCoverageIgnore
        }

        return $files;
    }

    /**
     * @param ReflectionClass<TestCase> $class
     *
     * @return list<class-string>
     */
    private static function dataProvidersOf(ReflectionClass $class): array
    {
        $classNames = [];

        foreach (Reflection::publicMethodsDeclaredDirectlyInTestClass($class) as $method) {
            $methodName = $method->getName();

            // @codeCoverageIgnoreStart
            if ($methodName === '') {
                continue;
            }
            // @codeCoverageIgnoreEnd

            foreach (MetadataRegistry::parser()->forMethod($class->getName(), $methodName)->isDataProvider() as $dataProvider) {
                assert($dataProvider instanceof DataProviderMetadata);

                $classNames[$dataProvider->className()] = true;
            }
        }

        return array_keys($classNames);
    }
}
