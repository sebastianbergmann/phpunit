--TEST--
PHPUnit chains previous error handler for file-scope errors
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/PreviousErrorHandlerFileScopeTest.php';

require __DIR__ . '/../../bootstrap.php';

set_error_handler(static function (int $errno, string $errstr): bool {
    return false;
});

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Runner Triggered Notice () in %sPreviousErrorHandlerFileScopeTest.php:%d
file scope user notice
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\PreviousErrorHandlerFileScopeTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\PreviousErrorHandlerFileScopeTest::testSuccess)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\PreviousErrorHandlerFileScopeTest::testSuccess)
Test Passed (PHPUnit\TestFixture\ErrorHandler\PreviousErrorHandlerFileScopeTest::testSuccess)
Test Finished (PHPUnit\TestFixture\ErrorHandler\PreviousErrorHandlerFileScopeTest::testSuccess)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\PreviousErrorHandlerFileScopeTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
