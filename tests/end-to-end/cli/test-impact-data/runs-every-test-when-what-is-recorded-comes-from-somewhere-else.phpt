--TEST--
Every test is run when what is recorded comes from somewhere else than where this test run records it from
--FILE--
<?php declare(strict_types=1);
$cacheDirectory = __DIR__ . '/_files/.phpunit.cache.other-provenance';

function run(array $additionalArguments = []): void
{
    global $cacheDirectory;

    $process = proc_open(
        [
            PHP_BINARY,
            __DIR__ . '/../../../../phpunit',
            '--no-progress',
            '--colors=never',
            '--configuration',
            __DIR__ . '/_files/phpunit-from-coverage-targets.xml',
            '--cache-directory',
            $cacheDirectory,
            ...$additionalArguments,
        ],
        [1 => ['pipe', 'w']],
        $pipes,
    );

    $output = stream_get_contents($pipes[1]);

    fclose($pipes[1]);
    proc_close($process);

    foreach (preg_split('/\R/', $output) as $line) {
        if (str_starts_with($line, 'Impact:') || str_starts_with($line, 'OK') || str_starts_with($line, 'Tests:') || str_starts_with($line, 'No tests executed')) {
            print $line . PHP_EOL;
        }
    }
}

run();

print PHP_EOL . 'What was recorded from the code coverage targets is of no use to a test run that observes what the tests execute:' . PHP_EOL;

run(['--do-not-derive-test-impact-data-from-coverage-targets', '--only-impacted']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/.phpunit.cache.other-provenance');
--EXPECTF--
OK, but some tests were skipped!
Tests: 4, Assertions: 3, Skipped: 1.

What was recorded from the code coverage targets is of no use to a test run that observes what the tests execute:
Impact:        every test is run: what is known was recorded from the code coverage targets the tests declare, and this test run records what the tests execute
OK, but some tests were skipped!
Tests: 4, Assertions: 3, Skipped: 1.
