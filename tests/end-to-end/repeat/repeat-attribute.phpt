--TEST--
#[Repeat] attribute repeats individual test method
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/RepeatAttributeTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (4 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (4 tests)
Test Suite Started (PHPUnit\TestFixture\Repeat\RepeatAttributeTest, 4 tests)
Test Preparation Started (PHPUnit\TestFixture\Repeat\RepeatAttributeTest::testRepeatedThreeTimes (repetition 1 of 3))
Test Prepared (PHPUnit\TestFixture\Repeat\RepeatAttributeTest::testRepeatedThreeTimes (repetition 1 of 3))
Test Passed (PHPUnit\TestFixture\Repeat\RepeatAttributeTest::testRepeatedThreeTimes (repetition 1 of 3))
Test Finished (PHPUnit\TestFixture\Repeat\RepeatAttributeTest::testRepeatedThreeTimes (repetition 1 of 3))
Test Preparation Started (PHPUnit\TestFixture\Repeat\RepeatAttributeTest::testRepeatedThreeTimes (repetition 2 of 3))
Test Prepared (PHPUnit\TestFixture\Repeat\RepeatAttributeTest::testRepeatedThreeTimes (repetition 2 of 3))
Test Passed (PHPUnit\TestFixture\Repeat\RepeatAttributeTest::testRepeatedThreeTimes (repetition 2 of 3))
Test Finished (PHPUnit\TestFixture\Repeat\RepeatAttributeTest::testRepeatedThreeTimes (repetition 2 of 3))
Test Preparation Started (PHPUnit\TestFixture\Repeat\RepeatAttributeTest::testRepeatedThreeTimes (repetition 3 of 3))
Test Prepared (PHPUnit\TestFixture\Repeat\RepeatAttributeTest::testRepeatedThreeTimes (repetition 3 of 3))
Test Passed (PHPUnit\TestFixture\Repeat\RepeatAttributeTest::testRepeatedThreeTimes (repetition 3 of 3))
Test Finished (PHPUnit\TestFixture\Repeat\RepeatAttributeTest::testRepeatedThreeTimes (repetition 3 of 3))
Test Preparation Started (PHPUnit\TestFixture\Repeat\RepeatAttributeTest::testNotRepeated)
Test Prepared (PHPUnit\TestFixture\Repeat\RepeatAttributeTest::testNotRepeated)
Test Passed (PHPUnit\TestFixture\Repeat\RepeatAttributeTest::testNotRepeated)
Test Finished (PHPUnit\TestFixture\Repeat\RepeatAttributeTest::testNotRepeated)
Test Suite Finished (PHPUnit\TestFixture\Repeat\RepeatAttributeTest, 4 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
