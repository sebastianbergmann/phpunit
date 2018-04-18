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
            case 'reverse':
                $this->tests = \array_reverse($this->tests);

                break;

            case 'random':
                \shuffle($this->tests);

                break;

            case 'normal':
            default:
                break;
        }

        if (!empty($this->tests) && ($this->tests[0] instanceof TestCase) && $testSuite->getDependencyResolutionStrategy() === 'reorder') {
            $this->reorderTestsByDependencies();
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

    /**
     * Reorder Tests within a TestCase in such a way as to resolve as many dependencies as possible.
     * The algorithm will leave the tests in original running order when it can.
     * For more details see the documentation for test dependencies.
     *
     * The final running order will be:
     * 1. tests without dependencies
     * 2. tests with resolved dependencies
     * 3. tests for which the dependencies could not be resolved
     *
     * Short description of algorithm:
     * 1. Compile two lists of Tests in original order: with and without dependencies.
     * 2. Independent tests can be run first.
     * 3a. Pick the next Test from the list of dependants.
     *  b. When all dependencies run before this Test, move it to the reordered list
     *  c. Start again from the top of the list of dependants.
     * 4. When we reach the end add any leftover tests to the end. These will be marked 'skipped'.
     */
    private function reorderTestsByDependencies()
    {
        if (empty($this->tests)) {
            return;
        }

        $todo         = [];
        $newTestOrder = [];

        foreach ($this->tests as $test) {
            if ($test->hasDependencies()) {
                $todo[] = $test;
            } else {
                $newTestOrder[] = $test;
            }
        }

        if (empty($todo)) {
            return;
        }

        $i = 0;

        do {
            $todoNames = \array_merge(
                \array_map(function (TestCase $t) {
                    return $t->getName();
                }, $todo),
                \array_map(function (TestCase $t) {
                    return \get_class($t) . '::' . $t->getName();
                }, $todo)
            );

            if (empty(\array_intersect($todo[$i]->getDependencies(), $todoNames))) {
                $newTestOrder = \array_merge($newTestOrder, \array_splice($todo, $i, 1));
                $i            = 0;
            } else {
                $i++;
            }
        } while (!empty($todo) && ($i < \count($todo)));

        $newTestOrder = \array_merge($newTestOrder, $todo);
        $this->tests  = $newTestOrder;
    }
}
