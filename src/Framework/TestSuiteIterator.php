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

    /**
     * @param TestSuite $testSuite
     */
    public function __construct(TestSuite $testSuite)
    {
        $this->tests = $testSuite->tests();

        switch ($testSuite->getTestRunningOrder()) {
            case 'reverse':
                $this->tests = \array_reverse($this->tests);

                break;

            case 'random':
                \shuffle($this->tests);

                break;

            case 'normal':
            default:
                // do nothing, leave order of tests as is
                break;
        }

        if (!empty($this->tests) && ($this->tests[0] instanceof TestCase)) {
            switch ($testSuite->getDependencyResolutionStrategy()) {
                case 'reorder':
                    // Reorder dependencies
                    $this->reorderTestsByDependencies();

                    break;

                case 'ignore':
                default:
                    // do nothing; let the runner skip dependant tests
                    break;
            }
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

        // Keep starting from the top of the list of tests as long as it gets shorter
        $i = 0;
        do {
            $todoNames = \array_map(function ($t) {
                return $t->getName();
            }, $todo);
            if (empty(\array_intersect($todo[$i]->getDependencies(), $todoNames))) {
                $newTestOrder += \array_splice($todo, $i, 1);
                $i = 0;
            } else {
                $i++;
            }
        } while (!empty($todo) && ($i < \count($todo)));

        // Add leftover tests to the end
        $newTestOrder += $todo;

        $this->tests = $newTestOrder;
    }
}
