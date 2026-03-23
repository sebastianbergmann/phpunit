--TEST--
A PHPUnit extension can register multiple subscribers at once
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/extension-register-subscribers/phpunit.xml';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit\TestFixture\Event\RegisterSubscribers\MyExecutionStartedSubscriber::notify
PHPUnit\TestFixture\Event\RegisterSubscribers\MyExecutionFinishedSubscriber::notify
