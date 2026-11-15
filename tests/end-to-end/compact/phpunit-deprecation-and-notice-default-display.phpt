--TEST--
phpunit --compact ../_files/PhpunitDeprecationAndNoticeTest (default display flags)
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--compact';
$_SERVER['argv'][] = __DIR__ . '/../_files/PhpunitDeprecationAndNoticeTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

OK (3 tests, 3 assertions, 1 PHPUnit deprecation, 1 PHPUnit notice)

--- RECEIPT
PHPUnit: %s
PHP: %s
Arguments: --do-not-record-test-run-history --no-configuration --compact %s
Working directory: %s
Configuration: (none)
Bootstrap: (none)
Extensions: (none)
Started: %s
Ended: %s (completed)
Shell exit code: 0
Tests: 3 run, 3 assertions
Issues triggered by tests: 1 PHPUnit deprecation, 1 PHPUnit notice
Issues triggered by the test runner: (none)
