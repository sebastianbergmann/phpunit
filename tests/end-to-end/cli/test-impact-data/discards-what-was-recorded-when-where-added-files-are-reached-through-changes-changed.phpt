--TEST--
What was recorded is discarded when where added files can only affect a test through a file that was changed to use them is not what it was
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
            __DIR__ . '/_files/added-files-reached-through-changes/' . $configuration,
            ...$additionalArguments,
        ],
        [1 => ['pipe', 'w']],
        $pipes,
    );

    $output = stream_get_contents($pipes[1]);

    fclose($pipes[1]);
    proc_close($process);

    foreach (preg_split('/\R/', $output) as $line) {
        if (str_starts_with($line, 'Every test is run') || str_starts_with($line, 'OK')) {
            print $line . PHP_EOL;
        }
    }
}

run('phpunit.xml');

run('phpunit-elsewhere.xml', ['--explain-impacted']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/added-files-reached-through-changes/.phpunit.cache');
--EXPECT--
OK (1 test, 1 assertion)
Every test is run: where an added file can only affect a test through a file that was changed to use it changed since the test impact data was recorded
