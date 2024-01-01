<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Filter;

use function assert;
use FilterIterator;
use Iterator;
use PHPUnit\Framework\TestSuite;
use ReflectionClass;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Factory
{
    /**
     * @psalm-var array<int,array{0: ReflectionClass, 1: array|string}>
     */
    private array $filters = [];

    /**
     * @psalm-param list<non-empty-string> $testIds
     */
    public function addTestIdFilter(array $testIds): void
    {
        $this->filters[] = [
            new ReflectionClass(TestIdFilterIterator::class), $testIds,
        ];
    }

    /**
     * @psalm-param list<non-empty-string> $groups
     */
    public function addExcludeGroupFilter(array $groups): void
    {
        $this->filters[] = [
            new ReflectionClass(ExcludeGroupFilterIterator::class), $groups,
        ];
    }

    /**
     * @psalm-param list<non-empty-string> $groups
     */
    public function addIncludeGroupFilter(array $groups): void
    {
        $this->filters[] = [
            new ReflectionClass(IncludeGroupFilterIterator::class), $groups,
        ];
    }

    /**
     * @psalm-param non-empty-string $name
     */
    public function addIncludeNameFilter(string $name): void
    {
        $this->filters[] = [
            new ReflectionClass(IncludeNameFilterIterator::class), $name,
        ];
    }

    /**
     * @psalm-param non-empty-string $name
     */
    public function addExcludeNameFilter(string $name): void
    {
        $this->filters[] = [
            new ReflectionClass(ExcludeNameFilterIterator::class), $name,
        ];
    }

    public function factory(Iterator $iterator, TestSuite $suite): FilterIterator
    {
        foreach ($this->filters as $filter) {
            [$class, $arguments] = $filter;

            try {
                $iterator = $class->newInstance($iterator, $arguments, $suite);
            } catch (\ReflectionException) {
                continue;
            }
        }

        assert($iterator instanceof FilterIterator);

        return $iterator;
    }
}
