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

use function mt_srand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestStatus\TestStatus;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\ResultCache\DefaultResultCache;
use PHPUnit\Runner\ResultCache\ResultCacheId;
use PHPUnit\TestFixture\FaillingDataProviderTest;
use PHPUnit\TestFixture\MultiDependencyTest;
use ReflectionClass;

#[CoversClass(TestSuiteSorter::class)]
#[Small]
final class TestSuiteSorterTest extends TestCase
{
    private const IGNORE_DEPENDENCIES                   = false;
    private const RESOLVE_DEPENDENCIES                  = true;
    private const MULTI_DEPENDENCY_TEST_EXECUTION_ORDER = [
        MultiDependencyTest::class . '::testOne',
        MultiDependencyTest::class . '::testTwo',
        MultiDependencyTest::class . '::testThree',
        MultiDependencyTest::class . '::testFour',
        MultiDependencyTest::class . '::testFive',
    ];

    public static function orderDurationWithoutCacheProvider(): array
    {
        return [
            'dependency-ignore' => [
                self::IGNORE_DEPENDENCIES,
                [
                    MultiDependencyTest::class . '::testOne',
                    MultiDependencyTest::class . '::testTwo',
                    MultiDependencyTest::class . '::testThree',
                    MultiDependencyTest::class . '::testFour',
                    MultiDependencyTest::class . '::testFive',
                ],
            ],
            'dependency-resolve' => [
                self::RESOLVE_DEPENDENCIES,
                [
                    MultiDependencyTest::class . '::testOne',
                    MultiDependencyTest::class . '::testTwo',
                    MultiDependencyTest::class . '::testThree',
                    MultiDependencyTest::class . '::testFour',
                    MultiDependencyTest::class . '::testFive',
                ],
            ],
        ];
    }

    public static function orderDurationWithCacheProvider(): array
    {
        return [
            'duration-same-dependency-ignore' => [
                self::IGNORE_DEPENDENCIES,
                [
                    'testOne'   => 1,
                    'testTwo'   => 1,
                    'testThree' => 1,
                    'testFour'  => 1,
                    'testFive'  => 1,
                ],
                [
                    MultiDependencyTest::class . '::testOne',
                    MultiDependencyTest::class . '::testTwo',
                    MultiDependencyTest::class . '::testThree',
                    MultiDependencyTest::class . '::testFour',
                    MultiDependencyTest::class . '::testFive',
                ],
            ],
            'duration-same-dependency-resolve' => [
                self::RESOLVE_DEPENDENCIES,
                [
                    'testOne'   => 1,
                    'testTwo'   => 1,
                    'testThree' => 1,
                    'testFour'  => 1,
                    'testFive'  => 1,
                ],
                [
                    MultiDependencyTest::class . '::testOne',
                    MultiDependencyTest::class . '::testTwo',
                    MultiDependencyTest::class . '::testThree',
                    MultiDependencyTest::class . '::testFour',
                    MultiDependencyTest::class . '::testFive',
                ],
            ],
            'duration-different-dependency-ignore' => [
                self::IGNORE_DEPENDENCIES,
                [
                    'testOne'   => 5,
                    'testTwo'   => 3,
                    'testThree' => 4,
                    'testFour'  => 1,
                    'testFive'  => 2,
                ],
                [
                    MultiDependencyTest::class . '::testFour',
                    MultiDependencyTest::class . '::testFive',
                    MultiDependencyTest::class . '::testTwo',
                    MultiDependencyTest::class . '::testThree',
                    MultiDependencyTest::class . '::testOne',
                ],
            ],
            'duration-different-dependency-resolve' => [
                self::RESOLVE_DEPENDENCIES,
                [
                    'testOne'   => 5,
                    'testTwo'   => 3,
                    'testThree' => 4,
                    'testFour'  => 1,
                    'testFive'  => 2,
                ],
                [
                    MultiDependencyTest::class . '::testFive',
                    MultiDependencyTest::class . '::testTwo',
                    MultiDependencyTest::class . '::testOne',
                    MultiDependencyTest::class . '::testThree',
                    MultiDependencyTest::class . '::testFour',
                ],
            ],
        ];
    }

