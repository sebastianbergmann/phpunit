--TEST--
Test that depends on a retried test with data provider runs when all data sets pass, including one that passed on a retry
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/DependsOnRetriedDataProviderSuccessTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Data Provider Method Called (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderSuccessTest::provider for test method PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderSuccessTest::testWithDataProvider)
Data Provider Method Finished for PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderSuccessTest::testWithDataProvider:
- PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderSuccessTest::provider
Test Suite Loaded (3 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (3 tests)
Test Suite Started (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderSuccessTest, 3 tests)
Test Suite for Test Method with Data Provider Started (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderSuccessTest::testWithDataProvider, 2 data sets)
Test Suite for Retried Test Method Started (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderSuccessTest::testWithDataProvider#stable, up to 2 attempts)
Test Preparation Started (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderSuccessTest::testWithDataProvider#stable)
Test Prepared (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderSuccessTest::testWithDataProvider#stable)
Test Passed (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderSuccessTest::testWithDataProvider#stable)
Test Finished (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderSuccessTest::testWithDataProvider#stable)
Test Suite for Retried Test Method Finished (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderSuccessTest::testWithDataProvider#stable, up to 2 attempts)
Test Suite for Retried Test Method Started (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderSuccessTest::testWithDataProvider#flaky, up to 2 attempts)
Test Attempt Failed (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderSuccessTest::testWithDataProvider#flaky)
Failure on first attempt for flaky data set
Test Preparation Started (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderSuccessTest::testWithDataProvider#flaky (attempt 2 of 2))
Test Prepared (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderSuccessTest::testWithDataProvider#flaky (attempt 2 of 2))
Test Passed (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderSuccessTest::testWithDataProvider#flaky (attempt 2 of 2))
Test Finished (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderSuccessTest::testWithDataProvider#flaky (attempt 2 of 2))
Test Suite for Retried Test Method Finished (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderSuccessTest::testWithDataProvider#flaky, up to 2 attempts)
Test Suite for Test Method with Data Provider Finished (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderSuccessTest::testWithDataProvider, 2 data sets)
Test Preparation Started (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderSuccessTest::testDependent)
Test Prepared (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderSuccessTest::testDependent)
Test Passed (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderSuccessTest::testDependent)
Test Finished (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderSuccessTest::testDependent)
Test Suite Finished (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderSuccessTest, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
