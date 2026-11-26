--TEST--
The --enforce-time-limit CLI option enforces the time limit configured for the size of a test, but no time limit is enforced for a test of unknown size when no default time limit is configured
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) echo 'skip: Extension pcntl is required';
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--enforce-time-limit';
$_SERVER['argv'][] = '--default-time-limit';
$_SERVER['argv'][] = '0';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/time-limit';

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
Test Suite Started (CLI Arguments, 3 tests)
Test Suite Started (PHPUnit\TestFixture\TimeLimit\TestWithLargeSizeTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\TimeLimit\TestWithLargeSizeTest::testOne)
Test Prepared (PHPUnit\TestFixture\TimeLimit\TestWithLargeSizeTest::testOne)
Test Passed (PHPUnit\TestFixture\TimeLimit\TestWithLargeSizeTest::testOne)
Test Finished (PHPUnit\TestFixture\TimeLimit\TestWithLargeSizeTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\TimeLimit\TestWithLargeSizeTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\TimeLimit\TestWithMediumSizeTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\TimeLimit\TestWithMediumSizeTest::testOne)
Test Prepared (PHPUnit\TestFixture\TimeLimit\TestWithMediumSizeTest::testOne)
Test Passed (PHPUnit\TestFixture\TimeLimit\TestWithMediumSizeTest::testOne)
Test Finished (PHPUnit\TestFixture\TimeLimit\TestWithMediumSizeTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\TimeLimit\TestWithMediumSizeTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\TimeLimit\TestWithoutSizeTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\TimeLimit\TestWithoutSizeTest::testOne)
Test Prepared (PHPUnit\TestFixture\TimeLimit\TestWithoutSizeTest::testOne)
Test Passed (PHPUnit\TestFixture\TimeLimit\TestWithoutSizeTest::testOne)
Test Finished (PHPUnit\TestFixture\TimeLimit\TestWithoutSizeTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\TimeLimit\TestWithoutSizeTest, 1 test)
Test Suite Finished (CLI Arguments, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
