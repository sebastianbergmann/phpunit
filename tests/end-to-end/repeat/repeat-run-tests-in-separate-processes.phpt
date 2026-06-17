--TEST--
#[Repeat] in combination with #[RunTestsInSeparateProcesses] reports each repetition with its repetition number
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/RepeatTestsInSeparateProcessesTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\Repeat\RepeatTestsInSeparateProcessesTest, 2 tests)
Test Suite for Repeated Test Method Started (PHPUnit\TestFixture\Repeat\RepeatTestsInSeparateProcessesTest::testOne, 2 repetitions)
Child Process Started
Test Preparation Started (PHPUnit\TestFixture\Repeat\RepeatTestsInSeparateProcessesTest::testOne (repetition 1 of 2))
Test Prepared (PHPUnit\TestFixture\Repeat\RepeatTestsInSeparateProcessesTest::testOne (repetition 1 of 2))
Test Passed (PHPUnit\TestFixture\Repeat\RepeatTestsInSeparateProcessesTest::testOne (repetition 1 of 2))
Test Finished (PHPUnit\TestFixture\Repeat\RepeatTestsInSeparateProcessesTest::testOne (repetition 1 of 2))
Child Process Finished
Child Process Started
Test Preparation Started (PHPUnit\TestFixture\Repeat\RepeatTestsInSeparateProcessesTest::testOne (repetition 2 of 2))
Test Prepared (PHPUnit\TestFixture\Repeat\RepeatTestsInSeparateProcessesTest::testOne (repetition 2 of 2))
Test Passed (PHPUnit\TestFixture\Repeat\RepeatTestsInSeparateProcessesTest::testOne (repetition 2 of 2))
Test Finished (PHPUnit\TestFixture\Repeat\RepeatTestsInSeparateProcessesTest::testOne (repetition 2 of 2))
Child Process Finished
Test Suite for Repeated Test Method Finished (PHPUnit\TestFixture\Repeat\RepeatTestsInSeparateProcessesTest::testOne, 2 repetitions)
Test Suite Finished (PHPUnit\TestFixture\Repeat\RepeatTestsInSeparateProcessesTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
