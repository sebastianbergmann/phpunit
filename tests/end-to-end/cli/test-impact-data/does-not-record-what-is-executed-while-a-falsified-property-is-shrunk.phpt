--TEST--
What is executed while a falsified property is shrunk, and while its minimal counterexample is replayed, is not recorded
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
$_SERVER['argv'][] = __DIR__ . '/_files/properties/phpunit-falsified-property.xml';

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/_files/print-recorded-test-impact-data.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv'], false);

print PHP_EOL . 'Recorded:' . PHP_EOL;

print_recorded_test_impact_data(__DIR__ . '/_files/properties/.phpunit.cache.falsified-property/test-impact-data');
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/properties/.phpunit.cache.falsified-property');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s with DriverThatReportsWhatWasCalledWhileItRan 1.0.0
Configuration: %s
Random Seed:   1234

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\TestImpactData\Properties\FalsifiedPropertyTest::testIsFalsifiedByAnExampleThatIsShrunk
Failed asserting that 200 is less than 200.

Property falsified after %s, counterexample shrunk in %d steps:

    $value = 100;

%sFalsifiedPropertyTest.php:%d

FAILURES!
Tests: 2, Assertions: %d, Failures: 1.

Recorded:
PHPUnit\TestFixture\TestImpactData\Properties\FalsifiedPropertyTest::testIsFalsifiedByAnExampleThatIsShrunk => Doubler.php, FalsifiedPropertyTest.php
PHPUnit\TestFixture\TestImpactData\Properties\FalsifiedPropertyTest::testNegates => FalsifiedPropertyTest.php, Negator.php
