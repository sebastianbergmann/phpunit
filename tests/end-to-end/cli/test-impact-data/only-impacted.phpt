--TEST--
Only the tests that can be affected by what changed are run
--FILE--
<?php declare(strict_types=1);
$configuration = __DIR__ . '/_files/phpunit.xml';
$sourceFile    = __DIR__ . '/_files/src/Rounder.php';
$contents      = file_get_contents($sourceFile);

function run(array $additionalArguments = []): void
{
    $process = proc_open(
        [
            PHP_BINARY,
            __DIR__ . '/../../../../phpunit',
            '--no-progress',
            '--colors=never',
            '--configuration',
            __DIR__ . '/_files/phpunit.xml',
            ...$additionalArguments,
        ],
        [1 => ['pipe', 'w']],
        $pipes,
    );

    $output = stream_get_contents($pipes[1]);

    fclose($pipes[1]);
    proc_close($process);

    foreach (explode(PHP_EOL, $output) as $line) {
        if (str_starts_with($line, 'Impact:') || str_starts_with($line, 'OK') || str_starts_with($line, 'Tests:') || str_starts_with($line, 'No tests executed')) {
            print $line . PHP_EOL;
        }
    }
}

run();

print PHP_EOL . 'Nothing changed:' . PHP_EOL;

run(['--only-impacted']);

file_put_contents($sourceFile, $contents . PHP_EOL);

print PHP_EOL . 'Rounder.php changed:' . PHP_EOL;

run(['--only-impacted']);

file_put_contents($sourceFile, $contents);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/.phpunit.cache');
--EXPECT--
OK, but some tests were skipped!
Tests: 4, Assertions: 3, Skipped: 1.

Nothing changed:
Impact:        1 of 4 tests can be affected by what changed; 3 tests are not run
OK, but some tests were skipped!
Tests: 1, Assertions: 0, Skipped: 1.

Rounder.php changed:
Impact:        4 of 4 tests can be affected by what changed; 0 tests are not run
OK, but some tests were skipped!
Tests: 4, Assertions: 3, Skipped: 1.
