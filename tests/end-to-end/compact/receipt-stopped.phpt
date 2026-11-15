--TEST--
The receipt in the compact output says that the test run was stopped when a stopOn* setting ended it
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--compact';
$_SERVER['argv'][] = '--stop-on-failure';
$_SERVER['argv'][] = __DIR__ . '/../_files/basic/unit/StatusTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s


--- FAILURE: PHPUnit\TestFixture\Basic\StatusTest::testFailure
Failed asserting that false is true.

%sStatusTest.php:%d

FAILURES (2 tests, 2 assertions, 1 failure)

--- RECEIPT
PHPUnit: %s
PHP: %s
Arguments: --do-not-record-test-run-history --no-configuration --compact --stop-on-failure %s
Working directory: %s
Configuration: (none)
Bootstrap: (none)
Extensions: (none)
Started: %s
Ended: %s (stopped)
Shell exit code: 1
Tests: 2 run, 2 assertions, 1 failed
Issues triggered by tests: (none)
Issues triggered by the test runner: (none)
