--TEST--
Stopping test execution after second incomplete test works
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--stop-on-incomplete=2';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/MultipleIncompleteTest.php';

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
Test Suite Started (PHPUnit\TestFixture\TestRunnerStopping\MultipleIncompleteTest, 4 tests)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\MultipleIncompleteTest::testOne)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\MultipleIncompleteTest::testOne)
Test Marked Incomplete (PHPUnit\TestFixture\TestRunnerStopping\MultipleIncompleteTest::testOne)
message
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\MultipleIncompleteTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\MultipleIncompleteTest::testTwo)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\MultipleIncompleteTest::testTwo)
Test Marked Incomplete (PHPUnit\TestFixture\TestRunnerStopping\MultipleIncompleteTest::testTwo)
message
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\MultipleIncompleteTest::testTwo)
Test Runner Execution Aborted
Test Suite Finished (PHPUnit\TestFixture\TestRunnerStopping\MultipleIncompleteTest, 4 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
