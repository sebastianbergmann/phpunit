--TEST--
Which tests are recorded as having executed a source file can be asked without running tests
--FILE--
<?php declare(strict_types=1);
$argumentsForTestRun = [
    $_SERVER['argv'][0],
    '--do-not-record-test-run-history',
    '--no-progress',
    '--colors=never',
    '--configuration',
    __DIR__ . '/_files/phpunit.xml',
];

$argumentsForQuery = [
    $_SERVER['argv'][0],
    '--colors=never',
    '--configuration',
    __DIR__ . '/_files/phpunit.xml',
    '--list-tests-that-executed',
    __DIR__ . '/_files/src/Rounder.php',
];

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($argumentsForTestRun, false);

print PHP_EOL;

(new PHPUnit\TextUI\Application)->run($argumentsForQuery, false);
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

PHPUnit %s by Sebastian Bergmann and contributors.

Tests that executed %sRounder.php as it is now:
 - PHPUnit\TestFixture\TestImpactData\CalculatorTest::testAdds
 - PHPUnit\TestFixture\TestImpactData\IsolatedCalculatorTest::testAddsInAnotherProcess
 - PHPUnit\TestFixture\TestImpactData\NothingCoveredTest::testAdds
