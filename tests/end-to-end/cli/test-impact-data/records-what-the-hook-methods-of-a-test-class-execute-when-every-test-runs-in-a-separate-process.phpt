--TEST--
What the before-first-test methods and after-last-test methods of a test class execute is recorded for its tests when every one of them runs in a separate process, where these methods are only called in the child processes
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/hook-methods-in-separate-processes/phpunit.xml';

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/_files/print-recorded-test-impact-data.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print PHP_EOL . 'Recorded:' . PHP_EOL;

print_recorded_test_impact_data(__DIR__ . '/_files/hook-methods-in-separate-processes/.phpunit.cache/test-impact-data');
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/hook-methods-in-separate-processes/.phpunit.cache');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s with DriverThatReportsWhatWasLoadedWhileItRan 1.0.0
Configuration: %s

Time: %s, Memory: %s

OK (2 tests, 2 assertions)

Recorded:
PHPUnit\TestFixture\TestImpactData\HookMethodsInSeparateProcesses\EveryTestInASeparateProcessTest::testOne => EveryTestInASeparateProcessTest.php, Settings.php, TemporaryFiles.php
PHPUnit\TestFixture\TestImpactData\HookMethodsInSeparateProcesses\EveryTestInASeparateProcessTest::testTwo => EveryTestInASeparateProcessTest.php, Settings.php, TemporaryFiles.php
