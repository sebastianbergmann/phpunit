--TEST--
--recycle-workers-after with a value that is not a non-negative integer triggers a warning and is ignored
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--recycle-workers-after';
$_SERVER['argv'][] = '-1';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/PlainTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Triggered PHPUnit Warning (Option "--recycle-workers-after -1" ignored because "-1" is not a non-negative integer)
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
