--TEST--
A PHPUnit extension can subscribe to events
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/extension-bootstrap/phpunit-without-parameter.xml';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit\TestFixture\Event\MyExtension\MyExecutionFinishedSubscriber::notify
the-default-message
