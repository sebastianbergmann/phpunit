--TEST--
--property-examples-multiplier with a value that is not a positive integer triggers a warning and is ignored
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--property-examples';
$_SERVER['argv'][] = '5';
$_SERVER['argv'][] = '--property-examples-multiplier';
$_SERVER['argv'][] = '0';
$_SERVER['argv'][] = '--display-property-statistics';
$_SERVER['argv'][] = __DIR__ . '/_files/ExamplesMultiplierTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

..                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Option "--property-examples-multiplier 0" ignored because "0" is not a positive integer

--

Statistics of 2 properties:

1) PHPUnit\TestFixture\PropertyBasedTesting\ExamplesMultiplierTest::testWithConfiguredNumberOfExamples
5 examples checked, 0 rejected

2) PHPUnit\TestFixture\PropertyBasedTesting\ExamplesMultiplierTest::testWithSpecifiedNumberOfExamples
4 examples checked, 0 rejected

OK, but there were issues!
Tests: 2, Assertions: 9, PHPUnit Warnings: 1.
