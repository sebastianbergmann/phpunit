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

use function sys_get_temp_dir;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\DataProviderTestSuite;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestStatus\TestStatus;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\ResultCache\DefaultResultCache;
use PHPUnit\Runner\ResultCache\ResultCacheId;
use PHPUnit\TestFixture\LargeGroupAttributesTest;
use PHPUnit\TestFixture\MediumGroupAttributesTest;
use PHPUnit\TestFixture\NonReorderableTest;
use PHPUnit\TestFixture\SmallGroupAttributesTest;
use PHPUnit\TestFixture\SmallTestWithDataProvider;
use ReflectionClass;

#[CoversClass(TestSuiteSorter::class)]
#[CoversClass(DataProviderTestSuite::class)]
#[Small]
final class TestSuiteSorterTest extends TestCase
{
    public function testSortsBySize(): void
    {
        $large  = new LargeGroupAttributesTest('testOne');
        $small  = new SmallGroupAttributesTest('testOne');
        $medium = new MediumGroupAttributesTest('testOne');

        $suite = TestSuite::empty('test');
        $suite->setTests([$large, $small, $medium]);

        $sorter = new TestSuiteSorter;
        $sorter->reorderTestsInSuite($suite, TestSuiteSorter::ORDER_SIZE_ASCENDING, false, TestSuiteSorter::ORDER_DEFAULT);

        $tests = $suite->tests();

        $this->assertSame($small, $tests[0]);
        $this->assertSame($medium, $tests[1]);
        $this->assertSame($large, $tests[2]);
    }

    public function testSortsBySizeDescending(): void
    {
        $large  = new LargeGroupAttributesTest('testOne');
        $small  = new SmallGroupAttributesTest('testOne');
        $medium = new MediumGroupAttributesTest('testOne');

        $suite = TestSuite::empty('test');
        $suite->setTests([$large, $small, $medium]);

        $sorter = new TestSuiteSorter;
        $sorter->reorderTestsInSuite($suite, TestSuiteSorter::ORDER_SIZE_DESCENDING, false, TestSuiteSorter::ORDER_DEFAULT);

        $tests = $suite->tests();

        $this->assertSame($large, $tests[0]);
        $this->assertSame($medium, $tests[1]);
        $this->assertSame($small, $tests[2]);
    }

    public function testSortsBySizeWithDataProviderTestSuite(): void
    {
        $classReflector = new ReflectionClass(SmallTestWithDataProvider::class);
        $smallSuite     = TestSuite::fromClassReflector($classReflector);

        $dataProviderTestSuite = null;

        foreach ($smallSuite->tests() as $test) {
            if ($test instanceof DataProviderTestSuite) {
                $dataProviderTestSuite = $test;

                break;
            }
        }

        $this->assertNotNull($dataProviderTestSuite);

        $large = new LargeGroupAttributesTest('testOne');

        $suite = TestSuite::empty('test');
        $suite->setTests([$large, $dataProviderTestSuite]);

        $sorter = new TestSuiteSorter;
        $sorter->reorderTestsInSuite($suite, TestSuiteSorter::ORDER_SIZE_ASCENDING, false, TestSuiteSorter::ORDER_DEFAULT);

        $tests = $suite->tests();

        $this->assertSame($dataProviderTestSuite, $tests[0]);
        $this->assertSame($large, $tests[1]);
    }

    public function testSortByDurationWithNonReorderableTest(): void
    {
        $nonReorderable = new NonReorderableTest;
        $testCase       = new SmallGroupAttributesTest('testOne');

        $suite = TestSuite::empty('test');
        $suite->setTests([$nonReorderable, $testCase]);

        $sorter = new TestSuiteSorter;
        $sorter->reorderTestsInSuite($suite, TestSuiteSorter::ORDER_DURATION_ASCENDING, false, TestSuiteSorter::ORDER_DEFAULT);

        $tests = $suite->tests();

        $this->assertCount(2, $tests);
        $this->assertSame($nonReorderable, $tests[0]);
        $this->assertSame($testCase, $tests[1]);
    }

    public function testSortByDurationDescendingWithNonReorderableTest(): void
    {
        $nonReorderable = new NonReorderableTest;
        $testCase       = new SmallGroupAttributesTest('testOne');

        $suite = TestSuite::empty('test');
        $suite->setTests([$nonReorderable, $testCase]);

        $sorter = new TestSuiteSorter;
        $sorter->reorderTestsInSuite($suite, TestSuiteSorter::ORDER_DURATION_DESCENDING, false, TestSuiteSorter::ORDER_DEFAULT);

        $tests = $suite->tests();

        $this->assertCount(2, $tests);
        $this->assertSame($nonReorderable, $tests[0]);
        $this->assertSame($testCase, $tests[1]);
    }

