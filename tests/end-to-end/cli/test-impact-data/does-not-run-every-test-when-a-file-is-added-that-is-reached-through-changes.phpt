--TEST--
A file that is added where added files can only affect a test through a file that was changed to use them does not run every test, and a change to it that no test is recorded as depending on does once a test run recorded it
--FILE--
<?php declare(strict_types=1);
$greeter  = __DIR__ . '/_files/added-files-reached-through-changes/src/Greeter.php';
$added    = __DIR__ . '/_files/added-files-reached-through-changes/src/Added.php';
$contents = file_get_contents($greeter);

copy($greeter, $greeter . '.backup');

function run(array $additionalArguments = []): void
{
    $process = proc_open(
        [
            PHP_BINARY,
            __DIR__ . '/../../../../phpunit',
            '--no-progress',
            '--colors=never',
            '--configuration',
            __DIR__ . '/_files/added-files-reached-through-changes/phpunit.xml',
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

file_put_contents($added, "<?php final class Added { public const VALUE = 'first'; }");
file_put_contents($greeter, str_replace("return 'Hello ' . \$name;", "return 'Hello ' . \$name; // Added::VALUE", $contents));

print PHP_EOL . 'Added.php was added, and Greeter.php was changed to use it:' . PHP_EOL;

run(['--only-impacted']);

/*
 * The test run that set Added.php aside recorded it: the change that made
 * Greeter.php use it was assessed by that test run, and a change to it now is
 * a change nothing is known about.
 */
file_put_contents($added, "<?php final class Added { public const VALUE = 'second'; }");

print PHP_EOL . 'Added.php changed:' . PHP_EOL;

run(['--only-impacted']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

$greeter = __DIR__ . '/_files/added-files-reached-through-changes/src/Greeter.php';
$backup  = $greeter . '.backup';
$added   = __DIR__ . '/_files/added-files-reached-through-changes/src/Added.php';

if (is_file($backup)) {
    copy($backup, $greeter);
    unlink($backup);
}

if (is_file($added)) {
    unlink($added);
}

delete_directory(__DIR__ . '/_files/added-files-reached-through-changes/.phpunit.cache');
--EXPECTF--
OK (1 test, 1 assertion)

Added.php was added, and Greeter.php was changed to use it:
Impact:        1 of 1 tests can be affected by what changed; 0 tests are not run
OK (1 test, 1 assertion)

Added.php changed:
Impact:        every test is run: %sAdded.php changed and no test is recorded as depending on it
OK (1 test, 1 assertion)
