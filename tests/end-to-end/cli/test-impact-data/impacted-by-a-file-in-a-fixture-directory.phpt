--TEST--
The tests that depend on a fixture directory are selected when a file in that directory is named as having changed
--FILE--
<?php declare(strict_types=1);
function run(array $additionalArguments = [], string $input = ''): void
{
    $process = proc_open(
        [
            PHP_BINARY,
            __DIR__ . '/../../../../phpunit',
            '--no-progress',
            '--colors=never',
            '--configuration',
            __DIR__ . '/_files/phpunit-fixture-directory.xml',
            ...$additionalArguments,
        ],
        [0 => ['pipe', 'r'], 1 => ['pipe', 'w']],
        $pipes,
    );

    fwrite($pipes[0], $input);
    fclose($pipes[0]);

    $output = stream_get_contents($pipes[1]);

    fclose($pipes[1]);
    proc_close($process);

    foreach (preg_split('/\R/', $output) as $line) {
        if (str_starts_with($line, 'Impact:') || str_starts_with($line, 'OK')) {
            print $line . PHP_EOL;
        }
    }
}

run();

/*
 * Version control names the files that changed, and never the directory they
 * are in, which is how a fixture directory is declared and recorded.
 */
run(['--impacted-by-file', '-'], __DIR__ . '/_files/fixture-directory/one.txt' . PHP_EOL);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/.phpunit.cache.fixture-directory');
--EXPECT--
OK (2 tests, 2 assertions)
Impact:        1 of 2 tests can be affected by what changed; 1 test is not run
OK (1 test, 1 assertion)
