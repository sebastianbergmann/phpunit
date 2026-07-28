--TEST--
Test run history subscribers for incomplete, risky, and skipped tests
--FILE--
<?php declare(strict_types=1);
$cacheDirectory = sys_get_temp_dir() . '/phpunit-test-run-history-subscribers-test';

@mkdir($cacheDirectory, 0777, true);

$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--cache-directory';
$_SERVER['argv'][] = $cacheDirectory;
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/SubscribersTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (3 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (3 tests)
Test Suite Started (PHPUnit\TestFixture\TestRunHistory\SubscribersTest, 3 tests)
Test Preparation Started (PHPUnit\TestFixture\TestRunHistory\SubscribersTest::testIncomplete)
Test Prepared (PHPUnit\TestFixture\TestRunHistory\SubscribersTest::testIncomplete)
Test Marked Incomplete (PHPUnit\TestFixture\TestRunHistory\SubscribersTest::testIncomplete)
not yet implemented
Test Finished (PHPUnit\TestFixture\TestRunHistory\SubscribersTest::testIncomplete)
Test Preparation Started (PHPUnit\TestFixture\TestRunHistory\SubscribersTest::testSkipped)
Test Prepared (PHPUnit\TestFixture\TestRunHistory\SubscribersTest::testSkipped)
Test Skipped (PHPUnit\TestFixture\TestRunHistory\SubscribersTest::testSkipped)
not applicable
Test Finished (PHPUnit\TestFixture\TestRunHistory\SubscribersTest::testSkipped)
Test Preparation Started (PHPUnit\TestFixture\TestRunHistory\SubscribersTest::testRisky)
Test Prepared (PHPUnit\TestFixture\TestRunHistory\SubscribersTest::testRisky)
Test Passed (PHPUnit\TestFixture\TestRunHistory\SubscribersTest::testRisky)
Test Considered Risky (PHPUnit\TestFixture\TestRunHistory\SubscribersTest::testRisky)
This test did not perform any assertions
Test Finished (PHPUnit\TestFixture\TestRunHistory\SubscribersTest::testRisky)
Test Suite Finished (PHPUnit\TestFixture\TestRunHistory\SubscribersTest, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
