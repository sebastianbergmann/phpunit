--TEST--
The compact output shows how a property was falsified, with its minimal counterexample, below the failure message
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--compact';
$_SERVER['argv'][] = __DIR__ . '/../property-based-testing/_files/FalsifiedPropertyTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234


--- FAILURE: PHPUnit\TestFixture\PropertyBasedTesting\FalsifiedPropertyTest::testSumIsAtMostTen
Failed asserting that 11 is equal to 10 or is less than 10.

Property falsified after 1 example, counterexample shrunk in 2 steps:

    $a = 0;
    $b = 11;

%sFalsifiedPropertyTest.php:%d

FAILURES (1 test, 2 assertions, 1 failure)
