--TEST--
The hook methods of a test class that are called before its first test and after its last test are called for a property when process isolation is requested for all tests, as a property is run in the process of the test runner
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--process-isolation';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/ProcessIsolationClassHooksTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 3 assertions)
