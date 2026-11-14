--TEST--
An example of a property for which draw() cannot generate a value is rejected, and the reason is stated when the property is considered risky because too many examples were rejected
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/DrawnValuesRejectedTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

R                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 risky test:

1) PHPUnit\TestFixture\PropertyBasedTesting\DrawnValuesRejectedTest::testDrawsValuesThatCannotBeGenerated
This property was checked for only 0 of 2 examples, 20 examples were rejected because no value that satisfies a filter was generated; generate the values that the property needs instead of rejecting the others

%sDrawnValuesRejectedTest.php:%d

OK, but there were issues!
Tests: 1, Assertions: 0, Risky: 1.