    public function testDefectsFirstKeepsOrderOfNonDefectiveTests(): void
    {
        $small  = new SmallGroupAttributesTest('testOne');
        $medium = new MediumGroupAttributesTest('testOne');
        $large  = new LargeGroupAttributesTest('testOne');

        $suite = TestSuite::empty('test');
        $suite->setTests([$small, $medium, $large]);

        $cache = new DefaultResultCache(sys_get_temp_dir());
        $cache->setTime(ResultCacheId::fromReorderable($small), 0.1);
        $cache->setTime(ResultCacheId::fromReorderable($medium), 0.2);
        $cache->setTime(ResultCacheId::fromReorderable($large), 0.3);

        $sorter = new TestSuiteSorter($cache);
        $sorter->reorderTestsInSuite($suite, TestSuiteSorter::ORDER_DEFAULT, false, TestSuiteSorter::ORDER_DEFECTS_FIRST);

        $tests = $suite->tests();

        $this->assertSame($small, $tests[0]);
        $this->assertSame($medium, $tests[1]);
        $this->assertSame($large, $tests[2]);
    }

    public function testDefectsFirstDoesNotHoistSkippedTests(): void
    {
        $small  = new SmallGroupAttributesTest('testOne');
        $medium = new MediumGroupAttributesTest('testOne');
        $large  = new LargeGroupAttributesTest('testOne');

        $suite = TestSuite::empty('test');
        $suite->setTests([$small, $medium, $large]);

        $cache = new DefaultResultCache(sys_get_temp_dir());
        $cache->setStatus(ResultCacheId::fromReorderable($large), TestStatus::skipped());

        $sorter = new TestSuiteSorter($cache);
        $sorter->reorderTestsInSuite($suite, TestSuiteSorter::ORDER_DEFAULT, false, TestSuiteSorter::ORDER_DEFECTS_FIRST);

        $tests = $suite->tests();

        $this->assertSame($small, $tests[0]);
        $this->assertSame($medium, $tests[1]);
        $this->assertSame($large, $tests[2]);
    }

    public function testDefectsFirstDoesNotHoistIncompleteTests(): void
    {
        $small  = new SmallGroupAttributesTest('testOne');
        $medium = new MediumGroupAttributesTest('testOne');
        $large  = new LargeGroupAttributesTest('testOne');

        $suite = TestSuite::empty('test');
        $suite->setTests([$small, $medium, $large]);

        $cache = new DefaultResultCache(sys_get_temp_dir());
        $cache->setStatus(ResultCacheId::fromReorderable($large), TestStatus::incomplete());

        $sorter = new TestSuiteSorter($cache);
        $sorter->reorderTestsInSuite($suite, TestSuiteSorter::ORDER_DEFAULT, false, TestSuiteSorter::ORDER_DEFECTS_FIRST);

        $tests = $suite->tests();

        $this->assertSame($small, $tests[0]);
        $this->assertSame($medium, $tests[1]);
        $this->assertSame($large, $tests[2]);
    }

    public function testDefectsFirstHoistsFailingTest(): void
    {
        $small  = new SmallGroupAttributesTest('testOne');
        $medium = new MediumGroupAttributesTest('testOne');
        $large  = new LargeGroupAttributesTest('testOne');

        $suite = TestSuite::empty('test');
        $suite->setTests([$small, $medium, $large]);

        $cache = new DefaultResultCache(sys_get_temp_dir());
        $cache->setStatus(ResultCacheId::fromReorderable($large), TestStatus::failure());

        $sorter = new TestSuiteSorter($cache);
        $sorter->reorderTestsInSuite($suite, TestSuiteSorter::ORDER_DEFAULT, false, TestSuiteSorter::ORDER_DEFECTS_FIRST);

        $tests = $suite->tests();

        $this->assertSame($large, $tests[0]);
        $this->assertSame($small, $tests[1]);
        $this->assertSame($medium, $tests[2]);
    }

