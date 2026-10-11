--TEST--
--random-seed: a test gets the same random numbers when it is run on its own
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'testTwo';
$_SERVER['argv'][] = __DIR__ . '/_files/RandomNumberTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       PHP %s
Random Seed:   1234

setUpBeforeClass(): 453129364
testTwo(): 1511467534
Time: %s, Memory: %s

OK (1 test, 1 assertion)
