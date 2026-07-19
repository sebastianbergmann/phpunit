--TEST--
phpunit --parallel=3 --stop-on-failure runs the CLEAN section of a PHPT test whose FILE section it terminated when the run stopped, and does not start the FILE section of a test whose SKIPIF section it terminated
--FILE--
<?php declare(strict_types=1);
$dirty   = sys_get_temp_dir() . '/phpunit-parallel-stop-on-clean.dirty';
$cleaned = sys_get_temp_dir() . '/phpunit-parallel-stop-on-clean.cleaned';
$fileRan = sys_get_temp_dir() . '/phpunit-parallel-stop-on-clean.file-ran';

@unlink($dirty);
@unlink($cleaned);
@unlink($fileRan);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--stop-on-failure';
$_SERVER['argv'][] = '--parallel=3';
$_SERVER['argv'][] = __DIR__ . '/_files/FailingTest.php';
$_SERVER['argv'][] = __DIR__ . '/_files/sleeping-with-clean.phpt';
$_SERVER['argv'][] = __DIR__ . '/_files/sleeping-skipif.phpt';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

var_dump(is_file($cleaned), is_file($dirty), is_file($fileRan));

@unlink($dirty);
@unlink($cleaned);
@unlink($fileRan);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

F

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\ParallelStopOnClean\FailingTest::testThatFails
Failed asserting that false is true.

%sFailingTest.php:%d

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
bool(true)
bool(false)
bool(false)
