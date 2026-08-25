--TEST--
A test run that runs every test there is assesses a change to a source file no test refers to
--FILE--
<?php declare(strict_types=1);
$sourceFile = __DIR__ . '/_files/source-file-no-test-refers-to/src/Untested.php';
$contents   = file_get_contents($sourceFile);

function run(array $additionalArguments = []): void
{
    $process = proc_open(
        [
            PHP_BINARY,
            __DIR__ . '/../../../../phpunit',
            '--no-progress',
            '--colors=never',
            '--configuration',
            __DIR__ . '/_files/source-file-no-test-refers-to/phpunit.xml',
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

file_put_contents($sourceFile, str_replace("'first'", "'second'", $contents));

print PHP_EOL . 'Untested.php changed:' . PHP_EOL;

run(['--only-impacted']);

print PHP_EOL . 'Untested.php changed, and the test run that ran every test there is assessed it:' . PHP_EOL;

run(['--only-impacted']);

file_put_contents($sourceFile, $contents);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/source-file-no-test-refers-to/.phpunit.cache');
--EXPECTF--
OK (1 test, 1 assertion)

Untested.php changed:
Impact:        every test is run: %s changed and no test is recorded as depending on it
OK (1 test, 1 assertion)

Untested.php changed, and the test run that ran every test there is assessed it:
Impact:        0 of 1 tests can be affected by what changed; 1 test is not run
No tests executed!
