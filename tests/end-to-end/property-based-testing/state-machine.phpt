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

    steps: count 100, min 1, median 25, mean 24.24, p90 45, max 50
    steps with open(): count 100, min 0, median 1, mean 5.8, p90 15, max 39
    steps with deposit(): count 100, min 0, median 0, mean 4.72, p90 12, max 47
    steps with withdraw(): count 100, min 0, median 0, mean 4.13, p90 13, max 42
    steps with catchUpProjection(): count 100, min 0, median 4, mean 9.59, p90 29, max 50

OK (1 test, %d assertions)
