--TEST--
phpunit --parallel=2 lets a subscriber that is registered in the main process and in the worker processes count every test once, in the process that ran it, by comparing the process ID in the telemetry information of an event with its own
--FILE--
<?php declare(strict_types=1);
$log = tempnam(sys_get_temp_dir(), 'phpunit_counted_');

putenv('PHPUNIT_TEST_COUNTED_LOG=' . $log);

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/process-of-origin/phpunit.xml';
$_SERVER['argv'][] = '--parallel=2';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

$lines = file($log, FILE_IGNORE_NEW_LINES);

unlink($log);

sort($lines);

print implode(PHP_EOL, $lines) . PHP_EOL;
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %sphpunit.xml
Parallel:      2 workers

...                                                                 3 / 3 (100%)

Time: %s, Memory: %s

OK (3 tests, 3 assertions)
PHPUnit\TestFixture\ParallelWorkerExtension\ProcessOfOrigin\MainTest::testOne counted in main process
PHPUnit\TestFixture\ParallelWorkerExtension\ProcessOfOrigin\WorkerOneTest::testOne counted in worker process
PHPUnit\TestFixture\ParallelWorkerExtension\ProcessOfOrigin\WorkerTwoTest::testOne counted in worker process
