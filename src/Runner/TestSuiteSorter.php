<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Runner;

use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;

final class TestSuiteSorter
{
    /**
     * @var int
     */
    public const DEFAULT_ORDER = 0;

    /**
     * @var int
     */
    public const REVERSE_ORDER = 1;

    /**
     * @var int
     */
    public const RANDOM_ORDER = 2;

    /**
     * @var int
     */
    public const IGNORE_DEPENDENCIES = 3;

    /**
     * @var int
     */
    public const RESOLVE_DEPENDENCIES = 4;

    /**
     * @var int
     */
    private $testRunningOrder = self::DEFAULT_ORDER;

    /**
     * @var int
     */
    private $dependencyResolutionStrategy = self::RESOLVE_DEPENDENCIES;

    public function __construct(array $arguments)
    {
        $this->testRunningOrder               = $arguments['order'];
        $this->dependencyResolutionStrategy   = $arguments['reorderDependencies'];
    }

    public function reorderTestsInSuite(Test $suite): void
    {
        if ($suite instanceof TestSuite && !empty($suite->tests())) {
            foreach ($suite as $_suite) {
                $this->reorderTestsInSuite($_suite);
            }

            $this->sort($suite);
        }
    }

    private function sort(TestSuite $suite): void
    {
        if (empty($suite->tests())) {
            return;
        }

        if ($this->testRunningOrder === self::REVERSE_ORDER) {
            $suite->setTests($this->reverse($suite->tests()));
        } elseif ($this->testRunningOrder === self::RANDOM_ORDER) {
            $suite->setTests($this->randomize($suite->tests()));
        }

        if (($suite->tests()[0] instanceof TestCase) && $this->dependencyResolutionStrategy === self::RESOLVE_DEPENDENCIES) {
            $suite->setTests($this->resolveDependencies($suite->tests()));
        }
    }

    private function reverse(array $tests): array
    {
        return \array_reverse($tests);
    }

    private function randomize(array $tests): array
    {
        \shuffle($tests);

        return $tests;
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
     *
     * @param Test[] $tests
     *
     * @return Test[]
     */
    private function resolveDependencies(array $tests): array
    {
        if (empty($tests)) {
            return $tests;
        }

        $todo         = [];
        $newTestOrder = [];

        foreach ($tests as $test) {
            if ($test->hasDependencies()) {
                $todo[] = $test;
            } else {
                $newTestOrder[] = $test;
            }
        }

        if (empty($todo)) {
            return $tests;
        }

        $i = 0;

        do {
            $todoNames = \array_merge(
                \array_map(function (Test $t) {
                    return $t->getName();
                }, $todo),
                \array_map(function (Test $t) {
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

        return \array_merge($newTestOrder, $todo);
    }
}
