--TEST--
The source files the process of a test that runs in a process of its own loaded are recorded for that test, whether a line of them was executed or not, but not for a test that shares its process with other tests
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/process-of-its-own/phpunit.xml';

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/_files/print-recorded-test-impact-data.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv'], false);

print PHP_EOL . 'Recorded:' . PHP_EOL;

print_recorded_test_impact_data(__DIR__ . '/_files/process-of-its-own/.phpunit.cache/test-impact-data');
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/process-of-its-own/.phpunit.cache');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s with DriverThatReportsWhatWasLoadedWhileItRan 1.0.0
Configuration: %s

Time: %s, Memory: %s

OK (2 tests, 2 assertions)

Recorded:
PHPUnit\TestFixture\TestImpactData\ProcessOfItsOwn\InheritsTheGateInAnotherProcessTest::testDeniesPermission => InheritsTheGateInAnotherProcessTest.php, Page.php, ReportPage.php
PHPUnit\TestFixture\TestImpactData\ProcessOfItsOwn\InheritsTheGateTest::testDeniesPermission => InheritsTheGateTest.php, Page.php
