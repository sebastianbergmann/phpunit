--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6329
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--display-phpunit-deprecations';
$_SERVER['argv'][] = __DIR__ . '/6329/Issue6329DeprecationTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s MB

There was 1 PHPUnit test runner deprecation:

1) A runner deprecation!

OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Deprecations: 1.
