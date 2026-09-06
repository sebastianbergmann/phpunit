--TEST--
The repetitions of a repeated test are recorded as the test they are repetitions of
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/_files/print-recorded-test-impact-data.php';

$cacheDirectory = __DIR__ . '/_files/.phpunit.cache.repeat';

function run(array $additionalArguments = []): void
{
    global $cacheDirectory;

    $process = proc_open(
        [
            PHP_BINARY,
            __DIR__ . '/../../../../phpunit',
            '--do-not-record-test-run-history',
            '--no-progress',
            '--colors=never',
            '--configuration',
            __DIR__ . '/_files/phpunit.xml',
            '--cache-directory',
            $cacheDirectory,
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

print_recorded_test_impact_data($cacheDirectory . '/test-impact-data');

run(['--repeat=2']);

print PHP_EOL . 'Recorded after every test was repeated:' . PHP_EOL;

print_recorded_test_impact_data($cacheDirectory . '/test-impact-data');
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/.phpunit.cache.repeat');
--EXPECT--
Recorded:
PHPUnit\TestFixture\TestImpactData\CalculatorTest::testAdds => Calculator.php, CalculatorTest.php, Rounder.php
PHPUnit\TestFixture\TestImpactData\IsolatedCalculatorTest::testAddsInAnotherProcess => Calculator.php, IsolatedCalculatorTest.php, Rounder.php
PHPUnit\TestFixture\TestImpactData\NothingCoveredTest::testAdds => Calculator.php, NothingCoveredTest.php, Rounder.php

Recorded after every test was repeated:
PHPUnit\TestFixture\TestImpactData\CalculatorTest::testAdds => Calculator.php, CalculatorTest.php, Rounder.php
PHPUnit\TestFixture\TestImpactData\IsolatedCalculatorTest::testAddsInAnotherProcess => Calculator.php, IsolatedCalculatorTest.php, Rounder.php
PHPUnit\TestFixture\TestImpactData\NothingCoveredTest::testAdds => Calculator.php, NothingCoveredTest.php, Rounder.php
