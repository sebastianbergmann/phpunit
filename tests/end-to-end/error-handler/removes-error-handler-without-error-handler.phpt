--TEST--
A test that does not use PHPUnit's error handler and removes an error handler it did not register is considered risky
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/RemovesErrorHandlerWithoutErrorHandlerTest.php';

// Simulate an error handler registered via auto_prepend_file
set_error_handler(static fn () => false);

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

R                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 risky test:

1) PHPUnit\TestFixture\ErrorHandler\RemovesErrorHandlerWithoutErrorHandlerTest::testRemovesErrorHandlerThatItDidNotRegister
Test code or tested code removed error handlers other than its own

%sRemovesErrorHandlerWithoutErrorHandlerTest.php:%i

OK, but there were issues!
Tests: 1, Assertions: 1, Risky: 1.
