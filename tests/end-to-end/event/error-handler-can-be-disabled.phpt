--TEST--
The right events are emitted in the right order when PHPUnit's error handler is disabled
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--fail-on-notice';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/error-handler-can-be-disabled';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Bootstrap Finished (%s%esrc/Foo.php)
Event Facade Sealed
Test Suite Loaded (4 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (4 tests)
Test Suite Started (%s%ephpunit.xml, 4 tests)
Test Suite Started (default, 4 tests)
Test Suite Started (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest, 4 tests)
Test Preparation Started (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testMethodA)
Test Prepared (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testMethodA)
Test Passed (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testMethodA)
Test Finished (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testMethodA)
Test Preparation Started (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testMethodB)
Test Prepared (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testMethodB)
Test Passed (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testMethodB)
Test Finished (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testMethodB)
Test Preparation Started (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testErrorHandlerSet)
Test Prepared (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testErrorHandlerSet)
Test Passed (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testErrorHandlerSet)
Test Finished (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testErrorHandlerSet)
Test Preparation Started (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testErrorHandlerIsNotSet)
Test Prepared (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testErrorHandlerIsNotSet)
Test Passed (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testErrorHandlerIsNotSet)
Test Finished (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testErrorHandlerIsNotSet)
Test Suite Finished (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest, 4 tests)
Test Suite Finished (default, 4 tests)
Test Suite Finished (%s%ephpunit.xml, 4 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
