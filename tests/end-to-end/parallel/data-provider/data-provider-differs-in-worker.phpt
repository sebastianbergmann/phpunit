--TEST--
phpunit --parallel=2 reports the tests of a class whose data provider fails in the worker, or does not provide the data set the main process selected, as errored with a message that names the cause
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = __DIR__ . '/_files/failing/';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers

EE                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There were 2 errors:

1) PHPUnit\TestFixture\ParallelDataProvider\FailingInWorkerDataTest::testWithADataProviderThatFailsInAWorker#0 with data (true)
The data provider for PHPUnit\TestFixture\ParallelDataProvider\FailingInWorkerDataTest::testWithADataProviderThatFailsInAWorker failed when the worker process invoked it: %s

2) PHPUnit\TestFixture\ParallelDataProvider\UnstableDataSetNameDataTest::testWithADataSetWhoseNameIsUnstable@run-%s with data (true)
The data provider for PHPUnit\TestFixture\ParallelDataProvider\UnstableDataSetNameDataTest::testWithADataSetWhoseNameIsUnstable did not provide data set "run-%s" when the worker process invoked it; a data provider must provide the same data sets, under the same names, in every process

ERRORS!
Tests: 2, Assertions: 0, Errors: 2.
