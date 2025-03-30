--TEST--
Test runner warning is triggered when an exception is triggered in an extension's event subscriber
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/exception-in-extension-subscriber/phpunit.xml';
$_SERVER['argv'][] = '--extension';
$_SERVER['argv'][] = 'PHPUnit\TestFixture\Event\MyExtension\MyExtensionBootstrap';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Exception in third-party event subscriber: message
%A

WARNINGS!
Tests: 1, Assertions: 1, Warnings: 1.
