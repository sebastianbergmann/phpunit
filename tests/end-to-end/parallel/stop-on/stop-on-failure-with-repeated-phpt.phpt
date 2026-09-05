--TEST--
phpunit --repeat 2 --parallel=2 --stop-on-failure abandons a repeated PHPT test as a whole: the repetition that is running is terminated and the repetitions that have not started are not run
--FILE--
<?php declare(strict_types=1);
$marker = sys_get_temp_dir() . '/phpunit-parallel-stop-on-failure.marker';

@unlink($marker);

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--stop-on-failure';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = __DIR__ . '/_files/FailingTest.php';
$_SERVER['argv'][] = __DIR__ . '/_files/sleeping.phpt';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

// Neither the repetition that was running when the run stopped nor the one
// that had not started got as far as writing the marker.
var_dump(is_file($marker));

@unlink($marker);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers

F

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\ParallelStopOn\FailingTest::testThatFails (repetition 1 of 2)
Failed asserting that false is true.

%sFailingTest.php:%d

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
bool(false)
