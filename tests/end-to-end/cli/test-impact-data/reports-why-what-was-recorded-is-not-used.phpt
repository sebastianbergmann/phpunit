--TEST--
Why what was recorded is not used is reported instead of claiming that nothing was recorded
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
            __DIR__ . '/_files/phpunit-discarded.xml',
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

/*
 * The test suite is bootstrapped differently than it was when what is there
 * was recorded: what is there is not used, and that is what is reported.
 */
print PHP_EOL . 'Explained after the test suite is bootstrapped differently:' . PHP_EOL . PHP_EOL;

run(['--explain-impacted', '--bootstrap', __DIR__ . '/_files/another-bootstrap.php']);

print PHP_EOL . 'Listed after the test suite is bootstrapped differently:' . PHP_EOL . PHP_EOL;

run(['--list-tests-that-depend-on', __DIR__ . '/_files/src/Calculator.php', '--bootstrap', __DIR__ . '/_files/another-bootstrap.php']);

print PHP_EOL . 'Selected after the test suite is bootstrapped differently:' . PHP_EOL . PHP_EOL;

run(['--only-impacted', '--bootstrap', __DIR__ . '/_files/another-bootstrap.php']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/.phpunit.cache.discarded');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %s

Time: %s, Memory: %s

OK, but some tests were skipped!
Tests: 4, Assertions: 3, Skipped: 1.

Explained after the test suite is bootstrapped differently:

PHPUnit %s by Sebastian Bergmann and contributors.

Every test is run: a bootstrap script changed since the test impact data was recorded

Listed after the test suite is bootstrapped differently:

PHPUnit %s by Sebastian Bergmann and contributors.

No test that depends on %sCalculator.php is recorded: a bootstrap script changed since the test impact data was recorded

Selected after the test suite is bootstrapped differently:

PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %s
Impact:        every test is run: a bootstrap script changed since the test impact data was recorded

Time: %s, Memory: %s

OK, but some tests were skipped!
Tests: 4, Assertions: 3, Skipped: 1.
