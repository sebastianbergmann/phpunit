<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use RecursiveIterator;

/**
 * Iterator for test suites.
 */
final class TestSuiteIterator implements RecursiveIterator
{
    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var Test[]
     */
    private $tests;

    public function __construct(TestSuite $testSuite)
    {
        $this->tests = $testSuite->tests();
    }

    /**
     * Rewinds the Iterator to the first element.
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Checks if there is a current element after calls to rewind() or next().
     */
    public function valid(): bool
    {
        return $this->position < \count($this->tests);
    }

    /**
     * Returns the key of the current element.
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * Returns the current element.
     */
    public function current(): ?Test
    {
        return $this->valid() ? $this->tests[$this->position] : null;
    }

    /**
     * Moves forward to next element.
     */
    public function next(): void
    {
        $this->position++;
    }

    /**
     * Returns the sub iterator for the current element.
     *
     * @throws \UnexpectedValueException if the current element is no TestSuite
     */
    public function getChildren(): self
    {
        if (!$this->hasChildren()) {
            throw new UnexpectedValueException(
                'The current item is no TestSuite instance and hence cannot have any children.',
                1567849414
            );
        }

        /** @var TestSuite $current */
        $current = $this->current();

        return new self($current);
    }

    /**
     * Checks whether the current element has children.
     */
    public function hasChildren(): bool
    {
        return $this->current() instanceof TestSuite;
    }
}
