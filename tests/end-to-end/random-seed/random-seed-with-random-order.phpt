--TEST--
--random-seed: a test gets the same random numbers when tests are run in random order
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--order-by';
$_SERVER['argv'][] = 'random';
$_SERVER['argv'][] = __DIR__ . '/_files/RandomNumberTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       PHP %s
Random Seed:   1234

setUpBeforeClass(): 453129364
provider(): 1852620888
testThree(): 1193316594
testTwo(): 1511467534
testOne(): 1872927752
Time: %s, Memory: %s

OK (3 tests, 3 assertions)
