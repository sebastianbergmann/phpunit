--TEST--
Warning is triggered when extension is configured more than once on the command line
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/extension-bootstrap/phpunit.xml';
$_SERVER['argv'][] = '--extension';
$_SERVER['argv'][] = 'PHPUnit\TestFixture\Event\MyExtension\MyExtensionBootstrap';
$_SERVER['argv'][] = '--extension';
$_SERVER['argv'][] = 'PHPUnit\TestFixture\Event\MyExtension\MyExtensionBootstrap';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

.                                                                   1 / 1 (100%)PHPUnit\TestFixture\Event\MyExtension\MyExecutionFinishedSubscriber::notify
the-message


Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Extension "PHPUnit\TestFixture\Event\MyExtension\MyExtensionBootstrap" is configured more than once on the command line

OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Warnings: 1.
