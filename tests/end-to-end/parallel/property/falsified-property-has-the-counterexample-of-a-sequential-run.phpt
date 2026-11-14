--TEST--
phpunit without --parallel falsifies the property that property-is-checked-in-worker.phpt checks in a worker with the same counterexample, shrunk in the same number of steps
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/checked/BFalsifiedTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Random Seed:   1234

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\ParallelProperty\BFalsifiedTest::testSumIsAtMostTen
Failed asserting that 11 is equal to 10 or is less than 10.

Property falsified after 1 example, counterexample shrunk in 4 steps:

    $a = 0;
    $b = 11;

%sBFalsifiedTest.php:%d

FAILURES!
Tests: 1, Assertions: 2, Failures: 1.
