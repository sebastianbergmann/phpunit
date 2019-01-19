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

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
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
     * @var int
     */
    public const ORDER_DEFECTS_FIRST = 3;

    /**
     * @var int
     */
    public const ORDER_DURATION = 4;

    /**
     * List of sorting weights for all test result codes. A higher number gives higher priority.
     */
    private const DEFECT_SORT_WEIGHT = [
        BaseTestRunner::STATUS_ERROR      => 6,
        BaseTestRunner::STATUS_FAILURE    => 5,
        BaseTestRunner::STATUS_WARNING    => 4,
        BaseTestRunner::STATUS_INCOMPLETE => 3,
        BaseTestRunner::STATUS_RISKY      => 2,
        BaseTestRunner::STATUS_SKIPPED    => 1,
        BaseTestRunner::STATUS_UNKNOWN    => 0,
    ];

    /**
     * @var array<string, int> Associative array of (string => DEFECT_SORT_WEIGHT) elements
     */
    private $defectSortOrder = [];

    /**
     * @var TestResultCacheInterface
     */
    private $cache;

    /**
     * @var array array<string> A list of normalized names of tests before reordering
     */
    private $originalExecutionOrder = [];

    /**
     * @var array array<string> A list of normalized names of tests affected by reordering
     */
    private $executionOrder = [];

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public static function getTestSorterUID(Test $test): string
    {
        if ($test instanceof PhptTestCase) {
            return $test->getName();
        }

        if ($test instanceof TestCase) {
            $testName = $test->getName(true);

            if (\strpos($testName, '::') === false) {
                $testName = \get_class($test) . '::' . $testName;
            }

            return $testName;
        }

        return $test->getName();
    }

    public function __construct(?TestResultCacheInterface $cache = null)
    {
        $this->cache = $cache ?? new NullTestResultCache;
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function reorderTestsInSuite(Test $suite, int $order, bool $resolveDependencies, int $orderDefects, bool $isRootTestSuite = true): void
    {
        $allowedOrders = [
            self::ORDER_DEFAULT,
            self::ORDER_REVERSED,
            self::ORDER_RANDOMIZED,
            self::ORDER_DURATION,
        ];

        if (!\in_array($order, $allowedOrders, true)) {
            throw new Exception(
                '$order must be one of TestSuiteSorter::ORDER_DEFAULT, TestSuiteSorter::ORDER_REVERSED, or TestSuiteSorter::ORDER_RANDOMIZED, or TestSuiteSorter::ORDER_DURATION'
            );
        }

        $allowedOrderDefects = [
            self::ORDER_DEFAULT,
            self::ORDER_DEFECTS_FIRST,
        ];

        if (!\in_array($orderDefects, $allowedOrderDefects, true)) {
            throw new Exception(
                '$orderDefects must be one of TestSuiteSorter::ORDER_DEFAULT, TestSuiteSorter::ORDER_DEFECTS_FIRST'
            );
        }

        if ($isRootTestSuite) {
            $this->originalExecutionOrder = $this->calculateTestExecutionOrder($suite);
        }

        if ($suite instanceof TestSuite) {
            foreach ($suite as $_suite) {
                $this->reorderTestsInSuite($_suite, $order, $resolveDependencies, $orderDefects, false);
            }

            if ($orderDefects === self::ORDER_DEFECTS_FIRST) {
                $this->addSuiteToDefectSortOrder($suite);
            }

            $this->sort($suite, $order, $resolveDependencies, $orderDefects);
        }

        if ($isRootTestSuite) {
            $this->executionOrder = $this->calculateTestExecutionOrder($suite);
        }
    }

    public function getOriginalExecutionOrder(): array
    {
        return $this->originalExecutionOrder;
    }

    public function getExecutionOrder(): array
    {
        return $this->executionOrder;
    }

    private function sort(TestSuite $suite, int $order, bool $resolveDependencies, int $orderDefects): void
    {
        if (empty($suite->tests())) {
            return;
        }

        if ($order === self::ORDER_REVERSED) {
            $suite->setTests($this->reverse($suite->tests()));
        } elseif ($order === self::ORDER_RANDOMIZED) {
            $suite->setTests($this->randomize($suite->tests()));
        } elseif ($order === self::ORDER_DURATION && $this->cache !== null) {
            $suite->setTests($this->sortByDuration($suite->tests()));
        }

        if ($orderDefects === self::ORDER_DEFECTS_FIRST && $this->cache !== null) {
            $suite->setTests($this->sortDefectsFirst($suite->tests()));
        }

        if ($resolveDependencies && !($suite instanceof DataProviderTestSuite) && $this->suiteOnlyContainsTests($suite)) {
            $suite->setTests($this->resolveDependencies($suite->tests()));
        }
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    private function addSuiteToDefectSortOrder(TestSuite $suite): void
    {
        $max = 0;

        foreach ($suite->tests() as $test) {
            $testname = self::getTestSorterUID($test);

            if (!isset($this->defectSortOrder[$testname])) {
                $this->defectSortOrder[$testname]        = self::DEFECT_SORT_WEIGHT[$this->cache->getState($testname)];
                $max                                     = \max($max, $this->defectSortOrder[$testname]);
            }
        }

        $this->defectSortOrder[$suite->getName()] = $max;
    }

    private function suiteOnlyContainsTests(TestSuite $suite): bool
    {
        return \array_reduce(
            $suite->tests(),
            function ($carry, $test) {
                return $carry && ($test instanceof TestCase || $test instanceof DataProviderTestSuite);
            },
            true
        );
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

    private function sortDefectsFirst(array $tests): array
    {
        \usort(
            $tests,
            /**
             * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
             */
            function ($left, $right) {
                return $this->cmpDefectPriorityAndTime($left, $right);
            }
        );

        return $tests;
    }

    private function sortByDuration(array $tests): array
    {
        \usort(
            $tests,
            /**
             * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
             */
            function ($left, $right) {
                return $this->cmpDuration($left, $right);
            }
        );

        return $tests;
    }

    /**
     * Comparator callback function to sort tests for "reach failure as fast as possible":
     * 1. sort tests by defect weight defined in self::DEFECT_SORT_WEIGHT
     * 2. when tests are equally defective, sort the fastest to the front
     * 3. do not reorder successful tests
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    private function cmpDefectPriorityAndTime(Test $a, Test $b): int
    {
        $priorityA = $this->defectSortOrder[self::getTestSorterUID($a)] ?? 0;
        $priorityB = $this->defectSortOrder[self::getTestSorterUID($b)] ?? 0;

        if ($priorityB <=> $priorityA) {
            // Sort defect weight descending
            return $priorityB <=> $priorityA;
        }

        if ($priorityA || $priorityB) {
            return $this->cmpDuration($a, $b);
        }

        // do not change execution order
        return 0;
    }

    /**
     * Compares test duration for sorting tests by duration ascending.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    private function cmpDuration(Test $a, Test $b): int
    {
        return $this->cache->getTime(self::getTestSorterUID($a)) <=> $this->cache->getTime(self::getTestSorterUID($b));
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
            $todoNames = \array_map(
                /**
                 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
                 */
                function ($test) {
                    return self::getTestSorterUID($test);
                },
                $tests
            );

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
     * @return array<string> A list of full test names as "TestSuiteClassName::testMethodName"
     */
    private function getNormalizedDependencyNames($test): array
    {
        if ($test instanceof DataProviderTestSuite) {
            $testClass = \substr($test->getName(), 0, \strpos($test->getName(), '::'));
        } else {
            $testClass = \get_class($test);
        }

        $names = \array_map(
            function ($name) use ($testClass) {
                return \strpos($name, '::') === false ? $testClass . '::' . $name : $name;
            },
            $test->getDependencies()
        );

        return $names;
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    private function calculateTestExecutionOrder(Test $suite): array
    {
        $tests = [];

        if ($suite instanceof TestSuite) {
            foreach ($suite->tests() as $test) {
                if (!($test instanceof TestSuite)) {
                    $tests[] = self::getTestSorterUID($test);
                } else {
                    $tests = \array_merge($tests, $this->calculateTestExecutionOrder($test));
                }
            }
        }

        return $tests;
    }
}
