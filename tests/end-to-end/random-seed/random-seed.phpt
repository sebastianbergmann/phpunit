--TEST--
--random-seed seeds the random number generator for each test
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/RandomNumberTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       PHP %s
Random Seed:   1234

setUpBeforeClass(): 453129364
testOne(): 1872927752
testTwo(): 1511467534
provider(): 1852620888
testThree(): 1193316594
Time: %s, Memory: %s

OK (3 tests, 3 assertions)
