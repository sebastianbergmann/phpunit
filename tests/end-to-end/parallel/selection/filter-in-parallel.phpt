--TEST--
phpunit --parallel=2 --filter selects the same tests as a sequential run
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'testSecondWithDataProvider';
$_SERVER['argv'][] = __DIR__ . '/_files/';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

..                                                                  2 / 2 (100%)

Time: %s, Memory: %s

Second Selection (PHPUnit\TestFixture\ParallelSelection\SecondSelection)
 ✔ Second with data provider with data set "four"
 ✔ Second with data provider with data set "five"

OK (2 tests, 2 assertions)
