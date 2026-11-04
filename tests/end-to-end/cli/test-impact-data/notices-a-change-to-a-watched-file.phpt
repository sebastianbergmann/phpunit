--TEST--
A change to a file that is watched and that no test is recorded as depending on runs every test, and so does a file that is added where files are watched
--FILE--
<?php declare(strict_types=1);
$configuration = __DIR__ . '/_files/watched-files/config/app.php';
$added         = __DIR__ . '/_files/watched-files/config/added.php';
$contents      = file_get_contents($configuration);

copy($configuration, $configuration . '.backup');

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

file_put_contents($configuration, str_replace("'Hello world'", "'Hello everyone'", $contents));

print PHP_EOL . 'app.php changed:' . PHP_EOL;

run(['--only-impacted']);

copy($configuration . '.backup', $configuration);

run();

file_put_contents($added, '<?php return [];');

print PHP_EOL . 'added.php was added:' . PHP_EOL;

run(['--only-impacted']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

$configuration = __DIR__ . '/_files/watched-files/config/app.php';
$backup        = $configuration . '.backup';
$added         = __DIR__ . '/_files/watched-files/config/added.php';

if (is_file($backup)) {
    copy($backup, $configuration);
    unlink($backup);
}

if (is_file($added)) {
    unlink($added);
}

delete_directory(__DIR__ . '/_files/watched-files/.phpunit.cache');
--EXPECTF--
OK (1 test, 1 assertion)

app.php changed:
Impact:        every test is run: %sapp.php changed and no test is recorded as depending on it
Tests: 1, Assertions: 1, Failures: 1.
OK (1 test, 1 assertion)

added.php was added:
Impact:        every test is run: %sadded.php was not there, or was not watched, when what is known was recorded
OK (1 test, 1 assertion)
