--TEST--
The receipt in the compact output names the XML configuration file, the checksum of its contents, and the bootstrap scripts
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--compact';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/bootstrap-for-test-suite/phpunit.xml';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

OK (2 tests, 2 assertions)

--- RECEIPT
PHPUnit: %s
PHP: %s
Arguments: --do-not-record-test-run-history --compact --configuration %s
Working directory: %s
Configuration: %sphpunit.xml (SHA-256: %s)
Bootstrap: %sbootstrap.php, %sbootstrap_one.php, %sbootstrap_two.php
Extensions: (none)
Started: %s
Ended: %s (completed)
Shell exit code: 0
Tests: 2 run, 2 assertions
Issues triggered by tests: (none)
Issues triggered by the test runner: (none)
