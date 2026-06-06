--TEST--
Deprecation filter configuration: nonexistent class emits PHPUnit warning
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/deprecation-filter-nonexistent-class';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Class PHPUnit\TestFixture\ErrorHandler\DeprecationFilter\NonExistentFilter cannot be used as a deprecation filter because it does not exist

OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Warnings: 1.
