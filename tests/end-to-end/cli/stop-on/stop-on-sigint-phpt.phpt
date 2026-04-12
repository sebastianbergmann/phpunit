--TEST--
Stopping test execution on SIGINT while PHPT test is running does not report the PHPT test as failed
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) echo 'skip: Extension pcntl is required';
if (!extension_loaded('posix')) echo 'skip: Extension posix is required';
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/InterruptPhptTest.phpt';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (%sInterruptPhptTest.phpt, 1 test)
Test Preparation Started (%sInterruptPhptTest.phpt)
Test Prepared (%sInterruptPhptTest.phpt)
Child Process Started
Child Process Finished
Test Finished (%sInterruptPhptTest.phpt)
Test Suite Finished (%sInterruptPhptTest.phpt, 1 test)
Test Runner Execution Finished
Test Runner Finished
Test execution was interrupted by a signal.
PHPUnit Finished (Shell Exit Code: 0)
