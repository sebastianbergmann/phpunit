--TEST--
A state machine with an attribute that cannot be instantiated, because the name of one of its arguments is misspelled, for instance, is reported as the error of the property rather than run without the invariant or with a parameter that does not take its values from the pool
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/state-machine/StateMachineWithInvalidAttributeTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

EE                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There were 2 errors:

1) PHPUnit\TestFixture\PropertyBasedTesting\StateMachine\StateMachineWithInvalidAttributeTest::testInvariantHasAnAttributeThatCannotBeInstantiated
PHPUnit\Framework\PropertyBased\InvalidStateMachineException: State machine PHPUnit\TestFixture\PropertyBasedTesting\StateMachine\MachineWithInvalidAttributeOnInvariant cannot be run: invalid attribute PHPUnit\Framework\Attributes\StateMachineInvariant for method PHPUnit\TestFixture\PropertyBasedTesting\StateMachine\MachineWithInvalidAttributeOnInvariant::invariant() in %sMachineWithInvalidAttributeOnInvariant.php:%d
Unknown named parameter $atTheEnd

%sStateMachineWithInvalidAttributeTest.php:%d

2) PHPUnit\TestFixture\PropertyBasedTesting\StateMachine\StateMachineWithInvalidAttributeTest::testParameterOfRuleHasAnAttributeThatCannotBeInstantiated
PHPUnit\Framework\PropertyBased\InvalidStateMachineException: State machine PHPUnit\TestFixture\PropertyBasedTesting\StateMachine\MachineWithInvalidAttributeOnParameter cannot be run: invalid attribute PHPUnit\Framework\Attributes\FromPool for parameter $value of method PHPUnit\TestFixture\PropertyBasedTesting\StateMachine\MachineWithInvalidAttributeOnParameter::consume() in %sMachineWithInvalidAttributeOnParameter.php:%d
Unknown named parameter $consumed

%sStateMachineWithInvalidAttributeTest.php:%d

ERRORS!
Tests: 2, Assertions: 0, Errors: 2.
