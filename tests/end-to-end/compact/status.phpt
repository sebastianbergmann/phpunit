--TEST--
phpunit --compact ../_files/basic/unit/StatusTest
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--compact';
$_SERVER['argv'][] = __DIR__ . '/../_files/basic/unit/StatusTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

ERRORS (13 tests, 4 assertions, 2 errors, 2 failures, 3 skipped, 2 incomplete, 2 risky)

--- ERROR: PHPUnit\TestFixture\Basic\StatusTest::testError
RuntimeException:

%sStatusTest.php:%d

--- ERROR: PHPUnit\TestFixture\Basic\StatusTest::testErrorWithMessage
RuntimeException: error with custom message

%sStatusTest.php:%d

--- FAILURE: PHPUnit\TestFixture\Basic\StatusTest::testFailure
Failed asserting that false is true.

%sStatusTest.php:%d

--- FAILURE: PHPUnit\TestFixture\Basic\StatusTest::testFailureWithMessage
failure with custom message
Failed asserting that false is true.

%sStatusTest.php:%d

--- RISKY: PHPUnit\TestFixture\Basic\StatusTest::testRisky
This test did not perform any assertions

--- RISKY: PHPUnit\TestFixture\Basic\StatusTest::testRiskyWithMessage
This test did not perform any assertions
