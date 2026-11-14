--TEST--
A closing tag in the value that a step of the minimal counterexample of a property that runs a state machine returned is written as ?\> in the comment for the value, so that it does not end the comment and the PHP code of the steps
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/StateMachineWithRuleThatReturnsAClosingTagTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\PropertyBasedTesting\StateMachineWithRuleThatReturnsAClosingTagTest::testClosingTagIsNotReturned
Failed asserting that true is false.

Property falsified after 1 example with this counterexample:

    $machine->closingTag(); // '?\> <?php echo "pwned";'
    $machine->closingTagWasNotReturned(); // invariant failed

%sStateMachineWithRuleThatReturnsAClosingTagTest.php:%d
%sStateMachineWithRuleThatReturnsAClosingTagTest.php:%d

FAILURES!
Tests: 1, Assertions: %d, Failures: 1.
