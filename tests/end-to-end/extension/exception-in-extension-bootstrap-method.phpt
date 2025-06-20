--TEST--
Test runner warning is triggered when an exception is triggered in an extension's bootstrap method
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/exception-in-extension-bootstrap-method/phpunit.xml';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Bootstrapping of extension PHPUnit\TestFixture\Event\MyExtension\MyExtensionBootstrap failed: message
%A

OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Warnings: 1.
