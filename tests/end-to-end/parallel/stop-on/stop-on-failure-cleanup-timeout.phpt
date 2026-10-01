--TEST--
phpunit --parallel=2 --stop-on-failure --timeout 3 terminates the test that another worker is running when the run stops once the time limit for the test run is exceeded
--FILE--
<?php declare(strict_types=1);
$marker = sys_get_temp_dir() . '/phpunit-parallel-stop-on-failure-timeout.marker';

@unlink($marker);

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--stop-on-failure';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--timeout';
$_SERVER['argv'][] = '3';
$_SERVER['argv'][] = __DIR__ . '/_files/cleanup-timeout/';

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

1) PHPUnit\TestFixture\ParallelStopOn\AFailsOnceTheSleepingTestHasStartedTest::testFails
failure

%sAFailsOnceTheSleepingTestHasStartedTest.php:%d

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
testSleeps
