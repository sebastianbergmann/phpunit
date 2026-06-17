--TEST--
--repeat with data provider runs dependent test when all data sets pass
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/DependsOnDataProviderSuccessTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Data Provider Method Called (PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest::provider for test method PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest::testWithDataProvider)
Data Provider Method Finished for PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest::testWithDataProvider:
- PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest::provider
Test Suite Loaded (5 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (5 tests)
Test Suite Started (PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest, 5 tests)
Test Suite for Test Method with Data Provider Started (PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest::testWithDataProvider, 4 data sets)
Test Suite for Repeated Test Method Started (PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest::testWithDataProvider#one, 2 repetitions)
Test Preparation Started (PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest::testWithDataProvider#one (repetition 1 of 2))
Test Prepared (PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest::testWithDataProvider#one (repetition 1 of 2))
Test Passed (PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest::testWithDataProvider#one (repetition 1 of 2))
Test Finished (PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest::testWithDataProvider#one (repetition 1 of 2))
Test Preparation Started (PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest::testWithDataProvider#one (repetition 2 of 2))
Test Prepared (PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest::testWithDataProvider#one (repetition 2 of 2))
Test Passed (PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest::testWithDataProvider#one (repetition 2 of 2))
Test Finished (PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest::testWithDataProvider#one (repetition 2 of 2))
Test Suite for Repeated Test Method Finished (PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest::testWithDataProvider#one, 2 repetitions)
Test Suite for Repeated Test Method Started (PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest::testWithDataProvider#two, 2 repetitions)
Test Preparation Started (PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest::testWithDataProvider#two (repetition 1 of 2))
Test Prepared (PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest::testWithDataProvider#two (repetition 1 of 2))
Test Passed (PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest::testWithDataProvider#two (repetition 1 of 2))
Test Finished (PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest::testWithDataProvider#two (repetition 1 of 2))
Test Preparation Started (PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest::testWithDataProvider#two (repetition 2 of 2))
Test Prepared (PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest::testWithDataProvider#two (repetition 2 of 2))
Test Passed (PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest::testWithDataProvider#two (repetition 2 of 2))
Test Finished (PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest::testWithDataProvider#two (repetition 2 of 2))
Test Suite for Repeated Test Method Finished (PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest::testWithDataProvider#two, 2 repetitions)
Test Suite for Test Method with Data Provider Finished (PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest::testWithDataProvider, 4 data sets)
Test Preparation Started (PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest::testDependent)
Test Prepared (PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest::testDependent)
Test Passed (PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest::testDependent)
Test Finished (PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest::testDependent)
Test Suite Finished (PHPUnit\TestFixture\Repeat\DependsOnDataProviderSuccessTest, 5 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
