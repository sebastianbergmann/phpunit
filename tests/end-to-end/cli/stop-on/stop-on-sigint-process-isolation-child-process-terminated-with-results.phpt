--TEST--
Stopping test execution on SIGINT that also terminates the child process of a test that is run in a separate process reports the test as skipped (non-debug mode)
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) {
    echo 'skip: Extension pcntl is required';
} elseif (!extension_loaded('posix')) {
    echo 'skip: Extension posix is required';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/InterruptTerminatingChildProcessTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

S

Test execution was interrupted by a signal.

Time: %s, Memory: %s

OK, but some tests were skipped!
Tests: 1, Assertions: 0, Skipped: 1.
