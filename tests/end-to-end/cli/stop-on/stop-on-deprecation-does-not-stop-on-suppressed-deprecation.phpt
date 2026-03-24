--TEST--
--stop-on-deprecation does not stop on suppressed deprecation
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--stop-on-deprecation';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/SuppressedDeprecationTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (4 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (4 tests)
Test Suite Started (PHPUnit\TestFixture\TestRunnerStopping\SuppressedDeprecationTest, 4 tests)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\SuppressedDeprecationTest::testOne)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\SuppressedDeprecationTest::testOne)
Test Triggered Deprecation (PHPUnit\TestFixture\TestRunnerStopping\SuppressedDeprecationTest::testOne, unknown if issue was triggered in first-party code or third-party code, suppressed using operator) in %s:%d
suppressed message
Test Passed (PHPUnit\TestFixture\TestRunnerStopping\SuppressedDeprecationTest::testOne)
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\SuppressedDeprecationTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\SuppressedDeprecationTest::testTwo)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\SuppressedDeprecationTest::testTwo)
Test Passed (PHPUnit\TestFixture\TestRunnerStopping\SuppressedDeprecationTest::testTwo)
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\SuppressedDeprecationTest::testTwo)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\SuppressedDeprecationTest::testThree)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\SuppressedDeprecationTest::testThree)
Test Triggered Deprecation (PHPUnit\TestFixture\TestRunnerStopping\SuppressedDeprecationTest::testThree, unknown if issue was triggered in first-party code or third-party code) in %s:%d
non-suppressed message
Test Passed (PHPUnit\TestFixture\TestRunnerStopping\SuppressedDeprecationTest::testThree)
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\SuppressedDeprecationTest::testThree)
Test Runner Execution Aborted
Test Suite Finished (PHPUnit\TestFixture\TestRunnerStopping\SuppressedDeprecationTest, 4 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
