--TEST--
--stop-on-failure fails to stop on PHP 7
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--stop-on-error';
$_SERVER['argv'][] = __DIR__ . '/2145/Issue2145Test.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

E

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\Issue2145Test
Exception: message

%s:%d

ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
