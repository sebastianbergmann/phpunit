--TEST--
--parallel auto runs the test classes using one worker process per available CPU core
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel';
$_SERVER['argv'][] = 'auto';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = __DIR__ . '/_files/';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

..                                                                  2 / 2 (100%)

Time: %s, Memory: %s

First Auto (PHPUnit\TestFixture\ParallelAuto\FirstAuto)
 ✔ One

Second Auto (PHPUnit\TestFixture\ParallelAuto\SecondAuto)
 ✔ Two

OK (2 tests, 2 assertions)
