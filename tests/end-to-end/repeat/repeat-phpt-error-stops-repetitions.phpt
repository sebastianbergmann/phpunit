--TEST--
An errored repetition of a PHPT test skips the remaining repetitions
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '3';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = realpath(__DIR__ . '/_files/RepeatErroringPhpt.phpt');

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
Test Suite Started (%sRepeatErroringPhpt.phpt, 3 tests)
Test Suite for Repeated PHPT Started (%sRepeatErroringPhpt.phpt, 3 repetitions)
Test Preparation Started (%sRepeatErroringPhpt.phpt (repetition 1 of 3))
Test Prepared (%sRepeatErroringPhpt.phpt (repetition 1 of 3))
Test Errored (%sRepeatErroringPhpt.phpt (repetition 1 of 3))
Test Finished (%sRepeatErroringPhpt.phpt (repetition 1 of 3))
Test Skipped (%sRepeatErroringPhpt.phpt (repetition 2 of 3))
Remaining repetition skipped after failure in repetition 1
Test Skipped (%sRepeatErroringPhpt.phpt (repetition 3 of 3))
Remaining repetition skipped after failure in repetition 1
Test Suite for Repeated PHPT Finished (%sRepeatErroringPhpt.phpt, 3 repetitions)
Test Suite Finished (%sRepeatErroringPhpt.phpt, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
