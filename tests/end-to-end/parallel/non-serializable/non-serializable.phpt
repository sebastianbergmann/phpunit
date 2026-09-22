--TEST--phpunit --parallel=2 runs tests whose data cannot be serialized in the main process instead of a worker, and reports each such class with a test runner notice that names the test and what is wrong with its data
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--display-phpunit-notices';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = __DIR__ . '/_files/';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers

..                                                                  2 / 2 (100%)

Time: %s, Memory: %s

Closure Data (PHPUnit\TestFixture\ParallelNonSerializable\ClosureData)
 ✔ Receives a callable with data set #0

Resource Data (PHPUnit\TestFixture\ParallelNonSerializable\ResourceData)
 ✔ Receives an open resource with data set #0

There were 2 PHPUnit test runner notices:

1) The tests of class PHPUnit\TestFixture\ParallelNonSerializable\ClosureDataTest are run in the main process instead of a parallel worker because the data of test testReceivesACallable with data set #0 cannot be serialized: Serialization of 'Closure' is not allowed

2) The tests of class PHPUnit\TestFixture\ParallelNonSerializable\ResourceDataTest are run in the main process instead of a parallel worker because the data of test testReceivesAnOpenResource with data set #0 cannot be serialized: it contains a resource

OK, but there were issues!
Tests: 2, Assertions: 2, PHPUnit Notices: 2.
