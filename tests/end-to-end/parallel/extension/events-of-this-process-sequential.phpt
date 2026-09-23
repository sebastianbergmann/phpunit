--TEST--
phpunit lets a subscriber that is registered for the events of this process only count every test that ran in this process, and not count a test that ran in a separate process
--FILE--
<?php declare(strict_types=1);
$log = tempnam(sys_get_temp_dir(), 'phpunit_counted_');

putenv('PHPUNIT_TEST_COUNTED_LOG=' . $log);

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/events-of-this-process/phpunit.xml';

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

....                                                                4 / 4 (100%)

Time: %s, Memory: %s

OK (4 tests, 4 assertions)
PHPUnit\TestFixture\ParallelWorkerExtension\EventsOfThisProcess\MainTest::testOne counted in main process
PHPUnit\TestFixture\ParallelWorkerExtension\EventsOfThisProcess\WorkerOneTest::testOne counted in main process
PHPUnit\TestFixture\ParallelWorkerExtension\EventsOfThisProcess\WorkerTwoTest::testOne counted in main process
