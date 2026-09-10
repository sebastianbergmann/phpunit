--TEST--
Which tests can be affected by what changed, and why each of them can be, is reported
--FILE--
<?php declare(strict_types=1);
function run(array $additionalArguments = []): void
{
    $process = proc_open(
        [
            PHP_BINARY,
            __DIR__ . '/../../../../phpunit',
            '--no-progress',
            '--colors=never',
            '--configuration',
            __DIR__ . '/_files/phpunit-explain-impacted.xml',
            ...$additionalArguments,
        ],
        [1 => ['pipe', 'w']],
        $pipes,
    );

    $output = stream_get_contents($pipes[1]);

    fclose($pipes[1]);
    proc_close($process);

    print $output;
}

run();

print PHP_EOL . 'Nothing changed:' . PHP_EOL . PHP_EOL;

run(['--explain-impacted']);

print PHP_EOL . 'Calculator.php is named as changed:' . PHP_EOL . PHP_EOL;

/*
 * The last run is not run in another process, so that the code that explains
 * the selection is covered by this test.
 */
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/phpunit-explain-impacted.xml';
$_SERVER['argv'][] = '--explain-impacted';
$_SERVER['argv'][] = '--impacted-by';
$_SERVER['argv'][] = __DIR__ . '/_files/src/Calculator.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/.phpunit.cache.explain-impacted');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %s

Time: %s, Memory: %s

OK, but some tests were skipped!
Tests: 4, Assertions: 3, Skipped: 1.

Nothing changed:

PHPUnit %s by Sebastian Bergmann and contributors.

Recorded at %s from what the tests executed.

1 of 4 tests can be affected by what changed.

1 test has never been recorded:
 - PHPUnit\TestFixture\TestImpactData\SkippedTest::testIsSkipped


Calculator.php is named as changed:

PHPUnit %s by Sebastian Bergmann and contributors.

Recorded at %s from what the tests executed.

4 of 4 tests can be affected by what changed.

3 tests depend on something that changed:
 - PHPUnit\TestFixture\TestImpactData\CalculatorTest::testAdds
   %sCalculator.php
 - PHPUnit\TestFixture\TestImpactData\IsolatedCalculatorTest::testAddsInAnotherProcess
   %sCalculator.php
 - PHPUnit\TestFixture\TestImpactData\NothingCoveredTest::testAdds
   %sCalculator.php

1 test has never been recorded:
 - PHPUnit\TestFixture\TestImpactData\SkippedTest::testIsSkipped
