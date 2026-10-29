--TEST--
phpunit --parallel=2 --recycle-workers-after=1 replaces a worker with a fresh process after every test class, so that every class is the first to run in its process
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--bootstrap';
$_SERVER['argv'][] = __DIR__ . '/_files/tests/Probe.php';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--recycle-workers-after=1';
$_SERVER['argv'][] = __DIR__ . '/_files/tests/';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers

....                                                                4 / 4 (100%)

Time: %s, Memory: %s

OK (4 tests, 4 assertions)
