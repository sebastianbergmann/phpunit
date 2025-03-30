--TEST--
phpunit --configuration ../_files/baseline/unsupported-baseline/phpunit.xml --use-baseline does-not-exist.xml
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/baseline/unsupported-baseline/phpunit.xml';
$_SERVER['argv'][] = '--use-baseline ';
$_SERVER['argv'][] = 'does-not-exist.xml';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Cannot read baseline %sdoes-not-exist.xml, file does not exist

WARNINGS!
Tests: 1, Assertions: 1, Warnings: 1.
