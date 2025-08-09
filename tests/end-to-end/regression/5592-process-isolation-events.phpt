--TEST--
https://github.com/sebastianbergmann/phpunit/pull/5592
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--process-isolation';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/5592/Issue5592Test.php';

set_exception_handler(static fn () => null);

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
Test Suite Started (PHPUnit\TestFixture\Issue5592Test, 6 tests)
Child Process Started
Test Preparation Started (PHPUnit\TestFixture\Issue5592Test::testAddedAndRemovedErrorHandler)
Test Prepared (PHPUnit\TestFixture\Issue5592Test::testAddedAndRemovedErrorHandler)
Test Passed (PHPUnit\TestFixture\Issue5592Test::testAddedAndRemovedErrorHandler)
Test Finished (PHPUnit\TestFixture\Issue5592Test::testAddedAndRemovedErrorHandler)
Child Process Finished
Child Process Started
Test Preparation Started (PHPUnit\TestFixture\Issue5592Test::testAddedErrorHandler)
Test Prepared (PHPUnit\TestFixture\Issue5592Test::testAddedErrorHandler)
Test Failed (PHPUnit\TestFixture\Issue5592Test::testAddedErrorHandler)
Failed asserting that false is true.
Test Finished (PHPUnit\TestFixture\Issue5592Test::testAddedErrorHandler)
Child Process Finished
Child Process Started
Test Preparation Started (PHPUnit\TestFixture\Issue5592Test::testRemovedErrorHandler)
Test Prepared (PHPUnit\TestFixture\Issue5592Test::testRemovedErrorHandler)
Test Failed (PHPUnit\TestFixture\Issue5592Test::testRemovedErrorHandler)
Failed asserting that false is true.
Test Considered Risky (PHPUnit\TestFixture\Issue5592Test::testRemovedErrorHandler)
Test code or tested code removed error handlers other than its own
Test Finished (PHPUnit\TestFixture\Issue5592Test::testRemovedErrorHandler)
Child Process Finished
Child Process Started
Test Preparation Started (PHPUnit\TestFixture\Issue5592Test::testAddedAndRemovedExceptionHandler)
Test Prepared (PHPUnit\TestFixture\Issue5592Test::testAddedAndRemovedExceptionHandler)
Test Passed (PHPUnit\TestFixture\Issue5592Test::testAddedAndRemovedExceptionHandler)
Test Finished (PHPUnit\TestFixture\Issue5592Test::testAddedAndRemovedExceptionHandler)
Child Process Finished
Child Process Started
Test Preparation Started (PHPUnit\TestFixture\Issue5592Test::testAddedExceptionHandler)
Test Prepared (PHPUnit\TestFixture\Issue5592Test::testAddedExceptionHandler)
Test Failed (PHPUnit\TestFixture\Issue5592Test::testAddedExceptionHandler)
Failed asserting that false is true.
Test Finished (PHPUnit\TestFixture\Issue5592Test::testAddedExceptionHandler)
Child Process Finished
Child Process Started
Test Preparation Started (PHPUnit\TestFixture\Issue5592Test::testRemovedExceptionHandler)
Test Prepared (PHPUnit\TestFixture\Issue5592Test::testRemovedExceptionHandler)
Test Failed (PHPUnit\TestFixture\Issue5592Test::testRemovedExceptionHandler)
Failed asserting that false is true.
Test Finished (PHPUnit\TestFixture\Issue5592Test::testRemovedExceptionHandler)
Child Process Finished
Test Suite Finished (PHPUnit\TestFixture\Issue5592Test, 6 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
