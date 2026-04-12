--TEST--
PHPUnit chains previous error handler instead of backing off
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/PreviousErrorHandlerTest.php';

require __DIR__ . '/../../bootstrap.php';

set_error_handler(static function (int $errno, string $errstr): bool {
    return false;
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
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\PreviousErrorHandlerTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\PreviousErrorHandlerTest::testUserNotice)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\PreviousErrorHandlerTest::testUserNotice)
Test Triggered Notice (PHPUnit\TestFixture\ErrorHandler\PreviousErrorHandlerTest::testUserNotice) in %s:%d
notice from test
Test Passed (PHPUnit\TestFixture\ErrorHandler\PreviousErrorHandlerTest::testUserNotice)
Test Finished (PHPUnit\TestFixture\ErrorHandler\PreviousErrorHandlerTest::testUserNotice)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\PreviousErrorHandlerTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
