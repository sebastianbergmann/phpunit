--TEST--
The minimal counterexample of a property that runs a state machine is reported with its steps: values that rules produce are assigned to variables, other return values are comments, and an invariant is reported when it failed
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/state-machine/BankTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

.FF                                                                 3 / 3 (100%)

Time: %s, Memory: %s

There were 2 failures:

1) PHPUnit\TestFixture\PropertyBasedTesting\StateMachine\BankTest::testAccountsAreRebuiltFromTheirEvents
Failed asserting that 2 is identical to 0.

Property falsified after 5 examples, counterexample shrunk in 34 steps:

    $accounts1 = $machine->open();
    $machine->deposit(account: $accounts1, amount: 1); // [new MoneyDeposited(account: new AccountId(value: 'account-1'), amount: 1)]
    $machine->withdraw(account: $accounts1, amount: 1); // [new MoneyWithdrawn(account: new AccountId(value: 'account-1'), amount: 1)]
    $machine->accountsRebuiltFromTheirEventsMatchTheModel(); // invariant failed

%sBankMachine.php:%d
%sBankTest.php:%d

2) PHPUnit\TestFixture\PropertyBasedTesting\StateMachine\BankTest::testProjectionCatchesUpWithTheEventStore
Failed asserting that two arrays are identical.
--- Expected
+++ Actual
@@ @@
 Array &0 [
     'account-1' => 0,
-    'account-2' => 0,
 ]

Property falsified after 4 examples, counterexample shrunk in 17 steps:

    $accounts1 = $machine->open();
    $machine->catchUpProjection();
    $accounts2 = $machine->open();
    $machine->catchUpProjection();
    $machine->projectionMatchesTheModel(); // invariant failed

%sBankMachine.php:%d
%sBankTest.php:%d

FAILURES!
Tests: 3, Assertions: %d, Failures: 2.
