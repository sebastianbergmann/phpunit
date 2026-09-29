--TEST--
What is executed outside of the tests is recorded for the tests it can affect: what a data provider executes for the tests that are made from the data it provides, what is executed while the tests of a test class are run for the tests of that class, and everything else for every test
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/outside-of-tests/phpunit.xml';

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/_files/print-recorded-test-impact-data.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

$dataFile = __DIR__ . '/_files/outside-of-tests/.phpunit.cache/test-impact-data';

print PHP_EOL . 'Recorded:' . PHP_EOL;

print_recorded_test_impact_data($dataFile);

$data  = json_decode(file_get_contents($dataFile), true);
$files = [];

foreach ($data['executedOutsideOfTests'] as $version) {
    $files[] = basename($data['files'][$data['versions'][$version][0]]);
}

sort($files);

print PHP_EOL . 'Executed outside of any test: ' . implode(', ', $files) . PHP_EOL;
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/outside-of-tests/.phpunit.cache');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s with DriverThatReportsWhatWasLoadedWhileItRan 1.0.0
Configuration: %s

Time: %s, Memory: %s

OK (7 tests, 7 assertions)

Recorded:
PHPUnit\TestFixture\TestImpactData\OutsideOfTests\BeforeClassTest::testReadsWhatWasSetUpBeforeTheFirstTest => BeforeClassTest.php, Settings.php
PHPUnit\TestFixture\TestImpactData\OutsideOfTests\CallsRatesTest::testExecutesWhatItDependsOn => CallsRatesTest.php, Rates.php
PHPUnit\TestFixture\TestImpactData\OutsideOfTests\IsolatedProvidedTest::testUsesWhatTheDataProviderProvidesInAnotherProcess#0 => IsolatedCases.php, IsolatedProvidedTest.php
PHPUnit\TestFixture\TestImpactData\OutsideOfTests\ProvidedTest::testUsesWhatTheDataProviderProvides#0 => Cases.php, ProvidedTest.php
PHPUnit\TestFixture\TestImpactData\OutsideOfTests\ProvidedTest::testUsesWhatTheDataProviderProvides#1 => Cases.php, ProvidedTest.php
PHPUnit\TestFixture\TestImpactData\OutsideOfTests\ReadsBootStateTest::testReadsWhatTheBootstrapScriptSetUp => ReadsBootStateTest.php
PHPUnit\TestFixture\TestImpactData\OutsideOfTests\RequiresTopLevelTest::testReadsWhatARequiredFileSetUp => RequiresTopLevelTest.php

Executed outside of any test: Framework.php, TopLevel.php
