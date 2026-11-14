--TEST--
The steps of the minimal counterexample of a property that runs a state machine do not refer to the state machine with a variable, and do not assign a value of a pool to a variable, that is named like a parameter of the property, to which its argument is assigned before the steps
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/StateMachineOfPropertyWithParametersNamedLikeVariablesOfStepsTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\PropertyBasedTesting\StateMachineOfPropertyWithParametersNamedLikeVariablesOfStepsTest::testCountersAreNotIncremented
Failed asserting that true is false.

Property falsified after %s:

    $machine = 3;
    $counters1 = 7;

    $counters_1 = $stateMachine->counter();
    $stateMachine->increment(counter: $counters_1);
    $stateMachine->noCounterWasIncremented(); // invariant failed

%sStateMachineOfPropertyWithParametersNamedLikeVariablesOfStepsTest.php:%d
%sStateMachineOfPropertyWithParametersNamedLikeVariablesOfStepsTest.php:%d

FAILURES!
Tests: 1, Assertions: %d, Failures: 1.
