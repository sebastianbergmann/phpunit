--TEST--
--repeat with data provider repeats each data set independently
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/DataProviderTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Data Provider Method Called (PHPUnit\TestFixture\Repeat\DataProviderTest::provider for test method PHPUnit\TestFixture\Repeat\DataProviderTest::testWithDataProvider)
Data Provider Method Finished for PHPUnit\TestFixture\Repeat\DataProviderTest::testWithDataProvider:
- PHPUnit\TestFixture\Repeat\DataProviderTest::provider
Test Suite Loaded (4 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (4 tests)
Test Suite Started (PHPUnit\TestFixture\Repeat\DataProviderTest, 4 tests)
Test Suite Started (PHPUnit\TestFixture\Repeat\DataProviderTest::testWithDataProvider, 4 tests)
Test Preparation Started (PHPUnit\TestFixture\Repeat\DataProviderTest::testWithDataProvider#one (repetition 1 of 2))
Test Prepared (PHPUnit\TestFixture\Repeat\DataProviderTest::testWithDataProvider#one (repetition 1 of 2))
Test Passed (PHPUnit\TestFixture\Repeat\DataProviderTest::testWithDataProvider#one (repetition 1 of 2))
Test Finished (PHPUnit\TestFixture\Repeat\DataProviderTest::testWithDataProvider#one (repetition 1 of 2))
Test Preparation Started (PHPUnit\TestFixture\Repeat\DataProviderTest::testWithDataProvider#one (repetition 2 of 2))
Test Prepared (PHPUnit\TestFixture\Repeat\DataProviderTest::testWithDataProvider#one (repetition 2 of 2))
Test Passed (PHPUnit\TestFixture\Repeat\DataProviderTest::testWithDataProvider#one (repetition 2 of 2))
Test Finished (PHPUnit\TestFixture\Repeat\DataProviderTest::testWithDataProvider#one (repetition 2 of 2))
Test Preparation Started (PHPUnit\TestFixture\Repeat\DataProviderTest::testWithDataProvider#two (repetition 1 of 2))
Test Prepared (PHPUnit\TestFixture\Repeat\DataProviderTest::testWithDataProvider#two (repetition 1 of 2))
Test Passed (PHPUnit\TestFixture\Repeat\DataProviderTest::testWithDataProvider#two (repetition 1 of 2))
Test Finished (PHPUnit\TestFixture\Repeat\DataProviderTest::testWithDataProvider#two (repetition 1 of 2))
Test Preparation Started (PHPUnit\TestFixture\Repeat\DataProviderTest::testWithDataProvider#two (repetition 2 of 2))
Test Prepared (PHPUnit\TestFixture\Repeat\DataProviderTest::testWithDataProvider#two (repetition 2 of 2))
Test Passed (PHPUnit\TestFixture\Repeat\DataProviderTest::testWithDataProvider#two (repetition 2 of 2))
Test Finished (PHPUnit\TestFixture\Repeat\DataProviderTest::testWithDataProvider#two (repetition 2 of 2))
Test Suite Finished (PHPUnit\TestFixture\Repeat\DataProviderTest::testWithDataProvider, 4 tests)
Test Suite Finished (PHPUnit\TestFixture\Repeat\DataProviderTest, 4 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
