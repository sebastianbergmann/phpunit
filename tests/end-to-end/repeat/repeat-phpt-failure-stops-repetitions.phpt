--TEST--
A failing repetition of a PHPT test skips the remaining repetitions
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '3';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = realpath(__DIR__ . '/_files/RepeatFailingPhpt.phpt');

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
Test Suite Started (%s/RepeatFailingPhpt.phpt, 3 tests)
Test Suite for Repeated PHPT Started (%s/RepeatFailingPhpt.phpt, 3 repetitions)
Test Preparation Started (%s/RepeatFailingPhpt.phpt (repetition 1 of 3))
Test Prepared (%s/RepeatFailingPhpt.phpt (repetition 1 of 3))
Child Process Started
Child Process Finished
Test Failed (%s/RepeatFailingPhpt.phpt (repetition 1 of 3))
Failed asserting that two strings are equal.
Test Finished (%s/RepeatFailingPhpt.phpt (repetition 1 of 3))
Test Skipped (%s/RepeatFailingPhpt.phpt (repetition 2 of 3))
Remaining repetition skipped after failure in repetition 1
Test Skipped (%s/RepeatFailingPhpt.phpt (repetition 3 of 3))
Remaining repetition skipped after failure in repetition 1
Test Suite for Repeated PHPT Finished (%s/RepeatFailingPhpt.phpt, 3 repetitions)
Test Suite Finished (%s/RepeatFailingPhpt.phpt, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
