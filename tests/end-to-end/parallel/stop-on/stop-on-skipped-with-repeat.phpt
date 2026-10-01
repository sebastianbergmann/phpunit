--TEST--
phpunit --parallel=2 --repeat 3 --stop-on-skipped reports no test after the first repetition that is skipped because an earlier repetition failed, as a sequential run does
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '3';
$_SERVER['argv'][] = '--stop-on-skipped';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = __DIR__ . '/_files/repeat/';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers

FS

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\ParallelStopOn\RepeatedlyFailingTest::testFails (repetition 1 of 3)
failure

%sRepeatedlyFailingTest.php:%d

FAILURES!
Tests: 2, Assertions: 1, Failures: 1, Skipped: 1.
