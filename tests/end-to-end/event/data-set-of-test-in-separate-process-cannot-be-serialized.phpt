--TEST--
The right events are emitted in the right order for a test that is to be run in a separate process and whose data set cannot be serialized
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/DataSetOfTestInSeparateProcessCannotBeSerializedTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Data Provider Method Called (PHPUnit\TestFixture\Event\DataSetOfTestInSeparateProcessCannotBeSerializedTest::provider for test method PHPUnit\TestFixture\Event\DataSetOfTestInSeparateProcessCannotBeSerializedTest::testOne)
Data Provider Method Finished for PHPUnit\TestFixture\Event\DataSetOfTestInSeparateProcessCannotBeSerializedTest::testOne:
- PHPUnit\TestFixture\Event\DataSetOfTestInSeparateProcessCannotBeSerializedTest::provider
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\Event\DataSetOfTestInSeparateProcessCannotBeSerializedTest, 2 tests)
Test Suite Started (PHPUnit\TestFixture\Event\DataSetOfTestInSeparateProcessCannotBeSerializedTest::testOne, 1 test)
Test Errored (PHPUnit\TestFixture\Event\DataSetOfTestInSeparateProcessCannotBeSerializedTest::testOne#0)
Test could not be run in child process because its data set could not be serialized: Serialization of 'Closure' is not allowed
Test Suite Finished (PHPUnit\TestFixture\Event\DataSetOfTestInSeparateProcessCannotBeSerializedTest::testOne, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\DataSetOfTestInSeparateProcessCannotBeSerializedTest::testTwo)
Test Prepared (PHPUnit\TestFixture\Event\DataSetOfTestInSeparateProcessCannotBeSerializedTest::testTwo)
Test Passed (PHPUnit\TestFixture\Event\DataSetOfTestInSeparateProcessCannotBeSerializedTest::testTwo)
Test Finished (PHPUnit\TestFixture\Event\DataSetOfTestInSeparateProcessCannotBeSerializedTest::testTwo)
Test Suite Finished (PHPUnit\TestFixture\Event\DataSetOfTestInSeparateProcessCannotBeSerializedTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
