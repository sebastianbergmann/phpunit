--TEST--
phpunit --parallel=3 runs tests that use what another test class file declares, or loads, as a sequential run does, because every worker loads the test class files that the main process loaded
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=3';
$_SERVER['argv'][] = __DIR__ . '/_files/tests/';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      3 workers

...                                                                 3 / 3 (100%)

Time: %s, Memory: %s

OK (3 tests, 6 assertions)
