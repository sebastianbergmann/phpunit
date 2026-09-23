--TEST--
phpunit --parallel=2 reports an extension whose shutdown fails in a worker with a test runner warning, once for every worker
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/failing-worker-shutdown/phpunit.xml';
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

There were 2 PHPUnit test runner warnings:

1) Shutdown of extension PHPUnit\TestFixture\ParallelWorkerExtension\FailingShutdown\Extension in a parallel worker process failed: the resource this extension flushes at the end of a worker is gone
%A
2) Shutdown of extension PHPUnit\TestFixture\ParallelWorkerExtension\FailingShutdown\Extension in a parallel worker process failed: the resource this extension flushes at the end of a worker is gone
%A
OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Warnings: 2.
