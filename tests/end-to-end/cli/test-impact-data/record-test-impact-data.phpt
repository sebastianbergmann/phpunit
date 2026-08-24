--TEST--
Which source files each test executed is recorded when this is configured
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/phpunit.xml';

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/_files/print-recorded-test-impact-data.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print PHP_EOL . 'Recorded:' . PHP_EOL;

print_recorded_test_impact_data(__DIR__ . '/_files/.phpunit.cache/test-impact-data');
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/.phpunit.cache');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s with DriverWithFakeData 1.0.0
Configuration: %s

Time: %s, Memory: %s

OK, but some tests were skipped!
Tests: 4, Assertions: 3, Skipped: 1.

Recorded:
PHPUnit\TestFixture\TestImpactData\CalculatorTest::testAdds => Calculator.php, CalculatorTest.php, Rounder.php
PHPUnit\TestFixture\TestImpactData\IsolatedCalculatorTest::testAddsInAnotherProcess => Calculator.php, IsolatedCalculatorTest.php, Rounder.php
PHPUnit\TestFixture\TestImpactData\NothingCoveredTest::testAdds => Calculator.php, NothingCoveredTest.php, Rounder.php
