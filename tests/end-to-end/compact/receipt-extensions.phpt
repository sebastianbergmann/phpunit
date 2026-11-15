--TEST--
The receipt in the compact output names the bootstrap classes of extensions
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--compact';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../extension-xml/_files/extension-bootstrap/phpunit.xml';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

PHPUnit\TestFixture\Event\MyExtension\MyExecutionFinishedSubscriber::notify
the-message
OK (1 test, 1 assertion)

--- RECEIPT
PHPUnit: %s
PHP: %s
Arguments: --do-not-record-test-run-history --compact --configuration %s
Working directory: %s
Configuration: %sphpunit.xml (SHA-256: %s)
Bootstrap: %sautoload.php
Extensions: PHPUnit\TestFixture\Event\MyExtension\MyExtensionBootstrap
Started: %s
Ended: %s (completed)
Shell exit code: 0
Tests: 1 run, 1 assertion
Issues triggered by tests: (none)
Issues triggered by the test runner: (none)
