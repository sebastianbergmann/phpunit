--TEST--
A property is considered risky when the state machine that it runs took no step in most of its examples because none of its rules could be applied
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/state-machine/StateMachineWithRarelyApplicableRulesTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

R                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 risky test:

1) PHPUnit\TestFixture\PropertyBasedTesting\StateMachine\StateMachineWithRarelyApplicableRulesTest::testStateMachineRarelyTakesSteps
The state machine of this property took no step in 100 of 100 examples because none of its rules could be applied; make sure that the preconditions of the rules hold and that values are produced for the pools from which they take values

%sStateMachineWithRarelyApplicableRulesTest.php:%d

OK, but there were issues!
Tests: 1, Assertions: 100, Risky: 1.
