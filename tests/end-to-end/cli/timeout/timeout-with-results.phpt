--TEST--
The --timeout CLI option reports the test that was running when the time limit for the test run was exceeded
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) echo 'skip: Extension pcntl is required';
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--timeout';
$_SERVER['argv'][] = '1';
$_SERVER['argv'][] = __DIR__ . '/../../_files/timeout/SlowTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

E

The time limit of 1 second for the test run was exceeded.

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\Timeout\SlowTest::testOne
PHPUnit\Runner\TimeLimit\TimeLimitExceededException: This test was aborted because the time limit of 1 second for the test run was exceeded

%sSlowTest.php:%d

ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
