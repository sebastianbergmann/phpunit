--TEST--
phpunit --parallel=2 --exclude-group excludes the same tests as a sequential run
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--exclude-group';
$_SERVER['argv'][] = 'beta';
$_SERVER['argv'][] = __DIR__ . '/_files/';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers

...                                                                 3 / 3 (100%)

Time: %s, Memory: %s

First Selection (PHPUnit\TestFixture\ParallelSelection\FirstSelection)
 ✔ First plain

Second Selection (PHPUnit\TestFixture\ParallelSelection\SecondSelection)
 ✔ Second with data provider with data set "four"
 ✔ Second with data provider with data set "five"

OK (3 tests, 3 assertions)
