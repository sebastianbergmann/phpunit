<?php declare(strict_types=1);
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
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestSuiteIterator implements RecursiveIterator
{
    /**
     * @var int
     */
    private $position;

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
    public function current(): Test
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
     */
    public function getChildren(): self
    {
        return new self(
            $this->tests[$this->position]
        );
    }

    /**
     * Checks whether the current element has children.
     */
    public function hasChildren(): bool
    {
        return $this->tests[$this->position] instanceof TestSuite;
    }
}
