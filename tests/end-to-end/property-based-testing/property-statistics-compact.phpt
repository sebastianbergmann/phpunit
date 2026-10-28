--TEST--
--display-property-statistics displays the statistics of the properties with the compact output
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--compact';
$_SERVER['argv'][] = '--display-property-statistics';
$_SERVER['argv'][] = __DIR__ . '/_files/ClassifiedExamplesTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:%s
Random Seed:   1234

OK (1 test, %d assertions)

--- STATISTICS: PHPUnit\TestFixture\PropertyBasedTesting\ClassifiedExamplesTest::testClassifiesItsExamples
9 examples and 1 explicit example checked, 1 rejected

     55.6% even
     44.4% odd
     11.1% zero
