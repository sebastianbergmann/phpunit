--TEST--
What every example of a property that covers nothing executed is recorded for the property, whether the example performed assertions or not
--CONFLICTS--
test-impact-data
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/properties/phpunit-property-that-covers-nothing.xml';

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/_files/print-recorded-test-impact-data.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv'], false);

print PHP_EOL . 'Recorded:' . PHP_EOL;

print_recorded_test_impact_data(__DIR__ . '/_files/properties/.phpunit.cache.property-that-covers-nothing/test-impact-data');
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/properties/.phpunit.cache.property-that-covers-nothing');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s with DriverThatReportsWhatWasCalledWhileItRan 1.0.0
Configuration: %s
Random Seed:   1234

Time: %s, Memory: %s

OK (2 tests, 4 assertions)

Recorded:
PHPUnit\TestFixture\TestImpactData\Properties\PropertyThatCoversNothingTest::testEveryExamplePerformsAssertions => Doubler.php, Negator.php, PropertyThatCoversNothingTest.php, Tripler.php
PHPUnit\TestFixture\TestImpactData\Properties\PropertyThatCoversNothingTest::testSomeExamplesDoNotPerformAssertions => Doubler.php, Negator.php, PropertyThatCoversNothingTest.php, Tripler.php
