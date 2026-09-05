--TEST--
What a test that covers nothing executed is recorded for test impact analysis without reaching the code coverage report
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/phpunit-code-coverage-report-with-a-test-that-covers-nothing.xml';

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/_files/print-recorded-test-impact-data.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv'], false);

print PHP_EOL . 'Recorded:' . PHP_EOL;

print_recorded_test_impact_data(__DIR__ . '/_files/.phpunit.cache.with-a-test-that-covers-nothing/test-impact-data');
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/.phpunit.cache.with-a-test-that-covers-nothing');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s with DriverThatReportsWhatHasBeenLoaded 1.0.0
Configuration: %s

Time: %s, Memory: %s

OK (2 tests, 2 assertions)


Code Coverage Report:%w
  %s

 Summary:%w
  Classes: 100.00%% (1/1)
  Methods: 100.00%% (1/1)
  Lines:   100.00%% (1/1)

PHPUnit\TestFixture\TestImpactData\Doubler
  Methods: 100.00%% ( 1/ 1)   Lines: 100.00%% (  1/  1)

Recorded:
PHPUnit\TestFixture\TestImpactData\DoublerTest::testDoubles => Doubler.php, DoublerTest.php
PHPUnit\TestFixture\TestImpactData\TriplerTest::testTriples => Doubler.php, Tripler.php, TriplerTest.php
