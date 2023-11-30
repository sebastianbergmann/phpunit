--TEST--
Stopping test execution after first risky test works
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = '--stop-on-risky';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/RiskyTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Test Suite Loaded (2 tests)
Event Facade Sealed
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\TestRunnerStopping\RiskyTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\RiskyTest::testOne)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\RiskyTest::testOne)
Test Passed (PHPUnit\TestFixture\TestRunnerStopping\RiskyTest::testOne)
Test Considered Risky (PHPUnit\TestFixture\TestRunnerStopping\RiskyTest::testOne)
This test did not perform any assertions
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\RiskyTest::testOne)
Test Runner Execution Aborted
Test Suite Finished (PHPUnit\TestFixture\TestRunnerStopping\RiskyTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
