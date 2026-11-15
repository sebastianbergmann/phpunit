--TEST--
A PHPUnit extension can replace the progress output when compact output is used
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--compact';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/extension-replace-progress-output-compact/phpunit.xml';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

progress output replaced by extension
FAILURES (1 test, 1 assertion, 1 failure)

--- RECEIPT
PHPUnit: %s
PHP: %s
Arguments: --do-not-record-test-run-history --compact --configuration %s
Working directory: %s
Configuration: %sphpunit.xml (SHA-256: %s)
Bootstrap: %sautoload.php
Extensions: PHPUnit\TestFixture\Event\ReplaceProgressOutputCompact\MyExtensionBootstrap
Started: %s
Ended: %s (completed)
Shell exit code: 1
Tests: 1 run, 1 assertion, 1 failed
Issues triggered by tests: (none)
Issues triggered by the test runner: (none)
