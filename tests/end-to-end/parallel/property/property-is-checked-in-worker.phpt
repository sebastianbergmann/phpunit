--TEST--
phpunit --parallel=2 checks a property inside the worker that runs its class, with the examples and the counterexample of a sequential run with the same random seed
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = __DIR__ . '/_files/checked/';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers
Random Seed:   1234

..F                                                                 3 / 3 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\ParallelProperty\BFalsifiedTest::testSumIsAtMostTen
Failed asserting that 11 is equal to 10 or is less than 10.

Property falsified after 1 example, counterexample shrunk in 4 steps:

    $a = 0;
    $b = 11;

%sBFalsifiedTest.php:%d

FAILURES!
Tests: 3, Assertions: 13, Failures: 1.
