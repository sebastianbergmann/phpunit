--TEST--
phpunit --parallel=2 --display-property-statistics displays the statistics of a property that was checked in a worker
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--display-property-statistics';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = __DIR__ . '/_files/ClassifiedExamplesTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers
Random Seed:   1234

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

Statistics of 1 property:

1) PHPUnit\TestFixture\ParallelProperty\ClassifiedExamplesTest::testClassifiesItsExamples
9 examples and 1 explicit example checked, 1 rejected

     55.6% even
     44.4% odd
     11.1% zero

OK (1 test, 20 assertions)
