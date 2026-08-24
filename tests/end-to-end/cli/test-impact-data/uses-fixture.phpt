--TEST--
A fixture a data provider declares that it uses is recorded for every test that provider provides data for
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--do-not-fail-on-phpunit-warning';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/phpunit-uses-fixture.xml';

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/_files/print-recorded-test-impact-data.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv'], false);

print PHP_EOL . 'Recorded:' . PHP_EOL;

print_recorded_test_impact_data(__DIR__ . '/_files/.phpunit.cache.uses-fixture/test-impact-data');
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/.phpunit.cache.uses-fixture');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %s

Time: %s, Memory: %s

1 test triggered 1 PHPUnit warning:

1) PHPUnit\TestFixture\TestImpactData\SumTest::testAdds#0 with data (1, 2, 3)
Fixture ../fixtures/does-not-exist.csv does not exist, the attribute is ignored

%s

OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Warnings: 1.

Recorded:
PHPUnit\TestFixture\TestImpactData\SumTest::testAdds#0 => Calculator.php, sums.csv
