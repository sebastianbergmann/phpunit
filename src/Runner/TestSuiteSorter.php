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
    public const ORDER_DEFAULT = 0;

    /**
     * @var int
     */
    public const ORDER_REVERSED = 1;

    /**
     * @var int
     */
    public const ORDER_RANDOMIZED = 2;

    /**
     * @throws Exception
     */
    public function reorderTestsInSuite(Test $suite, int $order, bool $resolveDependencies): void
    {
        if ($order !== self::ORDER_DEFAULT && $order !== self::ORDER_REVERSED && $order !== self::ORDER_RANDOMIZED) {
            throw new Exception(
                '$order must be one of TestSuiteSorter::ORDER_DEFAULT, TestSuiteSorter::ORDER_REVERSED, or TestSuiteSorter::ORDER_RANDOMIZED'
            );
        }

        if ($suite instanceof TestSuite && !empty($suite->tests())) {
            foreach ($suite as $_suite) {
                $this->reorderTestsInSuite($_suite, $order, $resolveDependencies);
            }

            $this->sort($suite, $order, $resolveDependencies);
        }
    }

    private function sort(TestSuite $suite, int $order, bool $resolveDependencies): void
    {
        if (empty($suite->tests())) {
            return;
        }

        if ($order === self::ORDER_REVERSED) {
            $suite->setTests($this->reverse($suite->tests()));
        } elseif ($order === self::ORDER_RANDOMIZED) {
            $suite->setTests($this->randomize($suite->tests()));
        }

        if ($resolveDependencies && $suite->tests()[0] instanceof TestCase) {
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
