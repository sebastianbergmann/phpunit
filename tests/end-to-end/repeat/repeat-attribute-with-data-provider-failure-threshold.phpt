--TEST--
#[Repeat] with data provider applies the failure threshold per data set
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/RepeatAttributeWithDataProviderTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Data Provider Method Called (PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest::provider for test method PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest::testWithDataProvider)
Data Provider Method Finished for PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest::testWithDataProvider:
- PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest::provider
Test Suite Loaded (6 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (6 tests)
Test Suite Started (PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest, 6 tests)
Test Suite for Test Method with Data Provider Started (PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest::testWithDataProvider, 6 data sets)
Test Suite for Repeated Test Method Started (PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest::testWithDataProvider#one, 3 repetitions)
Test Preparation Started (PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest::testWithDataProvider#one (repetition 1 of 3))
Test Prepared (PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest::testWithDataProvider#one (repetition 1 of 3))
Test Failed (PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest::testWithDataProvider#one (repetition 1 of 3))
Failure on repetition 1 of data set one
Test Finished (PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest::testWithDataProvider#one (repetition 1 of 3))
Test Preparation Started (PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest::testWithDataProvider#one (repetition 2 of 3))
Test Prepared (PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest::testWithDataProvider#one (repetition 2 of 3))
Test Failed (PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest::testWithDataProvider#one (repetition 2 of 3))
Failure on repetition 2 of data set one
Test Finished (PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest::testWithDataProvider#one (repetition 2 of 3))
Test Skipped (PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest::testWithDataProvider#one (repetition 3 of 3))
Remaining repetition skipped after failure in repetition 2
Test Suite for Repeated Test Method Finished (PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest::testWithDataProvider#one, 3 repetitions)
Test Suite for Repeated Test Method Started (PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest::testWithDataProvider#two, 3 repetitions)
Test Preparation Started (PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest::testWithDataProvider#two (repetition 1 of 3))
Test Prepared (PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest::testWithDataProvider#two (repetition 1 of 3))
Test Passed (PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest::testWithDataProvider#two (repetition 1 of 3))
Test Finished (PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest::testWithDataProvider#two (repetition 1 of 3))
Test Preparation Started (PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest::testWithDataProvider#two (repetition 2 of 3))
Test Prepared (PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest::testWithDataProvider#two (repetition 2 of 3))
Test Passed (PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest::testWithDataProvider#two (repetition 2 of 3))
Test Finished (PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest::testWithDataProvider#two (repetition 2 of 3))
Test Preparation Started (PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest::testWithDataProvider#two (repetition 3 of 3))
Test Prepared (PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest::testWithDataProvider#two (repetition 3 of 3))
Test Passed (PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest::testWithDataProvider#two (repetition 3 of 3))
Test Finished (PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest::testWithDataProvider#two (repetition 3 of 3))
Test Suite for Repeated Test Method Finished (PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest::testWithDataProvider#two, 3 repetitions)
Test Suite for Test Method with Data Provider Finished (PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest::testWithDataProvider, 6 data sets)
Test Suite Finished (PHPUnit\TestFixture\Repeat\RepeatAttributeWithDataProviderTest, 6 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
