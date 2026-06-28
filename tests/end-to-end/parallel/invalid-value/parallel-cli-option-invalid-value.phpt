--TEST--
--parallel with a value that is not a positive integer triggers a warning and falls back to sequential execution
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--parallel';
$_SERVER['argv'][] = '0';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/PlainTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Triggered PHPUnit Warning (Option "--parallel 0" ignored because "0" is not a positive integer)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\ParallelInvalidValue\PlainTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ParallelInvalidValue\PlainTest::testOne)
Test Prepared (PHPUnit\TestFixture\ParallelInvalidValue\PlainTest::testOne)
Test Passed (PHPUnit\TestFixture\ParallelInvalidValue\PlainTest::testOne)
Test Finished (PHPUnit\TestFixture\ParallelInvalidValue\PlainTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\ParallelInvalidValue\PlainTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
