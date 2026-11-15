--TEST--
An exceeded time limit for the test run is printed as a record of its own in the compact output
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) echo 'skip: Extension pcntl is required';
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--compact';
$_SERVER['argv'][] = '--timeout';
$_SERVER['argv'][] = '1';
$_SERVER['argv'][] = __DIR__ . '/../_files/timeout/SlowTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s


--- ERROR: PHPUnit\TestFixture\Timeout\SlowTest::testOne
PHPUnit\Runner\TimeLimit\TimeLimitExceededException: This test was aborted because the time limit of 1 second for the test run was exceeded

%sSlowTest.php:%d

ERRORS (1 test, 0 assertions, 1 error)

--- TIME LIMIT EXCEEDED
The time limit of 1 second for the test run was exceeded.

--- RECEIPT
PHPUnit: %s
PHP: %s
Arguments: --do-not-record-test-run-history --no-configuration --compact --timeout 1 %s
Working directory: %s
Configuration: (none)
Bootstrap: (none)
Extensions: (none)
Started: %s
Ended: %s (time-limit-exceeded)
Shell exit code: 124
Tests: 1 run, 0 assertions, 1 errored
Issues triggered by tests: (none)
Issues triggered by the test runner: (none)
