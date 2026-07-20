--TEST--
phpunit --parallel=2 --stop-on-failure does not start the next test suite when the previous test suite's last result trips the stop condition
--FILE--
<?php declare(strict_types=1);
$marker = sys_get_temp_dir() . '/phpunit-parallel-stop-on-failure.marker';

@unlink($marker);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/phpunit.xml';
$_SERVER['argv'][] = '--stop-on-failure';
$_SERVER['argv'][] = '--parallel=2';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

var_dump(is_file($marker));

@unlink($marker);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %s

F

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\ParallelStopOn\FailingTest::testThatFails
Failed asserting that false is true.

%sFailingTest.php:%d

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
bool(false)
