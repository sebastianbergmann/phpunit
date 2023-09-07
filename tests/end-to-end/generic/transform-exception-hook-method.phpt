--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5300
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--dont-report-useless-tests';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/transform-exception-hook-method';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\TransformExceptionHookMethod\Test::testOne
PHPUnit\TestFixture\TransformExceptionHookMethod\TransformedException: transformed message

%s%eTest.php:24

ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
