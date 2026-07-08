--TEST--
PHPUnit does not enter an infinite loop when previous error handler calls it back
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/PreviousErrorHandlerTest.php';

require __DIR__ . '/../../bootstrap.php';

use PHPUnit\Runner\ErrorHandler;

set_error_handler(static function (int $errno, string $errstr, string $errfile, int $errline): bool {
    return ErrorHandler::instance()->__invoke($errno, $errstr, $errfile, $errline);
});

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       PHP %s

N                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK, but there were issues!
Tests: 1, Assertions: 1, Notices: 1.