    /**
     * A data provider for basic execution reordering options based on MultiDependencyTest.
     *
     * This class has the following relevant properties:
     *
     * - it has five tests testOne, testTwo, testThree, testFour, testFive
     * - testThree depends on testOne and testTwo
     * - testFour depends on MultiDependencyTest::testThree
     * - testFive has no dependencies
     */
    public static function commonSorterOptionsProvider(): array
    {
        return [
            'default' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::IGNORE_DEPENDENCIES,
                [
                    MultiDependencyTest::class . '::testOne',
                    MultiDependencyTest::class . '::testTwo',
                    MultiDependencyTest::class . '::testThree',
                    MultiDependencyTest::class . '::testFour',
                    MultiDependencyTest::class . '::testFive',
                ],
            ],

            // Activating dependency resolution should have no effect under normal circumstances
            'resolve default' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::RESOLVE_DEPENDENCIES,
                [
                    MultiDependencyTest::class . '::testOne',
                    MultiDependencyTest::class . '::testTwo',
                    MultiDependencyTest::class . '::testThree',
                    MultiDependencyTest::class . '::testFour',
                    MultiDependencyTest::class . '::testFive',
                ],
            ],

            // Reversing without checks should give a simple reverse order
            'reverse' => [
                TestSuiteSorter::ORDER_REVERSED,
                self::IGNORE_DEPENDENCIES,
                [
                    MultiDependencyTest::class . '::testFive',
                    MultiDependencyTest::class . '::testFour',
                    MultiDependencyTest::class . '::testThree',
                    MultiDependencyTest::class . '::testTwo',
                    MultiDependencyTest::class . '::testOne',
                ],
            ],

