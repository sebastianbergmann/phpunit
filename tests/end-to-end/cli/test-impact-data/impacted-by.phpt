--TEST--
Only the tests that can be affected by the paths that are named are run
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
            __DIR__ . '/_files/phpunit-from-coverage-targets.xml',
            ...$additionalArguments,
        ],
        [1 => ['pipe', 'w']],
        $pipes,
    );

    $output = stream_get_contents($pipes[1]);

    fclose($pipes[1]);
    proc_close($process);

    foreach (explode("\n", $output) as $line) {
        if (str_starts_with($line, 'Impact:') || str_starts_with($line, 'Tests:') || str_starts_with($line, 'No tests executed')) {
            print $line . PHP_EOL;
        }
    }
}

run();

print 'Rounder.php named:' . PHP_EOL;

run(['--impacted-by', __DIR__ . '/_files/src/Rounder.php']);

print PHP_EOL . 'A path nothing knows about named:' . PHP_EOL;

run(['--impacted-by', __DIR__ . '/_files/phpunit-from-coverage-targets.xml']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/.phpunit.cache.from-coverage-targets');
--EXPECTF--
Tests: 4, Assertions: 3, Skipped: 1.
Rounder.php named:
Impact:        2 of 4 tests can be affected by what changed; 2 tests are not run
Tests: 2, Assertions: 1, Skipped: 1.

A path nothing knows about named:
Impact:        every test is run: %s is not among the files that were recorded
Tests: 4, Assertions: 3, Skipped: 1.
