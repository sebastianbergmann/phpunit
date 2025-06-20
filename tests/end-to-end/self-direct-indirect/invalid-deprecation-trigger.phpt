--TEST--
Test Runner warnings are displayed correctly when invalid deprecation triggers are configured in the XML configuration file
--FILE--
<?php declare(strict_types=1);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/invalid-deprecation-trigger';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There were 3 PHPUnit test runner warnings:

1) Function does_not_exist cannot be configured as a deprecation trigger because it is not declared

2) invalid-string cannot be configured as a deprecation trigger because it is not in ClassName::methodName format

3) Method DoesNotExist::doesNotExist cannot be configured as a deprecation trigger because it is not declared

OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Warnings: 3.
