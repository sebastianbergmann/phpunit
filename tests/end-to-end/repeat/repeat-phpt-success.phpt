--TEST--
The --repeat CLI option runs a PHPT test N times
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '3';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = realpath(__DIR__ . '/_files/RepeatPassingPhpt.phpt');

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
Test Suite Started (%s/RepeatPassingPhpt.phpt, 3 tests)
Test Suite for Repeated PHPT Started (%s/RepeatPassingPhpt.phpt, 3 repetitions)
Test Preparation Started (%s/RepeatPassingPhpt.phpt (repetition 1 of 3))
Test Prepared (%s/RepeatPassingPhpt.phpt (repetition 1 of 3))
Child Process Started
Child Process Finished
Test Passed (%s/RepeatPassingPhpt.phpt (repetition 1 of 3))
Test Finished (%s/RepeatPassingPhpt.phpt (repetition 1 of 3))
Test Preparation Started (%s/RepeatPassingPhpt.phpt (repetition 2 of 3))
Test Prepared (%s/RepeatPassingPhpt.phpt (repetition 2 of 3))
Child Process Started
Child Process Finished
Test Passed (%s/RepeatPassingPhpt.phpt (repetition 2 of 3))
Test Finished (%s/RepeatPassingPhpt.phpt (repetition 2 of 3))
Test Preparation Started (%s/RepeatPassingPhpt.phpt (repetition 3 of 3))
Test Prepared (%s/RepeatPassingPhpt.phpt (repetition 3 of 3))
Child Process Started
Child Process Finished
Test Passed (%s/RepeatPassingPhpt.phpt (repetition 3 of 3))
Test Finished (%s/RepeatPassingPhpt.phpt (repetition 3 of 3))
Test Suite for Repeated PHPT Finished (%s/RepeatPassingPhpt.phpt, 3 repetitions)
Test Suite Finished (%s/RepeatPassingPhpt.phpt, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
