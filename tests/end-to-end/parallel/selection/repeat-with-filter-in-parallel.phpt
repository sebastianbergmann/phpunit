--TEST--
phpunit --parallel=2 --repeat 2 --filter repeats the same data sets as a sequential run
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'testFirstWithDataProvider';
$_SERVER['argv'][] = __DIR__ . '/_files/';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers

......                                                              6 / 6 (100%)

Time: %s, Memory: %s

First Selection (PHPUnit\TestFixture\ParallelSelection\FirstSelection)
 ✔ First with data provider with data set "one" (repetition 1 of 2)
 ✔ First with data provider with data set "one" (repetition 2 of 2)
 ✔ First with data provider with data set "two" (repetition 1 of 2)
 ✔ First with data provider with data set "two" (repetition 2 of 2)
 ✔ First with data provider with data set "three" (repetition 1 of 2)
 ✔ First with data provider with data set "three" (repetition 2 of 2)

OK (6 tests, 6 assertions)
