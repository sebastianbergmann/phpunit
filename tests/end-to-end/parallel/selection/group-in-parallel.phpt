--TEST--
phpunit --parallel=2 --group selects the same tests as a sequential run
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--group';
$_SERVER['argv'][] = 'beta';
$_SERVER['argv'][] = __DIR__ . '/_files/';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

....                                                                4 / 4 (100%)

Time: %s, Memory: %s

First Selection (PHPUnit\TestFixture\ParallelSelection\FirstSelection)
 ✔ First with data provider with data set "one"
 ✔ First with data provider with data set "two"
 ✔ First with data provider with data set "three"

Second Selection (PHPUnit\TestFixture\ParallelSelection\SecondSelection)
 ✔ Second plain

OK (4 tests, 4 assertions)
