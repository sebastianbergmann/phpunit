--TEST--
The right events are emitted in the right order when PHPUnit's error handler is disabled
--SKIPIF--
<?php declare(strict_types=1);
if (DIRECTORY_SEPARATOR === '\\') {
    print "skip: this test does not work on Windows / GitHub Actions\n";
}
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
Bootstrap Finished (%s/src/Foo.php)
Test Suite Loaded (2 tests)
Event Facade Sealed
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (%s/phpunit.xml, 2 tests)
Test Suite Started (default, 2 tests)
Test Suite Started (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testMethodA)
Test Prepared (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testMethodA)
Assertion Succeeded (Constraint: exception of type "Exception", Value: Exception Object #%d (
    'message' => 'fopen(%s/missing/directory): Failed to open stream: No such file or directory'
    'string' => ''
    'code' => 0
    'file' => '%s/src/Foo.php'
    'line' => 26
    'previous' => null
))
Assertion Succeeded (Constraint: exception message contains 'Failed to open stream', Value: 'fopen(%s/missing/directory): Failed to open stream: No such file or directory')
Test Passed (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testMethodA)
Test Finished (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testMethodA)
Test Preparation Started (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testMethodB)
Test Prepared (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testMethodB)
Assertion Succeeded (Constraint: is identical to 'Triggering', Value: 'Triggering')
Test Passed (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testMethodB)
Test Finished (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest::testMethodB)
Test Suite Finished (PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled\FooTest, 2 tests)
Test Suite Finished (default, 2 tests)
Test Suite Finished (%s/phpunit.xml, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
