--TEST--
The minimal counterexample is reported for properties whose minimal counterexample is known
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/KnownMinimalCounterexamplesTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

FFF                                                                 3 / 3 (100%)

Time: %s, Memory: %s

There were 3 failures:

1) PHPUnit\TestFixture\PropertyBasedTesting\KnownMinimalCounterexamplesTest::testAllocationPreservesTheAmount
Failed asserting that 0 is identical to 1.

Property falsified after %s, counterexample shrunk in %s:

    $money = new Money(amount: 1, currency: Currency::EUR);
    $parts = 2;

%sKnownMinimalCounterexamplesTest.php:%d

2) PHPUnit\TestFixture\PropertyBasedTesting\KnownMinimalCounterexamplesTest::testListHasNoDuplicates
Failed asserting that actual size 1 matches expected size 2.

Property falsified after %s, counterexample shrunk in %s:

    $values = [0, 0];

%sKnownMinimalCounterexamplesTest.php:%d

3) PHPUnit\TestFixture\PropertyBasedTesting\KnownMinimalCounterexamplesTest::testSumIsAtMostTen
Failed asserting that 11 is equal to 10 or is less than 10.

Property falsified after %s, counterexample shrunk in %s:

    $a = 0;
    $b = 11;

%sKnownMinimalCounterexamplesTest.php:%d

FAILURES!
Tests: 3, Assertions: %d, Failures: 3.
