--TEST--
Test that depends on a retried test with data provider is skipped when a data set exhausts all attempts
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/DependsOnRetriedDataProviderFailureTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Data Provider Method Called (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderFailureTest::provider for test method PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderFailureTest::testWithDataProvider)
Data Provider Method Finished for PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderFailureTest::testWithDataProvider:
- PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderFailureTest::provider
Test Suite Loaded (3 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (3 tests)
Test Suite Started (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderFailureTest, 3 tests)
Test Suite for Test Method with Data Provider Started (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderFailureTest::testWithDataProvider, 2 data sets)
Test Suite for Retried Test Method Started (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderFailureTest::testWithDataProvider#passing, up to 2 attempts)
Test Preparation Started (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderFailureTest::testWithDataProvider#passing)
Test Prepared (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderFailureTest::testWithDataProvider#passing)
Test Passed (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderFailureTest::testWithDataProvider#passing)
Test Finished (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderFailureTest::testWithDataProvider#passing)
Test Suite for Retried Test Method Finished (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderFailureTest::testWithDataProvider#passing, up to 2 attempts)
Test Suite for Retried Test Method Started (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderFailureTest::testWithDataProvider#failing, up to 2 attempts)
Test Attempt Failed (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderFailureTest::testWithDataProvider#failing)
Failed asserting that false is true.
Test Preparation Started (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderFailureTest::testWithDataProvider#failing (attempt 2 of 2))
Test Prepared (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderFailureTest::testWithDataProvider#failing (attempt 2 of 2))
Test Failed (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderFailureTest::testWithDataProvider#failing (attempt 2 of 2))
Failed asserting that false is true.
Test Finished (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderFailureTest::testWithDataProvider#failing (attempt 2 of 2))
Test Suite for Retried Test Method Finished (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderFailureTest::testWithDataProvider#failing, up to 2 attempts)
Test Suite for Test Method with Data Provider Finished (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderFailureTest::testWithDataProvider, 2 data sets)
Test Skipped (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderFailureTest::testDependent)
This test depends on "PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderFailureTest::testWithDataProvider" to pass
Test Suite Finished (PHPUnit\TestFixture\Retry\DependsOnRetriedDataProviderFailureTest, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
