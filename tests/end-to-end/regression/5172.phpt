--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5172
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--display-phpunit-deprecations';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/5172/phpunit.xml';
$_SERVER['argv'][] = '--testdox';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

Issue5172 (PHPUnit\TestFixture\Issue5172)
 ✔ One

There was 1 PHPUnit test runner deprecation:

1) Your XML configuration validates against a deprecated schema. Migrate your XML configuration using "--migrate-configuration"!

OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Deprecations: 1.
