--TEST--
No further examples of a property are generated once every combination of the values that it draws with draw() has been drawn
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--display-property-statistics';
$_SERVER['argv'][] = __DIR__ . '/_files/DrawnValuesFromAFiniteSetTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

Statistics of 1 property:

1) PHPUnit\TestFixture\PropertyBasedTesting\DrawnValuesFromAFiniteSetTest::testDrawsTwoBooleans
4 examples checked, 0 rejected

OK (1 test, 8 assertions)
