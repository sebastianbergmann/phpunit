--TEST--
--repeat with data provider skips dependent test when a later data set fails
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/DependsOnDataProviderFailureTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Data Provider Method Called (PHPUnit\TestFixture\Repeat\DependsOnDataProviderFailureTest::provider for test method PHPUnit\TestFixture\Repeat\DependsOnDataProviderFailureTest::testWithDataProvider)
Data Provider Method Finished for PHPUnit\TestFixture\Repeat\DependsOnDataProviderFailureTest::testWithDataProvider:
- PHPUnit\TestFixture\Repeat\DependsOnDataProviderFailureTest::provider
Test Suite Loaded (5 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (5 tests)
Test Suite Started (PHPUnit\TestFixture\Repeat\DependsOnDataProviderFailureTest, 5 tests)
Test Suite for Test Method with Data Provider Started (PHPUnit\TestFixture\Repeat\DependsOnDataProviderFailureTest::testWithDataProvider, 4 data sets)
Test Suite for Repeated Test Method Started (PHPUnit\TestFixture\Repeat\DependsOnDataProviderFailureTest::testWithDataProvider#passing, 2 repetitions)
Test Preparation Started (PHPUnit\TestFixture\Repeat\DependsOnDataProviderFailureTest::testWithDataProvider#passing (repetition 1 of 2))
Test Prepared (PHPUnit\TestFixture\Repeat\DependsOnDataProviderFailureTest::testWithDataProvider#passing (repetition 1 of 2))
Test Passed (PHPUnit\TestFixture\Repeat\DependsOnDataProviderFailureTest::testWithDataProvider#passing (repetition 1 of 2))
Test Finished (PHPUnit\TestFixture\Repeat\DependsOnDataProviderFailureTest::testWithDataProvider#passing (repetition 1 of 2))
Test Preparation Started (PHPUnit\TestFixture\Repeat\DependsOnDataProviderFailureTest::testWithDataProvider#passing (repetition 2 of 2))
Test Prepared (PHPUnit\TestFixture\Repeat\DependsOnDataProviderFailureTest::testWithDataProvider#passing (repetition 2 of 2))
Test Passed (PHPUnit\TestFixture\Repeat\DependsOnDataProviderFailureTest::testWithDataProvider#passing (repetition 2 of 2))
Test Finished (PHPUnit\TestFixture\Repeat\DependsOnDataProviderFailureTest::testWithDataProvider#passing (repetition 2 of 2))
Test Suite for Repeated Test Method Finished (PHPUnit\TestFixture\Repeat\DependsOnDataProviderFailureTest::testWithDataProvider#passing, 2 repetitions)
Test Suite for Repeated Test Method Started (PHPUnit\TestFixture\Repeat\DependsOnDataProviderFailureTest::testWithDataProvider#failing, 2 repetitions)
Test Preparation Started (PHPUnit\TestFixture\Repeat\DependsOnDataProviderFailureTest::testWithDataProvider#failing (repetition 1 of 2))
Test Prepared (PHPUnit\TestFixture\Repeat\DependsOnDataProviderFailureTest::testWithDataProvider#failing (repetition 1 of 2))
Test Failed (PHPUnit\TestFixture\Repeat\DependsOnDataProviderFailureTest::testWithDataProvider#failing (repetition 1 of 2))
Failed asserting that false is true.
Test Finished (PHPUnit\TestFixture\Repeat\DependsOnDataProviderFailureTest::testWithDataProvider#failing (repetition 1 of 2))
Test Skipped (PHPUnit\TestFixture\Repeat\DependsOnDataProviderFailureTest::testWithDataProvider#failing (repetition 2 of 2))
Remaining repetition skipped after failure in repetition 1
Test Suite for Repeated Test Method Finished (PHPUnit\TestFixture\Repeat\DependsOnDataProviderFailureTest::testWithDataProvider#failing, 2 repetitions)
Test Suite for Test Method with Data Provider Finished (PHPUnit\TestFixture\Repeat\DependsOnDataProviderFailureTest::testWithDataProvider, 4 data sets)
Test Skipped (PHPUnit\TestFixture\Repeat\DependsOnDataProviderFailureTest::testDependent)
This test depends on "PHPUnit\TestFixture\Repeat\DependsOnDataProviderFailureTest::testWithDataProvider" to pass
Test Suite Finished (PHPUnit\TestFixture\Repeat\DependsOnDataProviderFailureTest, 5 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
