--TEST--
A change to a file in the directory of a test suite that is not a test file and that no test is recorded as depending on runs every test
--FILE--
<?php declare(strict_types=1);
$helper   = __DIR__ . '/_files/watched-files/tests/Helper.php';
$contents = file_get_contents($helper);

copy($helper, $helper . '.backup');

function run(array $additionalArguments = []): void
{
    $process = proc_open(
        [
            PHP_BINARY,
            __DIR__ . '/../../../../phpunit',
            '--no-progress',
            '--colors=never',
            '--configuration',
            __DIR__ . '/_files/watched-files/phpunit.xml',
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

print PHP_EOL . 'Nothing changed:' . PHP_EOL;

run(['--only-impacted']);

file_put_contents($helper, str_replace("'world'", "'everyone'", $contents));

print PHP_EOL . 'Helper.php changed:' . PHP_EOL;

run(['--only-impacted']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

$helper = __DIR__ . '/_files/watched-files/tests/Helper.php';
$backup = $helper . '.backup';

if (is_file($backup)) {
    copy($backup, $helper);
    unlink($backup);
}

delete_directory(__DIR__ . '/_files/watched-files/.phpunit.cache');
--EXPECTF--
OK (1 test, 1 assertion)

Nothing changed:
Impact:        0 of 1 tests can be affected by what changed; 1 test is not run
No tests executed!

Helper.php changed:
Impact:        every test is run: %sHelper.php changed and no test is recorded as depending on it
Tests: 1, Assertions: 1, Failures: 1.
