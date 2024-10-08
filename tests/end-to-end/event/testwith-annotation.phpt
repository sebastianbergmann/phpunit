--TEST--
The right events are emitted in the right order for a successful test that uses the TestWith and TestWithJson attributes
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/../../_files/Metadata/Annotation/tests/TestWithTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Triggered PHPUnit Error (PHPUnit\TestFixture\Metadata\Annotation\TestWithTest::testDataSetIsInvalidJson)
The data provider specified for PHPUnit\TestFixture\Metadata\Annotation\TestWithTest::testDataSetIsInvalidJson is invalid
The data set for the @testWith annotation cannot be parsed: State mismatch (invalid or malformed JSON)
Test Triggered PHPUnit Error (PHPUnit\TestFixture\Metadata\Annotation\TestWithTest::testDataSetCannotBeParsed)
The data provider specified for PHPUnit\TestFixture\Metadata\Annotation\TestWithTest::testDataSetCannotBeParsed is invalid
The data set for the @testWith annotation cannot be parsed.
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Metadata\Annotation\TestWithTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\Metadata\Annotation\TestWithTest::testDataSetIsValidJson, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Metadata\Annotation\TestWithTest::testDataSetIsValidJson#0)
Test Prepared (PHPUnit\TestFixture\Metadata\Annotation\TestWithTest::testDataSetIsValidJson#0)
Test Passed (PHPUnit\TestFixture\Metadata\Annotation\TestWithTest::testDataSetIsValidJson#0)
Test Finished (PHPUnit\TestFixture\Metadata\Annotation\TestWithTest::testDataSetIsValidJson#0)
Test Suite Finished (PHPUnit\TestFixture\Metadata\Annotation\TestWithTest::testDataSetIsValidJson, 1 test)
Test Suite Finished (PHPUnit\TestFixture\Metadata\Annotation\TestWithTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
