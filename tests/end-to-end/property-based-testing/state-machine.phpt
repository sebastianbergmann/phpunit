--TEST--
A property runs a state machine with runStateMachine(), and --display-property-statistics displays how many steps its examples took, in total and with each rule
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--display-property-statistics';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'testBankKeepsTheBalancesOfAccounts';
$_SERVER['argv'][] = __DIR__ . '/_files/state-machine/BankTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

Statistics of 1 property:

1) PHPUnit\TestFixture\PropertyBasedTesting\StateMachine\BankTest::testBankKeepsTheBalancesOfAccounts
100 examples checked, 0 rejected

    steps: count 100, min 1, median 24, mean 25.7, p90 46, max 50
    steps with open(): count 100, min 0, median 1, mean 6.2, p90 18, max 49
    steps with deposit(): count 100, min 0, median 0, mean 5.42, p90 21, max 37
    steps with withdraw(): count 100, min 0, median 0, mean 4.74, p90 16, max 44
    steps with catchUpProjection(): count 100, min 0, median 3, mean 9.34, p90 30, max 50

OK (1 test, %d assertions)
