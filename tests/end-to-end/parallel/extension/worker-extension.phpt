--TEST--
phpunit --parallel=2 bootstraps an extension that implements ParallelWorkerExtension in the worker, where its subscribers receive the events of the tests the worker runs, live, once per test
--FILE--
<?php declare(strict_types=1);
$log = tempnam(sys_get_temp_dir(), 'phpunit_finished_');

putenv('PHPUNIT_TEST_FINISHED_LOG=' . $log);

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/worker-extension/phpunit.xml';
$_SERVER['argv'][] = '--parallel=2';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($log);

unlink($log);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %sphpunit.xml
Parallel:      2 workers

..                                                                  2 / 2 (100%)

Time: %s, Memory: %s

OK (2 tests, 3 assertions)
PHPUnit\TestFixture\ParallelWorkerExtension\Worker\SeesTheWorkerSubscriberTest::testTheSubscriberRegisteredInTheWorkerSawThisTestBeingPrepared finished in worker %d
PHPUnit\TestFixture\ParallelWorkerExtension\Worker\SeesTheWorkerSubscriberTest::testTheSubscriberPersistsAcrossTheTestsOfAUnit finished in worker %d
