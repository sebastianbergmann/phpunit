--TEST--
runStateMachine() can only be used once in a generated example of a property, with a positive maximum number of steps, and an invalid state machine is reported as the error of the property without shrinking
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/state-machine/InvalidUseOfStateMachinesTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

EEEEER                                                              6 / 6 (100%)

Time: %s, Memory: %s

There were 5 errors:

1) PHPUnit\TestFixture\PropertyBasedTesting\StateMachine\InvalidUseOfStateMachinesTest::testStateMachineIsRunOutsideOfAProperty
PHPUnit\Framework\Exception: runStateMachine() can only be used in a property

%sInvalidUseOfStateMachinesTest.php:%d

2) PHPUnit\TestFixture\PropertyBasedTesting\StateMachine\InvalidUseOfStateMachinesTest::testStateMachineIsRunForAnExplicitExample
PHPUnit\Framework\Exception: runStateMachine() cannot be used while an explicit example is checked

Property falsified by explicit example #0:

    $value = 0;

%sInvalidUseOfStateMachinesTest.php:%d

3) PHPUnit\TestFixture\PropertyBasedTesting\StateMachine\InvalidUseOfStateMachinesTest::testStateMachineIsRunTwice
PHPUnit\Framework\Exception: runStateMachine() can only be used once per example

Property falsified after 1 example.

%sInvalidUseOfStateMachinesTest.php:%d

4) PHPUnit\TestFixture\PropertyBasedTesting\StateMachine\InvalidUseOfStateMachinesTest::testStateMachineIsRunWithoutSteps
PHPUnit\Framework\Exception: The maximum number of steps passed to runStateMachine() must be positive

Property falsified after 1 example.

%sInvalidUseOfStateMachinesTest.php:%d

5) PHPUnit\TestFixture\PropertyBasedTesting\StateMachine\InvalidUseOfStateMachinesTest::testStateMachineIsInvalid
PHPUnit\Framework\PropertyBased\InvalidStateMachineException: State machine PHPUnit\TestFixture\PropertyBasedTesting\StateMachine\MachineWithoutRules cannot be run: it has no method with #[Rule]

%sInvalidUseOfStateMachinesTest.php:%d

--

There was 1 risky test:

1) PHPUnit\TestFixture\PropertyBasedTesting\StateMachine\InvalidUseOfStateMachinesTest::testStepsOfStateMachineCannotBeGenerated
This property was checked for only 0 of 100 examples because generating examples was stopped after 50 examples were rejected because no value that satisfies a filter was generated while fewer than 10 examples were checked; generate the values that the property needs instead of rejecting the others

%sInvalidUseOfStateMachinesTest.php:%d

ERRORS!
Tests: 6, Assertions: %d, Errors: 5, Risky: 1.
