--TEST--
The examplesMultiplier setting of the XML configuration file multiplies the number of examples of every property, whether it is specified for the property or not
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/examples-multiplier/phpunit.xml';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/ExamplesMultiplierTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s
Random Seed:   1234

..                                                                  2 / 2 (100%)

Time: %s, Memory: %s

Statistics of 2 properties:

1) PHPUnit\TestFixture\PropertyBasedTesting\ExamplesMultiplierTest::testWithConfiguredNumberOfExamples
15 examples checked, 0 rejected

2) PHPUnit\TestFixture\PropertyBasedTesting\ExamplesMultiplierTest::testWithSpecifiedNumberOfExamples
12 examples checked, 0 rejected

OK (2 tests, 27 assertions)
