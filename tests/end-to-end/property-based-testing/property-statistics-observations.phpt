--TEST--
--display-property-statistics displays the statistics of the values that the examples of each property observed with each label
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--display-property-statistics';
$_SERVER['argv'][] = __DIR__ . '/_files/ObservedValuesTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

Statistics of 1 property:

1) PHPUnit\TestFixture\PropertyBasedTesting\ObservedValuesTest::testObservesValuesOfItsExamples
9 examples and 1 explicit example checked, 1 rejected

    value: count 9, min 0, median 4, mean 4, p90 8, max 8
    third: count 9, min 0, median 1.33333, mean 1.33333, p90 2.66667, max 2.66667

OK (1 test, %d assertions)
