--TEST--
phpunit --parallel=2 does not bootstrap an extension that does not implement ParallelWorkerExtension in the worker: its subscribers receive the replayed events in the main process only
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/worker-unaware-extension/phpunit.xml';
$_SERVER['argv'][] = '--parallel=2';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %sphpunit.xml
Parallel:      2 workers

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 2 assertions)
