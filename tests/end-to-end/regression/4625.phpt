--TEST--
https://github.com/sebastianbergmann/phpunit/issues/4625
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/4625';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit error:

1) PHPUnit\TestFixture\Issue4625\Issue4625Test::testOne
The data provider specified for PHPUnit\TestFixture\Issue4625\Issue4625Test::testOne is invalid
The key must be an integer or a string, array given

%s:%d

ERRORS!
Tests: 1, Assertions: 1, Errors: 1.
