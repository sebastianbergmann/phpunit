--TEST--
What was recorded is discarded when what it rests on is not what it was
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/_files/print-recorded-test-impact-data.php';

$dataFile = __DIR__ . '/_files/.phpunit.cache/test-impact-data';

function run(array $additionalArguments = []): void
{
    $process = proc_open(
        [
            PHP_BINARY,
            __DIR__ . '/../../../../phpunit',
            '--do-not-record-test-run-history',
            '--no-progress',
            '--colors=never',
            '--configuration',
            __DIR__ . '/_files/phpunit.xml',
            ...$additionalArguments,
        ],
        [1 => ['pipe', 'w']],
        $pipes,
    );

    stream_get_contents($pipes[1]);

    fclose($pipes[1]);
    proc_close($process);
}

run();

print 'Recorded:' . PHP_EOL;

print_recorded_test_impact_data($dataFile);

/*
 * --coverage-filter makes another directory first-party code without the
 * configuration file changing: what was recorded was recorded for code that is
 * not what the code is now.
 */
run(['--coverage-filter', __DIR__ . '/_files/fixtures', '--filter', 'CalculatorTest']);

print PHP_EOL . 'Recorded after more code became first-party code:' . PHP_EOL;

print_recorded_test_impact_data($dataFile);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/.phpunit.cache');
--EXPECT--
Recorded:
PHPUnit\TestFixture\TestImpactData\CalculatorTest::testAdds => Calculator.php, CalculatorTest.php, Rounder.php
PHPUnit\TestFixture\TestImpactData\IsolatedCalculatorTest::testAddsInAnotherProcess => Calculator.php, IsolatedCalculatorTest.php, Rounder.php
PHPUnit\TestFixture\TestImpactData\NothingCoveredTest::testAdds => Calculator.php, NothingCoveredTest.php, Rounder.php

Recorded after more code became first-party code:
PHPUnit\TestFixture\TestImpactData\CalculatorTest::testAdds => Calculator.php, CalculatorTest.php, Rounder.php
PHPUnit\TestFixture\TestImpactData\IsolatedCalculatorTest::testAddsInAnotherProcess => Calculator.php, IsolatedCalculatorTest.php, Rounder.php
