--TEST--
#[Repeat] in combination with #[RunInSeparateProcess] stops remaining repetitions after the child process crashed
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/RepeatInSeparateProcessCrashTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Repeat\RepeatInSeparateProcessCrashTest, 3 tests)
Test Suite for Repeated Test Method Started (PHPUnit\TestFixture\Repeat\RepeatInSeparateProcessCrashTest::testOne, 3 repetitions)
Child Process Started
Child Process Errored
Test Errored (PHPUnit\TestFixture\Repeat\RepeatInSeparateProcessCrashTest::testOne (repetition 1 of 3))
Test was run in child process and ended unexpectedly
Test Finished (PHPUnit\TestFixture\Repeat\RepeatInSeparateProcessCrashTest::testOne (repetition 1 of 3))
Child Process Finished
Test Skipped (PHPUnit\TestFixture\Repeat\RepeatInSeparateProcessCrashTest::testOne (repetition 2 of 3))
Remaining repetition skipped after failure in repetition 1
Test Skipped (PHPUnit\TestFixture\Repeat\RepeatInSeparateProcessCrashTest::testOne (repetition 3 of 3))
Remaining repetition skipped after failure in repetition 1
Test Suite for Repeated Test Method Finished (PHPUnit\TestFixture\Repeat\RepeatInSeparateProcessCrashTest::testOne, 3 repetitions)
Test Suite Finished (PHPUnit\TestFixture\Repeat\RepeatInSeparateProcessCrashTest, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
