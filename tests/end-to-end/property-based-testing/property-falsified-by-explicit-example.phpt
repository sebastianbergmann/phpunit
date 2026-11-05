--TEST--
A property that is falsified by an explicit example is reported with the failure of that example, which is not shrunk, and with its name and arguments
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/PropertyFalsifiedByExplicitExampleTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

FF                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There were 2 failures:

1) PHPUnit\TestFixture\PropertyBasedTesting\PropertyFalsifiedByExplicitExampleTest::testAbsoluteValueIsTheValue
Failed asserting that 1 is identical to -1.

Property falsified by explicit example "minus one":

    $value = -1;

%sPropertyFalsifiedByExplicitExampleTest.php:%d

2) PHPUnit\TestFixture\PropertyBasedTesting\PropertyFalsifiedByExplicitExampleTest::testValueIsNotNegative
Failed asserting that -1 is equal to 0 or is greater than 0.

Property falsified by explicit example #1:

    $value = -1;

%sPropertyFalsifiedByExplicitExampleTest.php:%d

FAILURES!
Tests: 2, Assertions: %d, Failures: 2.
