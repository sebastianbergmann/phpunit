--TEST--
phpunit --compact --filter doesNotMatch ../../_files/Success.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--compact';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'doesNotMatch';
$_SERVER['argv'][] = __DIR__ . '/../../_files/Success.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

No tests executed!

--- RECEIPT
PHPUnit: %s
PHP: %s
Arguments: --do-not-record-test-run-history --no-configuration --compact --filter doesNotMatch %s
Working directory: %s
Configuration: (none)
Bootstrap: (none)
Extensions: (none)
Started: %s
Ended: %s (completed)
Shell exit code: 1
Tests: 0 run, 0 assertions
Issues triggered by tests: (none)
Issues triggered by the test runner: (none)
