--TEST--
A test run that is asked to fail on an empty test suite does not fail when no test can be affected by what changed
--FILE--
<?php declare(strict_types=1);
$cacheDirectory = __DIR__ . '/_files/.phpunit.cache.fail-on-empty-test-suite';

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
            __DIR__ . '/_files/phpunit-fixture-directory.xml',
            '--cache-directory',
            $cacheDirectory,
            ...$additionalArguments,
        ],
        [1 => ['pipe', 'w']],
        $pipes,
    );

    $output = stream_get_contents($pipes[1]);

    fclose($pipes[1]);

    $exitCode = proc_close($process);

    foreach (preg_split('/\R/', $output) as $line) {
        if (str_starts_with($line, 'Impact:') || str_starts_with($line, 'OK') || str_starts_with($line, 'No tests executed')) {
            print $line . PHP_EOL;
        }
    }

    print 'Exit code: ' . $exitCode . PHP_EOL;
}

run();

print PHP_EOL . 'Nothing changed:' . PHP_EOL;

run(['--only-impacted', '--fail-on-empty-test-suite']);

print PHP_EOL . 'Nothing to run because nothing was selected is not an empty test suite, whereas a filter that matches no test is:' . PHP_EOL;

run(['--fail-on-empty-test-suite', '--filter', 'ThereIsNoSuchTest']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/.phpunit.cache.fail-on-empty-test-suite');
--EXPECT--
OK (2 tests, 2 assertions)
Exit code: 0

Nothing changed:
Impact:        0 of 2 tests can be affected by what changed; 2 tests are not run
No tests executed!
Exit code: 0

Nothing to run because nothing was selected is not an empty test suite, whereas a filter that matches no test is:
No tests executed!
Exit code: 1
