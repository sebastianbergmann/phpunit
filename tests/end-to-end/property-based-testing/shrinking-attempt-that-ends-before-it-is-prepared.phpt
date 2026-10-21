--TEST--
A shrinking attempt that ends before it is prepared does not falsify the property in another way
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/ShrinkingAttemptThatEndsBeforeItIsPreparedTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\PropertyBasedTesting\ShrinkingAttemptThatEndsBeforeItIsPreparedTest::testIsFalsifiedByEveryExample
Failed asserting that 0 is greater than 100.

Property falsified after 1 example with this counterexample:

    $value = 0;

%sShrinkingAttemptThatEndsBeforeItIsPreparedTest.php:%d

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
