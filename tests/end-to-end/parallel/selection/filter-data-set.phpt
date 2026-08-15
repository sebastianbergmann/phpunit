--TEST--
phpunit --filter selects a single data set of a data provider method
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'testFirstWithDataProvider#two';
$_SERVER['argv'][] = __DIR__ . '/_files/';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

First Selection (PHPUnit\TestFixture\ParallelSelection\FirstSelection)
 ✔ First with data provider with data set "two"

OK (1 test, 1 assertion)
