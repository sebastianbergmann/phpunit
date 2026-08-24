--TEST--
Test impact data is derived from the code coverage targets the tests declare when this is configured
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/phpunit-from-coverage-targets.xml';

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/_files/print-recorded-test-impact-data.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv'], false);

print PHP_EOL . 'Recorded:' . PHP_EOL;

print_recorded_test_impact_data(__DIR__ . '/_files/.phpunit.cache.from-coverage-targets/test-impact-data');
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/.phpunit.cache.from-coverage-targets');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %s

Time: %s, Memory: %s

OK, but some tests were skipped!
Tests: 4, Assertions: 3, Skipped: 1.

Recorded:
PHPUnit\TestFixture\TestImpactData\CalculatorTest::testAdds => Calculator.php, CalculatorTest.php
PHPUnit\TestFixture\TestImpactData\IsolatedCalculatorTest::testAddsInAnotherProcess => Calculator.php, IsolatedCalculatorTest.php
PHPUnit\TestFixture\TestImpactData\SkippedTest::testIsSkipped => Rounder.php, SkippedTest.php
