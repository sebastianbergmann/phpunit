--TEST--
The right events are emitted in the right order for a test that uses a TestWithJson attribute which provides an invalid value
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/../../_files/Metadata/Attribute/tests/TestWithInvalidValueTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Triggered PHPUnit Error (PHPUnit\TestFixture\Metadata\Attribute\TestWithInvalidValueTest::testOne)
The data provider specified for PHPUnit\TestFixture\Metadata\Attribute\TestWithInvalidValueTest::testOne is invalid
Data set #0 is invalid, expected array but got bool
Test Runner Triggered Warning (No tests found in class "PHPUnit\TestFixture\Metadata\Attribute\TestWithInvalidValueTest".)
Test Suite Loaded (0 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (0 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