            // Reversing with resolution still allows testFive to move to front, testTwo before testOne
            'resolve reverse' => [
                TestSuiteSorter::ORDER_REVERSED,
                self::RESOLVE_DEPENDENCIES,
                [
                    MultiDependencyTest::class . '::testFive',
                    MultiDependencyTest::class . '::testTwo',
                    MultiDependencyTest::class . '::testOne',
                    MultiDependencyTest::class . '::testThree',
                    MultiDependencyTest::class . '::testFour',
                ],
            ],
        ];
    }

    /**
     * A data provider for testing defects execution reordering options based on MultiDependencyTest.
     *
     * This class has the following relevant properties:
     *
     * - it has five tests testOne, testTwo, testThree, testFour, testFive
     * - testThree depends on testOne and testTwo
     * - testFour depends on MultiDependencyTest::testThree
     * - testFive has no dependencies
     */
    public static function defectsSorterOptionsProvider(): array
    {
        return [
            // The most simple situation should work as normal
            'default, no defects' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::IGNORE_DEPENDENCIES,
                [
                    'testOne'   => ['state' => TestStatus::success(), 'time' => 1],
                    'testTwo'   => ['state' => TestStatus::success(), 'time' => 1],
                    'testThree' => ['state' => TestStatus::success(), 'time' => 1],
                    'testFour'  => ['state' => TestStatus::success(), 'time' => 1],
                    'testFive'  => ['state' => TestStatus::success(), 'time' => 1],
                ],
                [
                    MultiDependencyTest::class . '::testOne',
                    MultiDependencyTest::class . '::testTwo',
                    MultiDependencyTest::class . '::testThree',
                    MultiDependencyTest::class . '::testFour',
                    MultiDependencyTest::class . '::testFive',
                ],
            ],

            // Running with an empty cache should not spook the TestSuiteSorter
            'default, empty result cache' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::IGNORE_DEPENDENCIES,
                [
                    // empty result cache
                ],
                [
                    MultiDependencyTest::class . '::testOne',
                    MultiDependencyTest::class . '::testTwo',
                    MultiDependencyTest::class . '::testThree',
                    MultiDependencyTest::class . '::testFour',
                    MultiDependencyTest::class . '::testFive',
                ],
            ],

            // testFive is independent and can be moved to the front
            'default, testFive skipped' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::IGNORE_DEPENDENCIES,
                [
                    'testOne'   => ['state' => TestStatus::success(), 'time' => 1],
                    'testTwo'   => ['state' => TestStatus::success(), 'time' => 1],
                    'testThree' => ['state' => TestStatus::success(), 'time' => 1],
                    'testFour'  => ['state' => TestStatus::success(), 'time' => 1],
                    'testFive'  => ['state' => TestStatus::skipped(), 'time' => 1],
                ],
                [
                    MultiDependencyTest::class . '::testFive',
                    MultiDependencyTest::class . '::testOne',
                    MultiDependencyTest::class . '::testTwo',
                    MultiDependencyTest::class . '::testThree',
                    MultiDependencyTest::class . '::testFour',
                ],
            ],

            // Defects in testFive and testTwo, but the faster testFive should be run first
            'default, testTwo testFive skipped' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::IGNORE_DEPENDENCIES,
                [
                    'testOne'   => ['state' => TestStatus::success(), 'time' => 1],
                    'testTwo'   => ['state' => TestStatus::skipped(), 'time' => 1],
                    'testThree' => ['state' => TestStatus::success(), 'time' => 1],
                    'testFour'  => ['state' => TestStatus::success(), 'time' => 1],
                    'testFive'  => ['state' => TestStatus::skipped(), 'time' => 0],
                ],
                [
                    MultiDependencyTest::class . '::testFive',
                    MultiDependencyTest::class . '::testTwo',
                    MultiDependencyTest::class . '::testOne',
                    MultiDependencyTest::class . '::testThree',
                    MultiDependencyTest::class . '::testFour',
                ],
            ],

            // Skipping testThree will move it to the front when ignoring dependencies
            'default, testThree skipped' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::IGNORE_DEPENDENCIES,
                [
                    'testOne'   => ['state' => TestStatus::success(), 'time' => 1],
                    'testTwo'   => ['state' => TestStatus::success(), 'time' => 1],
                    'testThree' => ['state' => TestStatus::skipped(), 'time' => 1],
                    'testFour'  => ['state' => TestStatus::success(), 'time' => 1],
                    'testFive'  => ['state' => TestStatus::success(), 'time' => 1],
                ],
                [
                    MultiDependencyTest::class . '::testThree',
                    MultiDependencyTest::class . '::testOne',
                    MultiDependencyTest::class . '::testTwo',
                    MultiDependencyTest::class . '::testFour',
                    MultiDependencyTest::class . '::testFive',
                ],
            ],

            // Skipping testThree will move it to the front but behind its dependencies
            'default resolve, testThree skipped' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::RESOLVE_DEPENDENCIES,
                [
                    'testOne'   => ['state' => TestStatus::success(), 'time' => 1],
                    'testTwo'   => ['state' => TestStatus::success(), 'time' => 1],
                    'testThree' => ['state' => TestStatus::skipped(), 'time' => 1],
                    'testFour'  => ['state' => TestStatus::success(), 'time' => 1],
                    'testFive'  => ['state' => TestStatus::success(), 'time' => 1],
                ],
                [
                    MultiDependencyTest::class . '::testOne',
                    MultiDependencyTest::class . '::testTwo',
                    MultiDependencyTest::class . '::testThree',
                    MultiDependencyTest::class . '::testFour',
                    MultiDependencyTest::class . '::testFive',
                ],
            ],

            // Skipping testThree will move it to the front and keep the others reversed
            'reverse, testThree skipped' => [
                TestSuiteSorter::ORDER_REVERSED,
                self::IGNORE_DEPENDENCIES,
                [
                    'testOne'   => ['state' => TestStatus::success(), 'time' => 1],
                    'testTwo'   => ['state' => TestStatus::success(), 'time' => 1],
                    'testThree' => ['state' => TestStatus::skipped(), 'time' => 1],
                    'testFour'  => ['state' => TestStatus::success(), 'time' => 1],
                    'testFive'  => ['state' => TestStatus::success(), 'time' => 1],
                ],
                [
                    MultiDependencyTest::class . '::testThree',
                    MultiDependencyTest::class . '::testFive',
                    MultiDependencyTest::class . '::testFour',
                    MultiDependencyTest::class . '::testTwo',
                    MultiDependencyTest::class . '::testOne',
                ],
            ],

            // Demonstrate a limit of the dependency resolver: after sorting defects to the front,
            // the resolver will mark testFive done before testThree because of dependencies
            'default resolve, testThree skipped, testFive fast' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::RESOLVE_DEPENDENCIES,
                [
                    'testOne'   => ['state' => TestStatus::success(), 'time' => 1],
                    'testTwo'   => ['state' => TestStatus::success(), 'time' => 1],
                    'testThree' => ['state' => TestStatus::skipped(), 'time' => 0],
                    'testFour'  => ['state' => TestStatus::success(), 'time' => 1],
                    'testFive'  => ['state' => TestStatus::skipped(), 'time' => 1],
                ],
                [
                    MultiDependencyTest::class . '::testFive',
                    MultiDependencyTest::class . '::testOne',
                    MultiDependencyTest::class . '::testTwo',
                    MultiDependencyTest::class . '::testThree',
                    MultiDependencyTest::class . '::testFour',
                ],
            ],

            // Torture test
            // - incomplete TestResultCache
            // - skipped testThree: will move it to the front as far as possible
            // - testOne and testTwo are required before testThree, but can be reversed locally
            // - testFive is independent will remain reversed up front
            'reverse resolve, testThree skipped' => [
                TestSuiteSorter::ORDER_REVERSED,
                self::RESOLVE_DEPENDENCIES,
                [
                    'testOne'   => ['state' => TestStatus::success(), 'time' => 1],
                    'testTwo'   => ['state' => TestStatus::success(), 'time' => 1],
                    'testThree' => ['state' => TestStatus::skipped(), 'time' => 1],
                ],
                [
                    MultiDependencyTest::class . '::testFive',
                    MultiDependencyTest::class . '::testTwo',
                    MultiDependencyTest::class . '::testOne',
                    MultiDependencyTest::class . '::testThree',
                    MultiDependencyTest::class . '::testFour',
                ],
            ],

            // Make sure the dependency resolver is not confused by failing tests.
            // Scenario: Four has a @depends on Three and fails. Result: Three is still run first
            // testFive also fails but can be moved around freely and will be up front.
            'depends first, then defects' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::RESOLVE_DEPENDENCIES,
                [
                    'testOne'   => ['state' => TestStatus::success(), 'time' => 1],
                    'testTwo'   => ['state' => TestStatus::success(), 'time' => 1],
                    'testThree' => ['state' => TestStatus::success(), 'time' => 1],
                    'testFour'  => ['state' => TestStatus::failure(), 'time' => 1],
                    'testFive'  => ['state' => TestStatus::failure(), 'time' => 1],
                ],
                [
                    MultiDependencyTest::class . '::testFive',
                    MultiDependencyTest::class . '::testOne',
                    MultiDependencyTest::class . '::testTwo',
                    MultiDependencyTest::class . '::testThree',
                    MultiDependencyTest::class . '::testFour',
                ],
            ],
        ];
    }

    public static function suiteSorterOptionPermutationsProvider(): array
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

    /**
     * A data provider for testing defects execution reordering options based on FaillingDataProviderTest.
     */
    public static function defectsSorterWithDataProviderProvider(): array
    {
        return [
            // The most simple situation should work as normal
            'default, no defects' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::IGNORE_DEPENDENCIES,
                [
                    'testOne'                                => ['state' => TestStatus::success(), 'time' => 1],
                    'testWithProvider with data set "good1"' => ['state' => TestStatus::success(), 'time' => 1],
                    'testWithProvider with data set "good2"' => ['state' => TestStatus::success(), 'time' => 1],
                    'testWithProvider with data set "good3"' => ['state' => TestStatus::success(), 'time' => 1],
                    'testWithProvider with data set "fail1"' => ['state' => TestStatus::success(), 'time' => 1],
                    'testWithProvider with data set "fail2"' => ['state' => TestStatus::success(), 'time' => 1],
                ],
                [
                    FaillingDataProviderTest::class . '::testWithProvider with data set "good1"',
                    FaillingDataProviderTest::class . '::testWithProvider with data set "good2"',
                    FaillingDataProviderTest::class . '::testWithProvider with data set "fail1"',
                    FaillingDataProviderTest::class . '::testWithProvider with data set "good3"',
                    FaillingDataProviderTest::class . '::testWithProvider with data set "fail2"',
                    FaillingDataProviderTest::class . '::testOne',
                ],
            ],

            // Running with an empty cache should not spook the TestSuiteSorter
            'default, empty result cache' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::IGNORE_DEPENDENCIES,
                [
                    // empty result cache
                ],
                [
                    FaillingDataProviderTest::class . '::testWithProvider with data set "good1"',
                    FaillingDataProviderTest::class . '::testWithProvider with data set "good2"',
                    FaillingDataProviderTest::class . '::testWithProvider with data set "fail1"',
                    FaillingDataProviderTest::class . '::testWithProvider with data set "good3"',
                    FaillingDataProviderTest::class . '::testWithProvider with data set "fail2"',
                    FaillingDataProviderTest::class . '::testOne',
                ],
            ],

            'default, defects' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::IGNORE_DEPENDENCIES,
                [
                    'testOne'                                => ['state' => TestStatus::success(), 'time' => 1],
                    'testWithProvider with data set "good1"' => ['state' => TestStatus::success(), 'time' => 1],
                    'testWithProvider with data set "good2"' => ['state' => TestStatus::success(), 'time' => 1],
                    'testWithProvider with data set "good3"' => ['state' => TestStatus::success(), 'time' => 1],
                    'testWithProvider with data set "fail1"' => ['state' => TestStatus::error(), 'time' => 1],
                    'testWithProvider with data set "fail2"' => ['state' => TestStatus::error(), 'time' => 1],
                ],
                [
                    FaillingDataProviderTest::class . '::testWithProvider with data set "fail1"',
                    FaillingDataProviderTest::class . '::testWithProvider with data set "fail2"',
                    FaillingDataProviderTest::class . '::testWithProvider with data set "good1"',
                    FaillingDataProviderTest::class . '::testWithProvider with data set "good2"',
                    FaillingDataProviderTest::class . '::testWithProvider with data set "good3"',
                    FaillingDataProviderTest::class . '::testOne',
                ],
            ],
        ];
    }

    public function testThrowsExceptionWhenUsingInvalidOrderOption(): void
    {
        $suite = TestSuite::empty('test suite name');
        $suite->addTestSuite(new ReflectionClass(MultiDependencyTest::class));
        $sorter = new TestSuiteSorter;

        $this->expectException(InvalidOrderException::class);

        $sorter->reorderTestsInSuite($suite, -1, false, TestSuiteSorter::ORDER_DEFAULT);
    }

    public function testThrowsExceptionWhenUsingInvalidOrderDefectsOption(): void
    {
        $suite = TestSuite::empty('test suite name');
        $suite->addTestSuite(new ReflectionClass(MultiDependencyTest::class));
        $sorter = new TestSuiteSorter;

        $this->expectException(InvalidOrderException::class);

        $sorter->reorderTestsInSuite($suite, TestSuiteSorter::ORDER_DEFAULT, false, -1);
    }

    #[DataProvider('suiteSorterOptionPermutationsProvider')]
    public function testShouldNotAffectEmptyTestSuite(int $order, bool $resolveDependencies, int $orderDefects): void
    {
        $sorter = new TestSuiteSorter;
        $suite  = TestSuite::empty('test suite name');

        $sorter->reorderTestsInSuite($suite, $order, $resolveDependencies, $orderDefects);

        $this->assertEmpty($suite->tests());
        $this->assertEmpty($sorter->getOriginalExecutionOrder());
        $this->assertEmpty($sorter->getExecutionOrder());
    }

    #[DataProvider('commonSorterOptionsProvider')]
    public function testBasicExecutionOrderOptions(int $order, bool $resolveDependencies, array $expectedOrder): void
    {
        $suite = TestSuite::empty('test suite name');
        $suite->addTestSuite(new ReflectionClass(MultiDependencyTest::class));
        $sorter = new TestSuiteSorter;

        $sorter->reorderTestsInSuite($suite, $order, $resolveDependencies, TestSuiteSorter::ORDER_DEFAULT);

        $this->assertSame(self::MULTI_DEPENDENCY_TEST_EXECUTION_ORDER, $sorter->getOriginalExecutionOrder());
        $this->assertSame($expectedOrder, $sorter->getExecutionOrder());
    }

    public function testCanSetRandomizationWithASeed(): void
    {
        $suite = TestSuite::empty('test suite name');
        $suite->addTestSuite(new ReflectionClass(MultiDependencyTest::class));
        $sorter = new TestSuiteSorter;

        mt_srand(54321);
        $sorter->reorderTestsInSuite($suite, TestSuiteSorter::ORDER_RANDOMIZED, false, TestSuiteSorter::ORDER_DEFAULT);

        $expectedOrder = [
            MultiDependencyTest::class . '::testTwo',
            MultiDependencyTest::class . '::testFour',
            MultiDependencyTest::class . '::testFive',
            MultiDependencyTest::class . '::testThree',
            MultiDependencyTest::class . '::testOne',
        ];

        $this->assertSame($expectedOrder, $sorter->getExecutionOrder());
    }

    public function testCanSetRandomizationWithDefectsFirst(): void
    {
        $cache = new DefaultResultCache;

        $runState = [
            'testOne'   => ['state' => TestStatus::success(), 'time' => 1],
            'testTwo'   => ['state' => TestStatus::success(), 'time' => 1],
            'testThree' => ['state' => TestStatus::error(), 'time' => 1],
            'testFour'  => ['state' => TestStatus::success(), 'time' => 1],
            'testFive'  => ['state' => TestStatus::error(), 'time' => 1],
        ];

        foreach ($runState as $testName => $data) {
            $cache->setStatus(ResultCacheId::fromTestClassAndMethodName(MultiDependencyTest::class, $testName), $data['state']);
            $cache->setTime(ResultCacheId::fromTestClassAndMethodName(MultiDependencyTest::class, $testName), $data['time']);
        }

        $sorter = new TestSuiteSorter($cache);

        $suite = TestSuite::empty('test suite name');
        $suite->addTestSuite(new ReflectionClass(MultiDependencyTest::class));

        mt_srand(54321);
        $sorter->reorderTestsInSuite($suite, TestSuiteSorter::ORDER_RANDOMIZED, false, TestSuiteSorter::ORDER_DEFECTS_FIRST);

        $expectedOrder = [
            MultiDependencyTest::class . '::testFive',
            MultiDependencyTest::class . '::testThree',
            MultiDependencyTest::class . '::testTwo',
            MultiDependencyTest::class . '::testFour',
            MultiDependencyTest::class . '::testOne',
        ];

        $this->assertSame($expectedOrder, $sorter->getExecutionOrder());
    }

    public function testCanSetRandomizationWithASeedAndResolveDependencies(): void
    {
        $suite = TestSuite::empty('test suite name');
        $suite->addTestSuite(new ReflectionClass(MultiDependencyTest::class));
        $sorter = new TestSuiteSorter;

        mt_srand(54321);
        $sorter->reorderTestsInSuite($suite, TestSuiteSorter::ORDER_RANDOMIZED, true, TestSuiteSorter::ORDER_DEFAULT);

        $expectedOrder = [
            MultiDependencyTest::class . '::testTwo',
            MultiDependencyTest::class . '::testFive',
            MultiDependencyTest::class . '::testOne',
            MultiDependencyTest::class . '::testThree',
            MultiDependencyTest::class . '::testFour',
        ];

        $this->assertSame($expectedOrder, $sorter->getExecutionOrder());
    }

    #[DataProvider('orderDurationWithoutCacheProvider')]
    public function testOrderDurationWithoutCache(bool $resolveDependencies, array $expected): void
    {
        $suite = TestSuite::empty('test suite name');

        $suite->addTestSuite(new ReflectionClass(MultiDependencyTest::class));

        $sorter = new TestSuiteSorter;

        $sorter->reorderTestsInSuite(
            $suite,
            TestSuiteSorter::ORDER_DURATION,
            $resolveDependencies,
            TestSuiteSorter::ORDER_DEFAULT,
        );

        $this->assertSame($expected, $sorter->getExecutionOrder());
    }

    #[DataProvider('orderDurationWithCacheProvider')]
    public function testOrderDurationWithCache(bool $resolveDependencies, array $testTimes, array $expected): void
    {
        $suite = TestSuite::empty('test suite name');

        $suite->addTestSuite(new ReflectionClass(MultiDependencyTest::class));

        $cache = new DefaultResultCache;

        foreach ($testTimes as $testName => $time) {
            $cache->setTime(ResultCacheId::fromTestClassAndMethodName(MultiDependencyTest::class, $testName), $time);
        }

        $sorter = new TestSuiteSorter($cache);

        $sorter->reorderTestsInSuite(
            $suite,
            TestSuiteSorter::ORDER_DURATION,
            $resolveDependencies,
            TestSuiteSorter::ORDER_DEFAULT,
        );

        $this->assertSame($expected, $sorter->getExecutionOrder());
    }

    #[DataProvider('defectsSorterOptionsProvider')]
    public function testSuiteSorterDefectsOptions(int $order, bool $resolveDependencies, array $runState, array $expected): void
    {
        $suite = TestSuite::empty('test suite name');
        $suite->addTestSuite(new ReflectionClass(MultiDependencyTest::class));

        $cache = new DefaultResultCache;

        foreach ($runState as $testName => $data) {
            $cache->setStatus(ResultCacheId::fromTestClassAndMethodName(MultiDependencyTest::class, $testName), $data['state']);
            $cache->setTime(ResultCacheId::fromTestClassAndMethodName(MultiDependencyTest::class, $testName), $data['time']);
        }

        $sorter = new TestSuiteSorter($cache);
        $sorter->reorderTestsInSuite($suite, $order, $resolveDependencies, TestSuiteSorter::ORDER_DEFECTS_FIRST);

        $this->assertSame($expected, $sorter->getExecutionOrder());
    }

    #[DataProvider('defectsSorterWithDataProviderProvider')]
    public function testSuiteSorterDefectsWithDataProviderTest(int $order, bool $resolveDependencies, array $runState, array $expected): void
    {
        $suite = TestSuite::empty('test suite name');
        $suite->addTestSuite(new ReflectionClass(FaillingDataProviderTest::class));

        $cache = new DefaultResultCache;

        foreach ($runState as $testName => $data) {
            $cache->setStatus(ResultCacheId::fromTestClassAndMethodName(FaillingDataProviderTest::class, $testName), $data['state']);
            $cache->setTime(ResultCacheId::fromTestClassAndMethodName(FaillingDataProviderTest::class, $testName), $data['time']);
        }

        $sorter = new TestSuiteSorter($cache);
        $sorter->reorderTestsInSuite($suite, $order, $resolveDependencies, TestSuiteSorter::ORDER_DEFECTS_FIRST);

        $this->assertSame($expected, $sorter->getExecutionOrder());
    }
}
