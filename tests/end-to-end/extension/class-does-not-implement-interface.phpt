--TEST--
Test runner exits with error when configured extension class does not implement the interface
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/class-does-not-implement-interface/phpunit.xml';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Cannot bootstrap extension because class PHPUnit\TestFixture\Event\MyExtension\MyExtensionBootstrap does not implement interface PHPUnit\Runner\Extension\Extension

WARNINGS!
Tests: 1, Assertions: 1, Warnings: 1.
