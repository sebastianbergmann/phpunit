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

use function array_keys;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\Api\Groups;
use PHPUnit\Util\Reflection;
use PHPUnit\Util\Test as TestUtil;
use ReflectionClass;

/**
 * What is known about the tests in a single test file without loading it.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class TestIndexEntry
{
    /**
     * @var class-string<TestCase>
     */
    private string $className;

    /**
     * @var array<non-empty-string, list<non-empty-string>>
     */
    private array $groups;

    /**
     * @var non-empty-array<non-empty-string, non-empty-string>
     */
    private array $dependencies;

    /**
     * Returns null when the source files the entry would be derived from cannot
     * be read, in which case the file must not be skipped on a later run.
     *
     * @param ReflectionClass<TestCase> $class
     */
    public static function for(ReflectionClass $class, FileHasher $hasher): ?self
    {
        $dependencies = [];

        foreach (self::sourceFilesOf($class) as $file) {
            $hash = $hasher->hash($file);

            if ($hash === null) {
                return null;
            }

            $dependencies[$file] = $hash;
        }

        if ($dependencies === []) {
            return null;
        }

        $groups = [];

        foreach (Reflection::publicMethodsDeclaredDirectlyInTestClass($class) as $method) {
            if (!TestUtil::isTestMethod($method)) {
                continue;
            }

            $methodName = $method->getName();

            if ($methodName === '') {
                continue;
            }

            $groups[$methodName] = (new Groups)->groups($class->getName(), $methodName);
        }

        return new self($class->getName(), $groups, $dependencies);
    }

    /**
     * @param class-string<TestCase>                              $className
     * @param array<non-empty-string, list<non-empty-string>>     $groups
     * @param non-empty-array<non-empty-string, non-empty-string> $dependencies
     */
    public static function from(string $className, array $groups, array $dependencies): self
    {
        return new self($className, $groups, $dependencies);
    }

    /**
     * @param class-string<TestCase>                              $className
     * @param array<non-empty-string, list<non-empty-string>>     $groups
     * @param non-empty-array<non-empty-string, non-empty-string> $dependencies
     */
    private function __construct(string $className, array $groups, array $dependencies)
    {
        $this->className    = $className;
        $this->groups       = $groups;
        $this->dependencies = $dependencies;
    }

    /**
     * @return class-string<TestCase>
     */
    public function className(): string
    {
        return $this->className;
    }

    /**
     * The groups of every test method in the file, keyed by method name. These
     * are the groups that TestSuite::addTestMethod() merges into the test
     * suite, including the virtual groups that back --covers, --uses and
     * --requires-php-extension.
     *
     * @return array<non-empty-string, list<non-empty-string>>
     */
    public function groups(): array
    {
        return $this->groups;
    }

    /**
     * The source files this entry was derived from, and their hashes.
     *
     * @return non-empty-array<non-empty-string, non-empty-string>
     */
    public function dependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * An entry is only valid while every source file it was derived from still
     * has the contents it had when the entry was recorded.
     */
    public function isValid(FileHasher $hasher): bool
    {
        foreach ($this->dependencies as $file => $hash) {
            if ($hasher->hash($file) !== $hash) {
                return false;
            }
        }

        return true;
    }

    /**
     * Which test methods a test class has, and which metadata they carry, is
     * not decided by the file that declares the class alone: test methods are
     * collected from parent classes as well, see Reflection::
     * filterAndSortMethods(), and both classes and traits may contribute them.
     * The file that declares each of those is therefore part of the entry.
     *
     * Interfaces are not considered: they cannot contribute a test method to a
     * concrete test class.
     *
     * @param ReflectionClass<TestCase> $class
     *
     * @return list<non-empty-string>
     */
    private static function sourceFilesOf(ReflectionClass $class): array
    {
        $files   = [];
        $current = $class;

        while ($current !== false) {
            self::collectSourceFilesOf($current, $files);

            $current = $current->getParentClass();
        }

        return array_keys($files);
    }

    /**
     * @param ReflectionClass<object>       $class
     * @param array<non-empty-string, true> $files
     */
    private static function collectSourceFilesOf(ReflectionClass $class, array &$files): void
    {
        $file = $class->getFileName();

        if ($file !== false && $file !== '') {
            $files[$file] = true;
        }

        foreach ($class->getTraits() as $trait) {
            self::collectSourceFilesOf($trait, $files);
        }
    }
}
