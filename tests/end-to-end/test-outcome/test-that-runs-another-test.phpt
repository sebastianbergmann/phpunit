--TEST--
A test that runs another test errors
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/TestThatRunsAnotherTest.php';

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
Test Suite Started (PHPUnit\TestFixture\NestedTestRun\TestThatRunsAnotherTest, 4 tests)
Test Preparation Started (PHPUnit\TestFixture\NestedTestRun\TestThatRunsAnotherTest::testRunsAnotherTest)
Test Prepared (PHPUnit\TestFixture\NestedTestRun\TestThatRunsAnotherTest::testRunsAnotherTest)
Test Errored (PHPUnit\TestFixture\NestedTestRun\TestThatRunsAnotherTest::testRunsAnotherTest)
A test must not run another test
Test Finished (PHPUnit\TestFixture\NestedTestRun\TestThatRunsAnotherTest::testRunsAnotherTest)
Test Preparation Started (PHPUnit\TestFixture\NestedTestRun\TestThatRunsAnotherTest::testRunsAnotherTestInSeparateProcess)
Child Process Started (test requiring process isolation)
Child Process Finished (test requiring process isolation)
Test Prepared (PHPUnit\TestFixture\NestedTestRun\TestThatRunsAnotherTest::testRunsAnotherTestInSeparateProcess)
Test Errored (PHPUnit\TestFixture\NestedTestRun\TestThatRunsAnotherTest::testRunsAnotherTestInSeparateProcess)
A test must not run another test
Test Finished (PHPUnit\TestFixture\NestedTestRun\TestThatRunsAnotherTest::testRunsAnotherTestInSeparateProcess)
Test Preparation Started (PHPUnit\TestFixture\NestedTestRun\TestThatRunsAnotherTest::testRunsPhptTest)
Test Prepared (PHPUnit\TestFixture\NestedTestRun\TestThatRunsAnotherTest::testRunsPhptTest)
Test Errored (PHPUnit\TestFixture\NestedTestRun\TestThatRunsAnotherTest::testRunsPhptTest)
A test must not run another test
Test Finished (PHPUnit\TestFixture\NestedTestRun\TestThatRunsAnotherTest::testRunsPhptTest)
Test Preparation Started (PHPUnit\TestFixture\NestedTestRun\TestThatRunsAnotherTest::testOne)
Test Prepared (PHPUnit\TestFixture\NestedTestRun\TestThatRunsAnotherTest::testOne)
Test Passed (PHPUnit\TestFixture\NestedTestRun\TestThatRunsAnotherTest::testOne)
Test Finished (PHPUnit\TestFixture\NestedTestRun\TestThatRunsAnotherTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\NestedTestRun\TestThatRunsAnotherTest, 4 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
