--TEST--
#[Retry] retries each data set independently
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/RetryWithDataProviderTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Data Provider Method Called (PHPUnit\TestFixture\Retry\RetryWithDataProviderTest::provider for test method PHPUnit\TestFixture\Retry\RetryWithDataProviderTest::testWithDataProvider)
Data Provider Method Finished for PHPUnit\TestFixture\Retry\RetryWithDataProviderTest::testWithDataProvider:
- PHPUnit\TestFixture\Retry\RetryWithDataProviderTest::provider
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\Retry\RetryWithDataProviderTest, 2 tests)
Test Suite for Test Method with Data Provider Started (PHPUnit\TestFixture\Retry\RetryWithDataProviderTest::testWithDataProvider, 2 data sets)
Test Suite for Retried Test Method Started (PHPUnit\TestFixture\Retry\RetryWithDataProviderTest::testWithDataProvider#stable, up to 3 attempts)
Test Preparation Started (PHPUnit\TestFixture\Retry\RetryWithDataProviderTest::testWithDataProvider#stable)
Test Prepared (PHPUnit\TestFixture\Retry\RetryWithDataProviderTest::testWithDataProvider#stable)
Test Passed (PHPUnit\TestFixture\Retry\RetryWithDataProviderTest::testWithDataProvider#stable)
Test Finished (PHPUnit\TestFixture\Retry\RetryWithDataProviderTest::testWithDataProvider#stable)
Test Suite for Retried Test Method Finished (PHPUnit\TestFixture\Retry\RetryWithDataProviderTest::testWithDataProvider#stable, up to 3 attempts)
Test Suite for Retried Test Method Started (PHPUnit\TestFixture\Retry\RetryWithDataProviderTest::testWithDataProvider#flaky, up to 3 attempts)
Test Attempt Failed (PHPUnit\TestFixture\Retry\RetryWithDataProviderTest::testWithDataProvider#flaky)
Failure on first attempt for flaky data set
Test Preparation Started (PHPUnit\TestFixture\Retry\RetryWithDataProviderTest::testWithDataProvider#flaky (attempt 2 of 3))
Test Prepared (PHPUnit\TestFixture\Retry\RetryWithDataProviderTest::testWithDataProvider#flaky (attempt 2 of 3))
Test Passed (PHPUnit\TestFixture\Retry\RetryWithDataProviderTest::testWithDataProvider#flaky (attempt 2 of 3))
Test Finished (PHPUnit\TestFixture\Retry\RetryWithDataProviderTest::testWithDataProvider#flaky (attempt 2 of 3))
Test Suite for Retried Test Method Finished (PHPUnit\TestFixture\Retry\RetryWithDataProviderTest::testWithDataProvider#flaky, up to 3 attempts)
Test Suite for Test Method with Data Provider Finished (PHPUnit\TestFixture\Retry\RetryWithDataProviderTest::testWithDataProvider, 2 data sets)
Test Suite Finished (PHPUnit\TestFixture\Retry\RetryWithDataProviderTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
