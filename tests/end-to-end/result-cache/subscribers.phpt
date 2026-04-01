--TEST--
Result cache subscribers for incomplete, risky, and skipped tests
--FILE--
<?php declare(strict_types=1);
$cacheDirectory = sys_get_temp_dir() . '/phpunit-result-cache-subscribers-test';

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
Test Suite Started (PHPUnit\TestFixture\ResultCache\SubscribersTest, 3 tests)
Test Preparation Started (PHPUnit\TestFixture\ResultCache\SubscribersTest::testIncomplete)
Test Prepared (PHPUnit\TestFixture\ResultCache\SubscribersTest::testIncomplete)
Test Marked Incomplete (PHPUnit\TestFixture\ResultCache\SubscribersTest::testIncomplete)
not yet implemented
Test Finished (PHPUnit\TestFixture\ResultCache\SubscribersTest::testIncomplete)
Test Preparation Started (PHPUnit\TestFixture\ResultCache\SubscribersTest::testSkipped)
Test Prepared (PHPUnit\TestFixture\ResultCache\SubscribersTest::testSkipped)
Test Skipped (PHPUnit\TestFixture\ResultCache\SubscribersTest::testSkipped)
not applicable
Test Finished (PHPUnit\TestFixture\ResultCache\SubscribersTest::testSkipped)
Test Preparation Started (PHPUnit\TestFixture\ResultCache\SubscribersTest::testRisky)
Test Prepared (PHPUnit\TestFixture\ResultCache\SubscribersTest::testRisky)
Test Passed (PHPUnit\TestFixture\ResultCache\SubscribersTest::testRisky)
Test Considered Risky (PHPUnit\TestFixture\ResultCache\SubscribersTest::testRisky)
This test did not perform any assertions
Test Finished (PHPUnit\TestFixture\ResultCache\SubscribersTest::testRisky)
Test Suite Finished (PHPUnit\TestFixture\ResultCache\SubscribersTest, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
