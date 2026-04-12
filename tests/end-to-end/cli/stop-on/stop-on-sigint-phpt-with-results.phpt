--TEST--
Stopping test execution on SIGINT while PHPT test is running does not report the PHPT test as failed (non-debug mode)
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) echo 'skip: Extension pcntl is required';
if (!extension_loaded('posix')) echo 'skip: Extension posix is required';
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/InterruptPhptTest.phpt';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.%s

Test execution was interrupted by a signal.

Time: %s, Memory: %s

OK (1 test, 0 assertions)
