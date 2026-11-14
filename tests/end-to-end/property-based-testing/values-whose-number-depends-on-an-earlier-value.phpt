--TEST--
The minimal counterexample of a property that draws values whose number depends on an earlier value has fewer of these values
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/ValuesWhoseNumberDependsOnAnEarlierValueTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

FF                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There were 2 failures:

1) PHPUnit\TestFixture\PropertyBasedTesting\ValuesWhoseNumberDependsOnAnEarlierValueTest::testElementIsFoundAtItsIndex
Failed asserting that 0 is identical to 1.

Property falsified after %s, counterexample shrunk in %s:

    $size = 2;

    Draw 1 (list): [0, 0]
    Draw 2 (index): 1

%sValuesWhoseNumberDependsOnAnEarlierValueTest.php:%d

2) PHPUnit\TestFixture\PropertyBasedTesting\ValuesWhoseNumberDependsOnAnEarlierValueTest::testSumOfSummandsIsSmall
Failed asserting that 15 is less than 15.

Property falsified after %s, counterexample shrunk in %s:

    Draw 1 (count): 2
    Draw 2 (summand): 5
    Draw 3 (summand): 10

%sValuesWhoseNumberDependsOnAnEarlierValueTest.php:%d

FAILURES!
Tests: 2, Assertions: %d, Failures: 2.
