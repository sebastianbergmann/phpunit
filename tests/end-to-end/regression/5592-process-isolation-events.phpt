--TEST--
https://github.com/sebastianbergmann/phpunit/pull/5592
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--process-isolation';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/5592/Issue5592TestIsolation.php';

function global5592IsolationEventsExceptionHandler(Throwable $exception): void
{
}

set_exception_handler('global5592IsolationEventsExceptionHandler');

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (6 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (6 tests)
Test Suite Started (PHPUnit\TestFixture\Issue5592TestIsolation, 6 tests)
Child Process Started
Test Preparation Started (PHPUnit\TestFixture\Issue5592TestIsolation::testAddedAndRemovedErrorHandler)
Test Prepared (PHPUnit\TestFixture\Issue5592TestIsolation::testAddedAndRemovedErrorHandler)
Test Passed (PHPUnit\TestFixture\Issue5592TestIsolation::testAddedAndRemovedErrorHandler)
Test Finished (PHPUnit\TestFixture\Issue5592TestIsolation::testAddedAndRemovedErrorHandler)
Child Process Finished
Child Process Started
Test Preparation Started (PHPUnit\TestFixture\Issue5592TestIsolation::testAddedErrorHandler)
Test Prepared (PHPUnit\TestFixture\Issue5592TestIsolation::testAddedErrorHandler)
Test Failed (PHPUnit\TestFixture\Issue5592TestIsolation::testAddedErrorHandler)
Failed asserting that false is true.
Test Finished (PHPUnit\TestFixture\Issue5592TestIsolation::testAddedErrorHandler)
Child Process Finished
Child Process Started
Test Preparation Started (PHPUnit\TestFixture\Issue5592TestIsolation::testRemovedErrorHandler)
Test Prepared (PHPUnit\TestFixture\Issue5592TestIsolation::testRemovedErrorHandler)
Test Failed (PHPUnit\TestFixture\Issue5592TestIsolation::testRemovedErrorHandler)
Failed asserting that false is true.
Test Considered Risky (PHPUnit\TestFixture\Issue5592TestIsolation::testRemovedErrorHandler)
Test code or tested code removed error handlers other than its own
Test Finished (PHPUnit\TestFixture\Issue5592TestIsolation::testRemovedErrorHandler)
Child Process Finished
Child Process Started
Test Preparation Started (PHPUnit\TestFixture\Issue5592TestIsolation::testAddedAndRemovedExceptionHandler)
Test Prepared (PHPUnit\TestFixture\Issue5592TestIsolation::testAddedAndRemovedExceptionHandler)
Test Passed (PHPUnit\TestFixture\Issue5592TestIsolation::testAddedAndRemovedExceptionHandler)
Test Finished (PHPUnit\TestFixture\Issue5592TestIsolation::testAddedAndRemovedExceptionHandler)
Child Process Finished
Child Process Started
Test Preparation Started (PHPUnit\TestFixture\Issue5592TestIsolation::testAddedExceptionHandler)
Test Prepared (PHPUnit\TestFixture\Issue5592TestIsolation::testAddedExceptionHandler)
Test Failed (PHPUnit\TestFixture\Issue5592TestIsolation::testAddedExceptionHandler)
Failed asserting that false is true.
Test Finished (PHPUnit\TestFixture\Issue5592TestIsolation::testAddedExceptionHandler)
Child Process Finished
Child Process Started
Test Preparation Started (PHPUnit\TestFixture\Issue5592TestIsolation::testRemovedExceptionHandler)
Test Prepared (PHPUnit\TestFixture\Issue5592TestIsolation::testRemovedExceptionHandler)
Test Failed (PHPUnit\TestFixture\Issue5592TestIsolation::testRemovedExceptionHandler)
Failed asserting that false is true.
Test Finished (PHPUnit\TestFixture\Issue5592TestIsolation::testRemovedExceptionHandler)
Child Process Finished
Test Suite Finished (PHPUnit\TestFixture\Issue5592TestIsolation, 6 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
