--TEST--
phpunit --parallel=2 advances the workers while a test that runs in a separate process, and therefore in the main process, waits for its child process before any other test has been started
--SKIPIF--
<?php declare(strict_types=1);
if (DIRECTORY_SEPARATOR === '\\') {
    print "skip: a child process cannot be waited for without blocking on Windows\n";
}
--FILE--
<?php declare(strict_types=1);
$handshake = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-parallel-in-process-unit-' . getmypid();

putenv('PHPUNIT_TEST_HANDSHAKE=' . $handshake);

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--exclude-filter';
$_SERVER['argv'][] = 'AFirstTest';
$_SERVER['argv'][] = __DIR__ . '/_files/advancing/';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

@unlink($handshake . '.started');
@unlink($handshake . '.created');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers

...                                                                 3 / 3 (100%)

Time: %s, Memory: %s

OK (3 tests, 6 assertions)
