--TEST--
Stopping test execution on SIGINT while process isolation test is running does not report the test as failed (TestDox output)
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
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/InterruptProcessIsolationTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.

Test execution was interrupted by a signal.

Time: %s, Memory: %s

Interrupt Process Isolation (PHPUnit\TestFixture\TestRunnerStopping\InterruptProcessIsolation)
 ✔ One

OK (1 test, 1 assertion)
