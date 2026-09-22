--TEST--
phpunit --parallel=2 reports an extension whose bootstrap fails in the worker with a test runner warning and runs the tests all the same
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/failing-worker-extension/phpunit.xml';
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

There was 1 PHPUnit test runner warning:

1) Bootstrapping of extension PHPUnit\TestFixture\ParallelWorkerExtension\Failing\Extension in a parallel worker process failed: the resource this extension needs does not exist in a worker
%A
OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Warnings: 1.
