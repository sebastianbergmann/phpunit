--TEST--
Stopping test execution after second risky test works
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--stop-on-risky=2';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/MultipleRiskyTest.php';

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
Test Suite Started (PHPUnit\TestFixture\TestRunnerStopping\MultipleRiskyTest, 4 tests)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\MultipleRiskyTest::testOne)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\MultipleRiskyTest::testOne)
Test Passed (PHPUnit\TestFixture\TestRunnerStopping\MultipleRiskyTest::testOne)
Test Considered Risky (PHPUnit\TestFixture\TestRunnerStopping\MultipleRiskyTest::testOne)
This test did not perform any assertions
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\MultipleRiskyTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\MultipleRiskyTest::testTwo)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\MultipleRiskyTest::testTwo)
Test Passed (PHPUnit\TestFixture\TestRunnerStopping\MultipleRiskyTest::testTwo)
Test Considered Risky (PHPUnit\TestFixture\TestRunnerStopping\MultipleRiskyTest::testTwo)
This test did not perform any assertions
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\MultipleRiskyTest::testTwo)
Test Runner Execution Aborted
Test Suite Finished (PHPUnit\TestFixture\TestRunnerStopping\MultipleRiskyTest, 4 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
