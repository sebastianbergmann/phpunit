--TEST--
The right events are emitted in the right order for a test that is to be run in a separate process and that depends on a test whose return value cannot be serialized
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/DependencyInputOfTestInSeparateProcessCannotBeSerializedTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\DependencyInputOfTestInSeparateProcessCannotBeSerializedTest, 3 tests)
Test Preparation Started (PHPUnit\TestFixture\Event\DependencyInputOfTestInSeparateProcessCannotBeSerializedTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\DependencyInputOfTestInSeparateProcessCannotBeSerializedTest::testOne)
Test Passed (PHPUnit\TestFixture\Event\DependencyInputOfTestInSeparateProcessCannotBeSerializedTest::testOne)
Test Finished (PHPUnit\TestFixture\Event\DependencyInputOfTestInSeparateProcessCannotBeSerializedTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\Event\DependencyInputOfTestInSeparateProcessCannotBeSerializedTest::testTwo)
Test Errored (PHPUnit\TestFixture\Event\DependencyInputOfTestInSeparateProcessCannotBeSerializedTest::testTwo)
Test could not be run in child process because a value returned by a test it depends on could not be serialized: Serialization of 'Closure' is not allowed
Test Finished (PHPUnit\TestFixture\Event\DependencyInputOfTestInSeparateProcessCannotBeSerializedTest::testTwo)
Test Preparation Started (PHPUnit\TestFixture\Event\DependencyInputOfTestInSeparateProcessCannotBeSerializedTest::testThree)
Test Prepared (PHPUnit\TestFixture\Event\DependencyInputOfTestInSeparateProcessCannotBeSerializedTest::testThree)
Test Passed (PHPUnit\TestFixture\Event\DependencyInputOfTestInSeparateProcessCannotBeSerializedTest::testThree)
Test Finished (PHPUnit\TestFixture\Event\DependencyInputOfTestInSeparateProcessCannotBeSerializedTest::testThree)
Test Suite Finished (PHPUnit\TestFixture\Event\DependencyInputOfTestInSeparateProcessCannotBeSerializedTest, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
