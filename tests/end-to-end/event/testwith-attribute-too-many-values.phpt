--TEST--
The right events are emitted in the right order for a successful test that uses a TestWith attribute which provides more values than the test method accepts 
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/../../_files/Metadata/Attribute/tests/TestWithTooManyValuesTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Triggered PHPUnit Warning (PHPUnit\TestFixture\Metadata\Attribute\TestWithTooManyValuesTest::testOne)
Data set #0 provided by TestWith#0 attribute has more arguments (3) than the test method accepts (2)
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Metadata\Attribute\TestWithTooManyValuesTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\Metadata\Attribute\TestWithTooManyValuesTest::testOne, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Metadata\Attribute\TestWithTooManyValuesTest::testOne#0)
Test Prepared (PHPUnit\TestFixture\Metadata\Attribute\TestWithTooManyValuesTest::testOne#0)
Test Passed (PHPUnit\TestFixture\Metadata\Attribute\TestWithTooManyValuesTest::testOne#0)
Test Finished (PHPUnit\TestFixture\Metadata\Attribute\TestWithTooManyValuesTest::testOne#0)
Test Suite Finished (PHPUnit\TestFixture\Metadata\Attribute\TestWithTooManyValuesTest::testOne, 1 test)
Test Suite Finished (PHPUnit\TestFixture\Metadata\Attribute\TestWithTooManyValuesTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
