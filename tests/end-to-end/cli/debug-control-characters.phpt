--TEST--
Control characters in user-supplied strings are made visible in the output of --debug
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/../_files/ControlCharactersTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Data Provider Method Called (PHPUnit\TestFixture\ControlCharactersTest::provider for test method PHPUnit\TestFixture\ControlCharactersTest::testDataSetName)
Data Provider Method Finished for PHPUnit\TestFixture\ControlCharactersTest::testDataSetName:
- PHPUnit\TestFixture\ControlCharactersTest::provider
Test Suite Loaded (4 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (4 tests)
Test Suite Started (PHPUnit\TestFixture\ControlCharactersTest, 4 tests)
Test Preparation Started (PHPUnit\TestFixture\ControlCharactersTest::testUnexpectedOutput)
Test Prepared (PHPUnit\TestFixture\ControlCharactersTest::testUnexpectedOutput)
Test Passed (PHPUnit\TestFixture\ControlCharactersTest::testUnexpectedOutput)
Test Printed Unexpected Output
\u{001B}[2J\u{001B}[Hunexpected output

Test Finished (PHPUnit\TestFixture\ControlCharactersTest::testUnexpectedOutput)
Test Preparation Started (PHPUnit\TestFixture\ControlCharactersTest::testFailureMessage)
Test Prepared (PHPUnit\TestFixture\ControlCharactersTest::testFailureMessage)
Test Failed (PHPUnit\TestFixture\ControlCharactersTest::testFailureMessage)
Failed\u{001B}[2K\u{000D}Everything is fine
Test Finished (PHPUnit\TestFixture\ControlCharactersTest::testFailureMessage)
Test Suite for Test Method with Data Provider Started (PHPUnit\TestFixture\ControlCharactersTest::testDataSetName, 1 data set)
Test Preparation Started (PHPUnit\TestFixture\ControlCharactersTest::testDataSetName#\u{001B}[31mdata set name\u{001B}[0m)
Test Prepared (PHPUnit\TestFixture\ControlCharactersTest::testDataSetName#\u{001B}[31mdata set name\u{001B}[0m)
Test Failed (PHPUnit\TestFixture\ControlCharactersTest::testDataSetName#\u{001B}[31mdata set name\u{001B}[0m)
Failed asserting that true is false.
Test Finished (PHPUnit\TestFixture\ControlCharactersTest::testDataSetName#\u{001B}[31mdata set name\u{001B}[0m)
Test Suite for Test Method with Data Provider Finished (PHPUnit\TestFixture\ControlCharactersTest::testDataSetName, 1 data set)
Test Preparation Started (PHPUnit\TestFixture\ControlCharactersTest::testTestDoxLabel)
Test Prepared (PHPUnit\TestFixture\ControlCharactersTest::testTestDoxLabel)
Test Skipped (PHPUnit\TestFixture\ControlCharactersTest::testTestDoxLabel)
Skipped\u{001B}[2K\u{000D}Nothing to see here
Test Finished (PHPUnit\TestFixture\ControlCharactersTest::testTestDoxLabel)
Test Suite Finished (PHPUnit\TestFixture\ControlCharactersTest, 4 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
