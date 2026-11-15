--TEST--
phpunit --compact --no-progress
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--compact';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = __DIR__ . '/../_files/basic/SuccessTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

OK (1 test, 1 assertion)

--- RECEIPT
PHPUnit: %s
PHP: %s
Arguments: --do-not-record-test-run-history --no-configuration --compact --no-progress %s
Working directory: %s
Configuration: (none)
Bootstrap: (none)
Extensions: (none)
Started: %s
Ended: %s (completed)
Shell exit code: 0
Tests: 1 run, 1 assertion
Issues triggered by tests: (none)
Issues triggered by the test runner: (none)
