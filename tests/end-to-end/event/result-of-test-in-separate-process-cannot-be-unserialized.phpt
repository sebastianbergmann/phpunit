--TEST--
The right events are emitted in the right order for a test run in a separate process whose result cannot be unserialized
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/ResultOfTestInSeparateProcessCannotBeUnserializedTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Event\ResultOfTestInSeparateProcessCannotBeUnserializedTest, 1 test)
Child Process Started
Child Process Errored
Test Errored (PHPUnit\TestFixture\Event\ResultOfTestInSeparateProcessCannotBeUnserializedTest::testOne)
Test was run in child process and its result could not be unserialized: Cannot assign __PHP_Incomplete_Class to property PHPUnit\TestFixture\Event\ResultOfTestInSeparateProcessCannotBeUnserializedValue::$dependency of type PHPUnit\TestFixture\Event\ResultOfTestInSeparateProcessCannotBeUnserializedDependency
Test Finished (PHPUnit\TestFixture\Event\ResultOfTestInSeparateProcessCannotBeUnserializedTest::testOne)
Child Process Finished
Test Suite Finished (PHPUnit\TestFixture\Event\ResultOfTestInSeparateProcessCannotBeUnserializedTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
