--TEST--
phpunit --parallel=2 replaces a worker after the number of test classes that numberOfTestClassesBeforeWorkerRecycling configures
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/phpunit.xml';
$_SERVER['argv'][] = '--parallel=2';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %sphpunit.xml
Parallel:      2 workers

....                                                                4 / 4 (100%)

Time: %s, Memory: %s

OK (4 tests, 4 assertions)
