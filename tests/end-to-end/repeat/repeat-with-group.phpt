--TEST--
#[Repeat] does not break #[Group] metadata when filtering by group
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--group';
$_SERVER['argv'][] = 'bar';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/RepeatWithGroupTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (4 tests)
Test Runner Started
Test Suite Sorted
Test Suite Filtered (3 tests)
Test Runner Execution Started (3 tests)
Test Suite Started (PHPUnit\TestFixture\Repeat\RepeatWithGroupTest, 3 tests)
Test Suite for Repeated Test Method Started (PHPUnit\TestFixture\Repeat\RepeatWithGroupTest::testRepeatedThreeTimes, 3 repetitions)
Test Preparation Started (PHPUnit\TestFixture\Repeat\RepeatWithGroupTest::testRepeatedThreeTimes (repetition 1 of 3))
Test Prepared (PHPUnit\TestFixture\Repeat\RepeatWithGroupTest::testRepeatedThreeTimes (repetition 1 of 3))
Test Passed (PHPUnit\TestFixture\Repeat\RepeatWithGroupTest::testRepeatedThreeTimes (repetition 1 of 3))
Test Finished (PHPUnit\TestFixture\Repeat\RepeatWithGroupTest::testRepeatedThreeTimes (repetition 1 of 3))
Test Preparation Started (PHPUnit\TestFixture\Repeat\RepeatWithGroupTest::testRepeatedThreeTimes (repetition 2 of 3))
Test Prepared (PHPUnit\TestFixture\Repeat\RepeatWithGroupTest::testRepeatedThreeTimes (repetition 2 of 3))
Test Passed (PHPUnit\TestFixture\Repeat\RepeatWithGroupTest::testRepeatedThreeTimes (repetition 2 of 3))
Test Finished (PHPUnit\TestFixture\Repeat\RepeatWithGroupTest::testRepeatedThreeTimes (repetition 2 of 3))
Test Preparation Started (PHPUnit\TestFixture\Repeat\RepeatWithGroupTest::testRepeatedThreeTimes (repetition 3 of 3))
Test Prepared (PHPUnit\TestFixture\Repeat\RepeatWithGroupTest::testRepeatedThreeTimes (repetition 3 of 3))
Test Passed (PHPUnit\TestFixture\Repeat\RepeatWithGroupTest::testRepeatedThreeTimes (repetition 3 of 3))
Test Finished (PHPUnit\TestFixture\Repeat\RepeatWithGroupTest::testRepeatedThreeTimes (repetition 3 of 3))
Test Suite for Repeated Test Method Finished (PHPUnit\TestFixture\Repeat\RepeatWithGroupTest::testRepeatedThreeTimes, 3 repetitions)
Test Suite Finished (PHPUnit\TestFixture\Repeat\RepeatWithGroupTest, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
