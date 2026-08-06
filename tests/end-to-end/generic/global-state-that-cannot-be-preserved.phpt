--TEST--
A warning is triggered for each global variable that cannot be preserved for a test that runs in a separate process
--FILE--
<?php declare(strict_types=1);
$GLOBALS['globalClosure'] = static function (): void {};

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/GlobalStateThatCannotBePreservedTest.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\GlobalState\GlobalStateThatCannotBePreservedTest, 1 test)
Test Triggered PHPUnit Warning (PHPUnit\TestFixture\GlobalState\GlobalStateThatCannotBePreservedTest::testOne)
Global variable $GLOBALS['globalClosure'] was not preserved because it is a Closure
Child Process Started (test requiring process isolation)
Test Preparation Started (PHPUnit\TestFixture\GlobalState\GlobalStateThatCannotBePreservedTest::testOne)
Test Prepared (PHPUnit\TestFixture\GlobalState\GlobalStateThatCannotBePreservedTest::testOne)
Test Passed (PHPUnit\TestFixture\GlobalState\GlobalStateThatCannotBePreservedTest::testOne)
Test Finished (PHPUnit\TestFixture\GlobalState\GlobalStateThatCannotBePreservedTest::testOne)
Child Process Finished (test requiring process isolation)
Test Suite Finished (PHPUnit\TestFixture\GlobalState\GlobalStateThatCannotBePreservedTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
