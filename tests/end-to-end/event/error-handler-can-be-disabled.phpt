--TEST--
The right events are emitted in the right order when PHPUnit's error handler is disabled
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = '--fail-on-notice';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/error-handler-can-be-disabled';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Bootstrap Finished (%s%esrc/Foo.php)
Test Suite Loaded (4 tests)
Event Facade Sealed
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (4 tests)
Test Suite Started (%s%ephpunit.xml, 4 tests)
Test Suite Started (default, 4 tests)
Test Suite Started (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest, 4 tests)
Test Preparation Started (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testMethodA)
Test Prepared (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testMethodA)
Assertion Succeeded (Constraint: exception of type "Exception", Value: {enable export of objects to see this value})
Assertion Succeeded (Constraint: exception message contains 'Failed to open stream', Value: 'fopen(%s/missing/directory): Failed to open stream: No such file or directory')
Test Passed (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testMethodA)
Test Finished (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testMethodA)
Test Preparation Started (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testMethodB)
Test Prepared (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testMethodB)
Assertion Succeeded (Constraint: is identical to 'Triggering', Value: 'Triggering')
Test Passed (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testMethodB)
Test Finished (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testMethodB)
Test Preparation Started (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testErrorHandlerSet)
Test Prepared (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testErrorHandlerSet)
Assertion Succeeded (Constraint: is of type callable, Value: {enable export of objects to see this value})
Test Passed (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testErrorHandlerSet)
Test Finished (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testErrorHandlerSet)
Test Preparation Started (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testErrorHandlerIsNotSet)
Test Prepared (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testErrorHandlerIsNotSet)
Assertion Succeeded (Constraint: is null, Value: null)
Test Passed (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testErrorHandlerIsNotSet)
Test Finished (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testErrorHandlerIsNotSet)
Test Suite Finished (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest, 4 tests)
Test Suite Finished (default, 4 tests)
Test Suite Finished (%s%ephpunit.xml, 4 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
