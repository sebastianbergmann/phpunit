--TEST--
--display-property-statistics displays how many examples of each property were checked and rejected, and how many of them were classified with each label
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--display-property-statistics';
$_SERVER['argv'][] = __DIR__ . '/_files/ClassifiedExamplesTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

Statistics of 1 property:

1) PHPUnit\TestFixture\PropertyBasedTesting\ClassifiedExamplesTest::testClassifiesItsExamples
9 examples checked, 1 rejected

     55.6% even
     44.4% odd
     11.1% zero

OK (1 test, %d assertions)
