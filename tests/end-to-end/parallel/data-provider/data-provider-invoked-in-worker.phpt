--TEST--
phpunit --parallel=2 runs data-provided tests in a worker whatever their data is, because the worker invokes the data provider again instead of receiving the data from the main process
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--display-phpunit-notices';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = __DIR__ . '/_files/faithful/';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers

....                                                                4 / 4 (100%)

Time: %s, Memory: %s

Anonymous Class Data (PHPUnit\TestFixture\ParallelDataProvider\AnonymousClassData)
 ✔ Receives an instance of an anonymous class with data set #0

Closure Data (PHPUnit\TestFixture\ParallelDataProvider\ClosureData)
 ✔ Receives a closure with data set #0

Lossy Serialization Data (PHPUnit\TestFixture\ParallelDataProvider\LossySerializationData)
 ✔ Receives the whole object with data set "admin"

Resource Data (PHPUnit\TestFixture\ParallelDataProvider\ResourceData)
 ✔ Receives an open resource with data set #0

OK (4 tests, 10 assertions)
