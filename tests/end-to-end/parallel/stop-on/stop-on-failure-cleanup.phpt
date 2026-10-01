--TEST--
phpunit --parallel=2 --stop-on-failure lets the test that another worker is running when the run stops finish, starts no further test of its class, and runs tearDown() and tearDownAfterClass(), so that the class does not leave its fixtures behind
--FILE--
<?php declare(strict_types=1);
$marker = sys_get_temp_dir() . '/phpunit-parallel-stop-on-failure-cleanup.marker';

@unlink($marker);

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--stop-on-failure';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = __DIR__ . '/_files/cleanup/';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($marker);

@unlink($marker);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers

F

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\ParallelStopOn\AFailsOnceTheOtherTestHasStartedTest::testFails
failure

%sAFailsOnceTheOtherTestHasStartedTest.php:%d

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
testIsRunningWhenTheRunStops
tearDown
tearDownAfterClass
