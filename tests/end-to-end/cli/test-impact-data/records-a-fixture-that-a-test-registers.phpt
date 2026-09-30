--TEST--
A fixture a test registers while it runs is recorded for that test
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--do-not-fail-on-phpunit-warning';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/phpunit-registered-fixtures.xml';

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/_files/print-recorded-test-impact-data.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv'], false);

print PHP_EOL . 'Recorded:' . PHP_EOL;

print_recorded_test_impact_data(__DIR__ . '/_files/.phpunit.cache.registered-fixtures/test-impact-data');
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/.phpunit.cache.registered-fixtures');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s with DriverWithFakeData 1.0.0
Configuration: %s

Time: %s, Memory: %s

1 test triggered 2 PHPUnit warnings:

1) PHPUnit\TestFixture\TestImpactData\RegistersFixturesTest::testRegistersPathsThatDoNotExist
* Fixture does-not-exist.csv does not exist, it is ignored

* Fixture  does not exist, it is ignored

%s

OK, but there were issues!
Tests: 7, Assertions: 6, PHPUnit Warnings: 1, Skipped: 1.

Recorded:
PHPUnit\TestFixture\TestImpactData\RegistersAFixtureAfterTheTestTest::testAdds => Calculator.php, RegistersAFixtureAfterTheTestTest.php, Rounder.php, TestCaseThatRegistersAFixture.php, fixture-directory
PHPUnit\TestFixture\TestImpactData\RegistersAFixtureInAnotherProcessTest::testRegistersAFixture => Calculator.php, RegistersAFixtureInAnotherProcessTest.php, Rounder.php, sums.csv
PHPUnit\TestFixture\TestImpactData\RegistersFixturesTest::testRegistersAFileAndADirectory => Calculator.php, RegistersFixturesTest.php, Rounder.php, fixture-directory, sums.csv
PHPUnit\TestFixture\TestImpactData\RegistersFixturesTest::testRegistersAFileTheTestAlsoExecuted => Calculator.php, RegistersFixturesTest.php, Rounder.php
PHPUnit\TestFixture\TestImpactData\RegistersFixturesTest::testRegistersAPathThatIsRelativeToTheWorkingDirectory => Calculator.php, RegistersFixturesTest.php, Rounder.php, sums.csv
PHPUnit\TestFixture\TestImpactData\RegistersFixturesTest::testRegistersPathsThatDoNotExist => Calculator.php, RegistersFixturesTest.php, Rounder.php, sums.csv
