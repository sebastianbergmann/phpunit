--TEST--
PHPUnit does not record an issue when a previously registered error handler turns the error into an exception
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/PreviousErrorHandlerThrowsTest.php';

require __DIR__ . '/../../bootstrap.php';

set_error_handler(static function (int $errorNumber, string $errorString, string $errorFile, int $errorLine): bool {
    throw new ErrorException($errorString, 0, $errorNumber, $errorFile, $errorLine);
});

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\PreviousErrorHandlerThrowsTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\PreviousErrorHandlerThrowsTest::testWarningIsTurnedIntoException)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\PreviousErrorHandlerThrowsTest::testWarningIsTurnedIntoException)
Test Passed (PHPUnit\TestFixture\ErrorHandler\PreviousErrorHandlerThrowsTest::testWarningIsTurnedIntoException)
Test Finished (PHPUnit\TestFixture\ErrorHandler\PreviousErrorHandlerThrowsTest::testWarningIsTurnedIntoException)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\PreviousErrorHandlerThrowsTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
