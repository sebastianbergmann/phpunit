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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\DataProviderTestSuite;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
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
