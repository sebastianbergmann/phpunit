--TEST--
phpunit --compact ../_files/basic/unit/StatusTest
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--compact';
$_SERVER['argv'][] = __DIR__ . '/../_files/basic/unit/StatusTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s


--- FAILURE: PHPUnit\TestFixture\Basic\StatusTest::testFailure
Failed asserting that false is true.

%sStatusTest.php:%d

--- ERROR: PHPUnit\TestFixture\Basic\StatusTest::testError
RuntimeException:

%sStatusTest.php:%d

--- FAILURE: PHPUnit\TestFixture\Basic\StatusTest::testFailureWithMessage
failure with custom message
Failed asserting that false is true.

%sStatusTest.php:%d

--- ERROR: PHPUnit\TestFixture\Basic\StatusTest::testErrorWithMessage
RuntimeException: error with custom message

%sStatusTest.php:%d

ERRORS (13 tests, 4 assertions, 2 errors, 2 failures, 3 skipped, 2 incomplete, 2 risky)

--- RISKY: PHPUnit\TestFixture\Basic\StatusTest::testRisky
This test did not perform any assertions

--- RISKY: PHPUnit\TestFixture\Basic\StatusTest::testRiskyWithMessage
This test did not perform any assertions

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
Shell exit code: 2
Tests: 13 run, 4 assertions, 2 errored, 2 failed, 3 skipped, 2 incomplete, 2 risky
Issues triggered by tests: (none)
Issues triggered by the test runner: (none)
