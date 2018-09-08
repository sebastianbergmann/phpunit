<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;

/**
 * @group test-reorder
 */
class TestSuiteSorterTest extends TestCase
{
    /**
     * Constants to improve clarity of @dataprovider
     */
    private const IGNORE_DEPENDENCIES  = false;

    private const RESOLVE_DEPENDENCIES = true;

    public function testThrowsExceptionWhenUsingInvalidOrderOption()
    {
        $suite = new TestSuite;
        $suite->addTestSuite(\MultiDependencyTest::class);
        $sorter = new TestSuiteSorter();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('$order must be one of TestSuiteSorter::ORDER_DEFAULT, TestSuiteSorter::ORDER_REVERSED, or TestSuiteSorter::ORDER_RANDOMIZED');
        $sorter->reorderTestsInSuite($suite, -1, false, TestSuiteSorter::ORDER_DEFAULT);
    }

    public function testThrowsExceptionWhenUsingInvalidOrderDefectsOption()
    {
        $suite = new TestSuite;
        $suite->addTestSuite(\MultiDependencyTest::class);
        $sorter = new TestSuiteSorter();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('$orderDefects must be one of TestSuiteSorter::ORDER_DEFAULT, TestSuiteSorter::ORDER_DEFECTS_FIRST');
        $sorter->reorderTestsInSuite($suite, TestSuiteSorter::ORDER_DEFAULT, false, -1);
    }

    /**
     * @dataProvider suiteSorterOptionPermutationsProvider
     */
    public function testShouldNotAffectEmptyTestSuite(int $order, bool $resolveDependencies, int $orderDefects)
    {
        $sorter = new TestSuiteSorter;
        $suite  = new TestSuite;

        $this->assertSame([], $suite->tests());

        $sorter->reorderTestsInSuite($suite, $order, $resolveDependencies, $orderDefects);

        $this->assertSame([], $suite->tests());
    }

    /**
     * @dataProvider commonSorterOptionsProvider
     */
    public function testBasicExecutionOrderOptions(int $order, bool $resolveDependencies, array $expected)
    {
        $suite = new TestSuite;
        $suite->addTestSuite(\MultiDependencyTest::class);
        $sorter = new TestSuiteSorter();

        $sorter->reorderTestsInSuite($suite, $order, $resolveDependencies, TestSuiteSorter::ORDER_DEFAULT);

        $this->assertSame($expected, $this->getTestExecutionOrder($suite));
    }

    public function testCanSetRandomizationWithASeed()
    {
        $suite = new TestSuite;
        $suite->addTestSuite(\MultiDependencyTest::class);
        $sorter = new TestSuiteSorter();

        \mt_srand(54321);
        $sorter->reorderTestsInSuite($suite, TestSuiteSorter::ORDER_RANDOMIZED, false, TestSuiteSorter::ORDER_DEFAULT);

        $this->assertSame(['testTwo', 'testFour', 'testFive', 'testThree', 'testOne'], $this->getTestExecutionOrder($suite));
    }

    public function testCanSetRandomizationWithASeedAndResolveDependencies()
    {
        $suite = new TestSuite;
        $suite->addTestSuite(\MultiDependencyTest::class);
        $sorter = new TestSuiteSorter();

        \mt_srand(54321);
        $sorter->reorderTestsInSuite($suite, TestSuiteSorter::ORDER_RANDOMIZED, true, TestSuiteSorter::ORDER_DEFAULT);

        $this->assertSame(['testTwo', 'testFive', 'testOne', 'testThree', 'testFour'], $this->getTestExecutionOrder($suite));
    }

    /**
     * @dataProvider defectsSorterOptionsProvider
     */
    public function testSuiteSorterDefectsOptions(int $order, bool $resolveDependencies, array $runState, array $expected)
    {
        $suite = new TestSuite;
        $suite->addTestSuite(\MultiDependencyTest::class);

        $cache = new TestResultCache();

        foreach ($runState as $testName => $data) {
            $cache->setState($testName, $data['state']);
            $cache->setTime($testName, $data['time']);
        }

        $sorter  = new TestSuiteSorter($cache);
        $sorter->reorderTestsInSuite($suite, $order, $resolveDependencies, TestSuiteSorter::ORDER_DEFECTS_FIRST);

        $this->assertSame($expected, $this->getTestExecutionOrder($suite));
    }

