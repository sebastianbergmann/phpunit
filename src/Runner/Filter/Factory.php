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

use FilterIterator;
use InvalidArgumentException;
use Iterator;
use PHPUnit\Framework\TestSuite;
use ReflectionClass;

class Factory
{
    /**
     * @var array
     */
    private $filters = [];

    /**
     * @param ReflectionClass $filter
     * @param mixed           $args
     */
    public function addFilter(ReflectionClass $filter, $args)
    {
        if (!$filter->isSubclassOf(\RecursiveFilterIterator::class)) {
            throw new InvalidArgumentException(
                \sprintf(
                    'Class "%s" does not extend RecursiveFilterIterator',
                    $filter->name
                )
            );
        }

        $this->filters[] = [$filter, $args];
    }

    /**
     * @return FilterIterator
     */
    public function factory(Iterator $iterator, TestSuite $suite)
    {
        foreach ($this->filters as $filter) {
            list($class, $args) = $filter;
            $iterator           = $class->newInstance($iterator, $args, $suite);
        }

        return $iterator;
    }
}
