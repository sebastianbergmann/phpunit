--TEST--
The source files a process that runs a single test loaded are recorded for that test, whether a line of them was executed or not
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/process-of-its-own/phpunit.xml';
$_SERVER['argv'][] = '--cache-directory';
$_SERVER['argv'][] = __DIR__ . '/_files/process-of-its-own/.phpunit.cache.single-test';
$_SERVER['argv'][] = __DIR__ . '/_files/process-of-its-own/tests/InheritsTheGateTest.php';

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/_files/print-recorded-test-impact-data.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv'], false);

print PHP_EOL . 'Recorded:' . PHP_EOL;

print_recorded_test_impact_data(__DIR__ . '/_files/process-of-its-own/.phpunit.cache.single-test/test-impact-data');
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/process-of-its-own/.phpunit.cache.single-test');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s with DriverThatReportsWhatWasLoadedWhileItRan 1.0.0
Configuration: %s

Time: %s, Memory: %s

OK (1 test, 1 assertion)

Recorded:
PHPUnit\TestFixture\TestImpactData\ProcessOfItsOwn\InheritsTheGateTest::testDeniesPermission => InheritsTheGateTest.php, Page.php, ReportPage.php