    /**
     * A @dataprovider for basic execution reordering options based on MultiDependencyTest
     * This class has the following relevant properties:
     * - it has five tests 'testOne' ... 'testFive'
     * - 'testThree' @depends on both 'testOne' and 'testTwo'
     * - 'testFour' @depends on 'MultiDependencyTest::testThree' to test FQN @depends
     * - 'testFive' has no dependencies
     */
    public function commonSorterOptionsProvider(): array
    {
        return [
            'default' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::IGNORE_DEPENDENCIES,
                ['testOne', 'testTwo', 'testThree', 'testFour', 'testFive']],

            // Activating dependency resolution should have no effect under normal circumstances
            'resolve default' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::RESOLVE_DEPENDENCIES,
                ['testOne', 'testTwo', 'testThree', 'testFour', 'testFive']],

            // Reversing without checks should give a simple reverse order
            'reverse' => [
                TestSuiteSorter::ORDER_REVERSED,
                self::IGNORE_DEPENDENCIES,
                ['testFive', 'testFour', 'testThree', 'testTwo', 'testOne']],

            // Reversing with resolution still allows testFive to move to front, testTwo before testOne
            'resolve reverse' => [
                TestSuiteSorter::ORDER_REVERSED,
                self::RESOLVE_DEPENDENCIES,
                ['testFive', 'testTwo', 'testOne', 'testThree', 'testFour']],
        ];
    }

    /**
     * A @dataprovider for testing defects execution reordering options based on MultiDependencyTest
     * This class has the following relevant properties:
     * - it has five tests 'testOne' ... 'testFive'
     * - 'testThree' @depends on both 'testOne' and 'testTwo'
     * - 'testFour' @depends on 'MultiDependencyTest::testThree' to test FQN @depends
     * - 'testFive' has no dependencies
     */
    public function defectsSorterOptionsProvider(): array
    {
        return [
            // The most simple situation should work as normal
            'default, no defects' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::IGNORE_DEPENDENCIES,
                [
                    'testOne'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testTwo'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testThree' => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testFour'  => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testFive'  => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                ],
                ['testOne', 'testTwo', 'testThree', 'testFour', 'testFive']],

            // Running with an empty cache should not spook the TestSuiteSorter
            'default, empty result cache' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::IGNORE_DEPENDENCIES,
                [
                    // empty result cache
                ],
                ['testOne', 'testTwo', 'testThree', 'testFour', 'testFive']],

            // testFive is independent and can be moved to the front
            'default, testFive skipped' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::IGNORE_DEPENDENCIES,
                [
                    'testOne'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testTwo'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testThree' => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testFour'  => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testFive'  => ['state' => BaseTestRunner::STATUS_SKIPPED, 'time' => 1],
                ],
                ['testFive', 'testOne', 'testTwo', 'testThree', 'testFour']],

            // Defects in testFive and testTwo, but the faster testFive should be run first
            'default, testTwo testFive skipped' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::IGNORE_DEPENDENCIES,
                [
                    'testOne'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testTwo'   => ['state' => BaseTestRunner::STATUS_SKIPPED, 'time' => 1],
                    'testThree' => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testFour'  => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testFive'  => ['state' => BaseTestRunner::STATUS_SKIPPED, 'time' => 0],
                ],
                ['testFive', 'testTwo', 'testOne', 'testThree', 'testFour']],

            // Skipping testThree will move it to the front when ignoring dependencies
            'default, testThree skipped' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::IGNORE_DEPENDENCIES,
                [
                    'testOne'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testTwo'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testThree' => ['state' => BaseTestRunner::STATUS_SKIPPED, 'time' => 1],
                    'testFour'  => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testFive'  => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                ],
                ['testThree', 'testOne', 'testTwo', 'testFour', 'testFive']],

            // Skipping testThree will move it to the front but behind its dependencies
            'default resolve, testThree skipped' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::RESOLVE_DEPENDENCIES,
                [
                    'testOne'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testTwo'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testThree' => ['state' => BaseTestRunner::STATUS_SKIPPED, 'time' => 1],
                    'testFour'  => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testFive'  => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                ],
                ['testOne', 'testTwo', 'testThree', 'testFour', 'testFive']],

            // Skipping testThree will move it to the front and keep the others reversed
            'reverse, testThree skipped' => [
                TestSuiteSorter::ORDER_REVERSED,
                self::IGNORE_DEPENDENCIES,
                [
                    'testOne'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testTwo'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testThree' => ['state' => BaseTestRunner::STATUS_SKIPPED, 'time' => 1],
                    'testFour'  => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testFive'  => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                ],
                ['testThree', 'testFive', 'testFour', 'testTwo', 'testOne']],

            // Demonstrate a limit of the dependency resolver: after sorting defects to the front,
            // the resolver will mark testFive done before testThree because of dependencies
            'default resolve, testThree skipped, testFive fast' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::RESOLVE_DEPENDENCIES,
                [
                    'testOne'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testTwo'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testThree' => ['state' => BaseTestRunner::STATUS_SKIPPED, 'time' => 0],
                    'testFour'  => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testFive'  => ['state' => BaseTestRunner::STATUS_SKIPPED, 'time' => 1],
                ],
                ['testFive', 'testOne', 'testTwo', 'testThree', 'testFour']],

            // Torture test
            // - incomplete TestResultCache
            // - skipped testThree: will move it to the front as far as possible
            // - testOne and testTwo are required before testThree, but can be reversed locally
            // - testFive is independent will remain reversed up front
            'reverse resolve, testThree skipped' => [
                TestSuiteSorter::ORDER_REVERSED,
                self::RESOLVE_DEPENDENCIES,
                [
                    'testOne'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testTwo'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testThree' => ['state' => BaseTestRunner::STATUS_SKIPPED, 'time' => 1],
                ],
                ['testFive', 'testTwo', 'testOne', 'testThree', 'testFour']],

            // Make sure the dependency resolver is not confused by failing tests.
            // Scenario: Four has a @depends on Three and fails. Result: Three is still run first
            // testFive also fails but can be moved around freely and will be up front.
            'depends first, then defects' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::RESOLVE_DEPENDENCIES,
                [
                    'testOne'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testTwo'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testThree' => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testFour'  => ['state' => BaseTestRunner::STATUS_FAILURE, 'time' => 1],
                    'testFive'  => ['state' => BaseTestRunner::STATUS_FAILURE, 'time' => 1],
                ],
                ['testFive', 'testOne', 'testTwo', 'testThree', 'testFour']],
        ];
    }

    /**
     * @see https://github.com/lstrojny/phpunit-clever-and-smart/issues/38
     */
    public function testCanHandleSuiteWithEmptyTestCase()
    {
        $suite = new TestSuite;
        $suite->addTestSuite(\EmptyTestCaseTest::class);

        $sorter = new TestSuiteSorter();

        $sorter->reorderTestsInSuite($suite, TestSuiteSorter::ORDER_DEFAULT, false, TestSuiteSorter::ORDER_DEFAULT);

        $this->assertSame(\EmptyTestCaseTest::class, $suite->tests()[0]->getName());
        $this->assertSame('No tests found in class "EmptyTestCaseTest".', $suite->tests()[0]->tests()[0]->getMessage());
    }

    public function suiteSorterOptionPermutationsProvider()
    {
        $orderValues        = [TestSuiteSorter::ORDER_DEFAULT, TestSuiteSorter::ORDER_REVERSED, TestSuiteSorter::ORDER_RANDOMIZED];
        $resolveValues      = [false, true];
        $orderDefectsValues = [TestSuiteSorter::ORDER_DEFAULT, TestSuiteSorter::ORDER_DEFECTS_FIRST];

        $data = [];

        foreach ($orderValues as $order) {
            foreach ($resolveValues as $resolve) {
                foreach ($orderDefectsValues as $orderDefects) {
                    $data[] = [$order, $resolve, $orderDefects];
                }
            }
        }

        return $data;
    }

    private function getTestExecutionOrder(TestSuite $suite): array
    {
        return \array_map(function ($test) {
            return $test->getName();
        }, $suite->tests()[0]->tests());
    }
}
