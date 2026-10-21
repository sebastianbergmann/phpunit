--TEST--
Values that the body of a property draws with draw() can depend on the arguments of the property and on each other, and they are shrunk together with the arguments
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/DrawTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

FF.                                                                 3 / 3 (100%)

Time: %s, Memory: %s

There were 2 failures:

1) PHPUnit\TestFixture\PropertyBasedTesting\DrawTest::testDrawsValuesThatDependOnTheArgumentAndOnEachOther
Failed asserting that 5 is not identical to 5.

Property falsified after %s, counterexample shrunk in %s:

    $size = 1;

    Draw 1 (list): [5]
    Draw 2 (index): 0

%sDrawTest.php:%d

2) PHPUnit\TestFixture\PropertyBasedTesting\DrawTest::testDrawsValuesWithoutParameters
Failed asserting that 100 is less than 100.

Property falsified after %s, counterexample shrunk in %s:

    Draw 1: 0
    Draw 2: 100

%sDrawTest.php:%d

FAILURES!
Tests: 3, Assertions: %d, Failures: 2.
