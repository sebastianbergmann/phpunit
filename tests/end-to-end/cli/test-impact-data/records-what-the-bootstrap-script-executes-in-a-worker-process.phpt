--TEST--
What the bootstrap script only executes in a worker process of a parallel test run is recorded as executed outside of the tests
--CONFLICTS--
test-impact-data
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--parallel';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/parallel/phpunit.xml';

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/_files/print-recorded-test-impact-data.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

$dataFile = __DIR__ . '/_files/parallel/.phpunit.cache/test-impact-data';

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

delete_directory(__DIR__ . '/_files/parallel/.phpunit.cache');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s with DriverThatReportsWhatWasLoadedWhileItRan 1.0.0
Configuration: %s
Parallel:      2 workers

Time: %s, Memory: %s

OK (2 tests, 2 assertions)

Recorded:
PHPUnit\TestFixture\TestImpactData\Parallel\AdderTest::testAdds => Adder.php, AdderTest.php
PHPUnit\TestFixture\TestImpactData\Parallel\MultiplierTest::testMultiplies => Multiplier.php, MultiplierTest.php

Executed outside of any test: WorkerSetup.php
