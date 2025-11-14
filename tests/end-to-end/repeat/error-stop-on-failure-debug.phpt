--TEST--
Repeat option
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--stop-on-failure';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'test2';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '3';
$_SERVER['argv'][] = __DIR__ . '/_files/RepeatWithFailuresTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using PHP %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (%d tests)
Test Runner Started
Test Suite Sorted
Test Suite Filtered (3 tests)
Test Runner Execution Started (3 tests)
Test Suite Started (RepeatWithFailuresTest, 3 tests)
Test Preparation Started (RepeatWithFailuresTest::test2)
Test Prepared (RepeatWithFailuresTest::test2)
Test Finished (RepeatWithFailuresTest::test2)
Test Preparation Started (RepeatWithFailuresTest::test2)
Test Prepared (RepeatWithFailuresTest::test2)
Test Failed (RepeatWithFailuresTest::test2)
Failed asserting that true is false.
Test Finished (RepeatWithFailuresTest::test2)
Test Skipped (RepeatWithFailuresTest::test2)
Test repetition #3 failure
Test Suite Finished (RepeatWithFailuresTest, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
