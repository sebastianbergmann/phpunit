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

use PHPUnit\Framework\DataProviderTestSuite;
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
    public const ORDER_RANDOMIZED = 1;

    /**
     * @var int
     */
    public const ORDER_REVERSED = 2;

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

        if ($resolveDependencies && !($suite instanceof DataProviderTestSuite) && $this->suiteOnlyContainsTests($suite)) {
            $suite->setTests($this->resolveDependencies($suite->tests()));
        }
    }

    private function suiteOnlyContainsTests(TestSuite $suite): bool
    {
        return \array_reduce($suite->tests(), function ($carry, $test) {
            return $carry && ($test instanceof TestCase || $test instanceof DataProviderTestSuite);
        }, true);
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
     * Short description of algorithm:
     * 1. Pick the next Test from remaining tests to be checked for dependencies.
     * 2. If the test has no dependencies: mark done, start again from the top
     * 3. If the test has dependencies but none left to do: mark done, start again from the top
     * 4. When we reach the end add any leftover tests to the end. These will be marked 'skipped' during execution.
     *
     * @param array<DataProviderTestSuite|TestCase> $tests
     *
     * @return array<DataProviderTestSuite|TestCase>
     */
    private function resolveDependencies(array $tests): array
    {
        $newTestOrder = [];
        $i            = 0;

        do {
            $todoNames = \array_map(function ($test) {
                return $this->getNormalizedTestName($test);
            }, $tests);

            if (!$tests[$i]->hasDependencies() || empty(\array_intersect($this->getNormalizedDependencyNames($tests[$i]), $todoNames))) {
                $newTestOrder = \array_merge($newTestOrder, \array_splice($tests, $i, 1));
                $i            = 0;
            } else {
                $i++;
            }
        } while (!empty($tests) && ($i < \count($tests)));

        return \array_merge($newTestOrder, $tests);
    }

    /**
     * @param DataProviderTestSuite|TestCase $test
     *
     * @return string Full test name as "TestSuiteClassName::testMethodName"
     */
    private function getNormalizedTestName($test): string
    {
        if (\strpos($test->getName(), '::') !== false) {
            return $test->getName();
        }

        return \get_class($test) . '::' . $test->getName();
    }

    /**
     * @param DataProviderTestSuite|TestCase $test
     *
     * @return array<string> A list of full test names as "TestSuiteClassName::testMethodName"
     */
    private function getNormalizedDependencyNames($test): array
    {
        if ($test instanceof DataProviderTestSuite) {
            $testClass = \substr($test->getName(), 0, \strpos($test->getName(), '::'));
        } else {
            $testClass = \get_class($test);
        }

        $names = \array_map(function ($name) use ($testClass) {
            return \strpos($name, '::') === false
                ? $testClass . '::' . $name
                : $name;
        }, $test->getDependencies());

        return $names;
    }
}
