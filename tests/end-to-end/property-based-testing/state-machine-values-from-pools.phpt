--TEST--
The steps of the minimal counterexample of a property that runs a state machine take the values from a pool that they took before the steps between them were deleted
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '18';
$_SERVER['argv'][] = __DIR__ . '/_files/state-machine/HashTableTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   18

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\PropertyBasedTesting\StateMachine\HashTableTest::testValuesAreFoundUntilTheirKeysAreDeleted
Failed asserting that null is identical to 0.

Property falsified after %d examples, counterexample shrunk in %d steps:

    $keys1 = $machine->put(key: 0, value: 0);
    $keys2 = $machine->put(key: 8, value: 0);
    $machine->delete(key: $keys1);
    $machine->get(key: $keys2);

%sHashTableMachine.php:%d
%sHashTableTest.php:%d

FAILURES!
Tests: 1, Assertions: %d, Failures: 1.