    public function testDefectsFirstPreservesChildSuiteOrderWhenBothContainEqualDefects(): void
    {
        $unitTest     = new SmallGroupAttributesTest('testOne');
        $endToEndTest = new LargeGroupAttributesTest('testOne');

        $unitSuite = TestSuite::empty('unit');
        $unitSuite->setTests([$unitTest]);

        $endToEndSuite = TestSuite::empty('end-to-end');
        $endToEndSuite->setTests([$endToEndTest]);

        $parent = TestSuite::empty('test');
        $parent->setTests([$unitSuite, $endToEndSuite]);

        $cache = new DefaultResultCache(sys_get_temp_dir());
        $cache->setStatus(ResultCacheId::fromReorderable($unitTest), TestStatus::failure());
        $cache->setStatus(ResultCacheId::fromReorderable($endToEndTest), TestStatus::failure());
        $cache->setTime(ResultCacheId::fromReorderable($unitTest), 0.01);
        $cache->setTime(ResultCacheId::fromReorderable($endToEndTest), 10.0);

        $sorter = new TestSuiteSorter($cache);
        $sorter->reorderTestsInSuite($parent, TestSuiteSorter::ORDER_DEFAULT, false, TestSuiteSorter::ORDER_DEFECTS_FIRST);

        $tests = $parent->tests();

        $this->assertSame($unitSuite, $tests[0]);
        $this->assertSame($endToEndSuite, $tests[1]);
    }

    public function testDefectsFirstHoistsChildSuiteContainingNestedFailingTest(): void
    {
        $failingTest = new LargeGroupAttributesTest('testOne');
        $passingTest = new SmallGroupAttributesTest('testOne');

        $innerFailing = TestSuite::empty('inner-failing');
        $innerFailing->setTests([$failingTest]);

        $outerFailing = TestSuite::empty('outer-failing');
        $outerFailing->setTests([$innerFailing]);

        $outerPassing = TestSuite::empty('outer-passing');
        $outerPassing->setTests([$passingTest]);

        $parent = TestSuite::empty('parent');
        $parent->setTests([$outerPassing, $outerFailing]);

        $cache = new DefaultResultCache(sys_get_temp_dir());
        $cache->setStatus(ResultCacheId::fromReorderable($failingTest), TestStatus::failure());

        $sorter = new TestSuiteSorter($cache);
        $sorter->reorderTestsInSuite($parent, TestSuiteSorter::ORDER_DEFAULT, false, TestSuiteSorter::ORDER_DEFECTS_FIRST);

        $tests = $parent->tests();

        $this->assertSame($outerFailing, $tests[0]);
        $this->assertSame($outerPassing, $tests[1]);
    }

    public function testDefectsFirstHoistsClassSuiteContainingFailingDataProviderTest(): void
    {
        $classReflector = new ReflectionClass(SmallTestWithDataProvider::class);
        $classSuite     = TestSuite::fromClassReflector($classReflector);

        $dataProviderTestSuite = null;

        foreach ($classSuite->tests() as $test) {
            if ($test instanceof DataProviderTestSuite) {
                $dataProviderTestSuite = $test;

                break;
            }
        }

        $this->assertInstanceOf(DataProviderTestSuite::class, $dataProviderTestSuite);

        $failingDataSet = $dataProviderTestSuite->tests()[0];

        $passingClassSuite = TestSuite::empty('passing');
        $passingClassSuite->setTests([new SmallGroupAttributesTest('testOne')]);

        $parent = TestSuite::empty('parent');
        $parent->setTests([$passingClassSuite, $classSuite]);

        $cache = new DefaultResultCache(sys_get_temp_dir());
        $cache->setStatus(ResultCacheId::fromReorderable($failingDataSet), TestStatus::failure());

        $sorter = new TestSuiteSorter($cache);
        $sorter->reorderTestsInSuite($parent, TestSuiteSorter::ORDER_DEFAULT, false, TestSuiteSorter::ORDER_DEFECTS_FIRST);

        $tests = $parent->tests();

        $this->assertSame($classSuite, $tests[0]);
        $this->assertSame($passingClassSuite, $tests[1]);
    }

    public function testSortBySizeAssignsUnknownToPlainTestSuite(): void
    {
        $suiteA = TestSuite::empty('A');
        $suiteB = TestSuite::empty('B');

        $parent = TestSuite::empty('test');
        $parent->setTests([$suiteA, $suiteB]);

        $sorter = new TestSuiteSorter;
        $sorter->reorderTestsInSuite($parent, TestSuiteSorter::ORDER_SIZE_ASCENDING, false, TestSuiteSorter::ORDER_DEFAULT);

        $tests = $parent->tests();

        $this->assertSame($suiteA, $tests[0]);
        $this->assertSame($suiteB, $tests[1]);
    }
}
