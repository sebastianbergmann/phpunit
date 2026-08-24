--TEST--
Only the tests that can be affected by the paths that are listed in a file, or on standard input, are run
--FILE--
<?php declare(strict_types=1);
function run(array $additionalArguments = [], ?string $standardInput = null): void
{
    $descriptors = [1 => ['pipe', 'w']];

    if ($standardInput !== null) {
        $descriptors[0] = ['pipe', 'r'];
    }

    $process = proc_open(
        [
            PHP_BINARY,
            __DIR__ . '/../../../../phpunit',
            '--no-progress',
            '--colors=never',
            '--configuration',
            __DIR__ . '/_files/phpunit-impacted-by-file.xml',
            ...$additionalArguments,
        ],
        $descriptors,
        $pipes,
    );

    if ($standardInput !== null) {
        fwrite($pipes[0], $standardInput);
        fclose($pipes[0]);
    }

    $output = stream_get_contents($pipes[1]);

    fclose($pipes[1]);
    proc_close($process);

    foreach (preg_split('/\R/', $output) as $line) {
        if (str_starts_with($line, 'Impact:') || str_starts_with($line, 'OK') || str_starts_with($line, 'Tests:') || str_starts_with($line, 'No tests executed') || str_starts_with($line, 'Cannot read')) {
            print $line . PHP_EOL;
        }
    }
}

$listOfPaths = __DIR__ . '/_files/changed-paths.txt';

file_put_contents($listOfPaths, __DIR__ . '/_files/src/Rounder.php' . PHP_EOL);

run();

print PHP_EOL . 'Rounder.php listed in a file:' . PHP_EOL;

run(['--impacted-by-file', $listOfPaths]);

print PHP_EOL . 'Rounder.php on standard input:' . PHP_EOL;

run(['--impacted-by-file', '-'], __DIR__ . '/_files/src/Rounder.php' . PHP_EOL);

print PHP_EOL . 'Nothing on standard input:' . PHP_EOL;

run(['--impacted-by-file', '-'], '');

print PHP_EOL . 'A list that is not there:' . PHP_EOL;

run(['--impacted-by-file', __DIR__ . '/_files/does-not-exist.txt']);

unlink($listOfPaths);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/.phpunit.cache.impacted-by-file');
--EXPECTF--
OK (2 tests, 2 assertions)

Rounder.php listed in a file:
Impact:        1 of 2 tests can be affected by what changed; 1 test is not run
OK (1 test, 1 assertion)

Rounder.php on standard input:
Impact:        1 of 2 tests can be affected by what changed; 1 test is not run
OK (1 test, 1 assertion)

Nothing on standard input:
Impact:        0 of 2 tests can be affected by what changed; 2 tests are not run
No tests executed!

A list that is not there:
Cannot read the files and directories that changed from %s
