--TEST--
What was recorded is kept when only settings that cannot change what code a test executes changed, and discarded when one that can changed
--FILE--
<?php declare(strict_types=1);
function run(string $configuration, array $additionalArguments = []): void
{
    $process = proc_open(
        [
            PHP_BINARY,
            __DIR__ . '/../../../../phpunit',
            '--no-progress',
            '--colors=never',
            '--configuration',
            __DIR__ . '/_files/' . $configuration,
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

run('phpunit-execution-settings.xml');

/*
 * A tool that runs PHPUnit with a configuration of its own changes how
 * results are reported, in which order tests are run, and when a test run
 * stops: none of that changes what code a test executes.
 */
print PHP_EOL . 'Explained with a configuration that only reports differently:' . PHP_EOL . PHP_EOL;

run('phpunit-execution-settings-reported-differently.xml', ['--explain-impacted']);

/*
 * A PHP setting can change what code a test executes.
 */
print PHP_EOL . 'Explained with a configuration that configures a PHP setting:' . PHP_EOL . PHP_EOL;

run('phpunit-execution-settings-with-php-setting.xml', ['--explain-impacted']);

/*
 * So can a PHP setting that is configured on the command line, without the
 * configuration file changing.
 */
print PHP_EOL . 'Explained with a PHP setting that is configured on the command line:' . PHP_EOL . PHP_EOL;

run('phpunit-execution-settings.xml', ['--explain-impacted', '-d', 'precision=10']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/.phpunit.cache.execution-settings');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %s

Time: %s, Memory: %s

OK, but some tests were skipped!
Tests: 4, Assertions: 3, Skipped: 1.

Explained with a configuration that only reports differently:

PHPUnit %s by Sebastian Bergmann and contributors.

Recorded at %s from what the tests executed.

1 of 4 tests can be affected by what changed.

1 test has never been recorded:
 - PHPUnit\TestFixture\TestImpactData\SkippedTest::testIsSkipped


Explained with a configuration that configures a PHP setting:

PHPUnit %s by Sebastian Bergmann and contributors.

Every test is run: the configuration changed since the test impact data was recorded

Explained with a PHP setting that is configured on the command line:

PHPUnit %s by Sebastian Bergmann and contributors.

Every test is run: the configuration changed since the test impact data was recorded
