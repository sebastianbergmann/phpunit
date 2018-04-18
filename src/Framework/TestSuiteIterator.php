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
class TestSuiteIterator implements RecursiveIterator
{
    /**
     * @var int
     */
    protected $position;

    /**
     * @var Test[]
     */
    protected $tests;

    public function __construct(TestSuite $testSuite)
    {
        $this->tests = $testSuite->tests();

        if (empty($this->tests)) {
            return;
        }

        switch ($testSuite->getTestRunningOrder()) {
            case TestSuite::REVERSE_ORDER:
                TestSuiteSorter::reverse($this->tests);

                break;

            case TestSuite::RANDOM_ORDER:
                TestSuiteSorter::randomize($this->tests);

                break;

            case TestSuite::DEFAULT_ORDER:
            default:

                break;
        }

        if (($this->tests[0] instanceof TestCase) && $testSuite->getDependencyResolutionStrategy() === TestSuite::RESOLVE_DEPENDENCIES) {
            $this->tests = TestSuiteSorter::performDependencyResolution($this->tests);
        }
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
