--TEST--
--explain-impacted names the files that were added where added files can only affect a test through a file that was changed to use them, whether what changed is worked out or named, and a file that is added elsewhere runs every test
--FILE--
<?php declare(strict_types=1);
$added      = __DIR__ . '/_files/added-files-reached-through-changes/src/Added.php';
$discovered = __DIR__ . '/_files/added-files-reached-through-changes/src/Discovered';

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
        if (str_starts_with($line, 'PHPUnit ') || str_starts_with($line, 'Runtime:') || str_starts_with($line, 'Configuration:') || str_starts_with($line, 'Time:') || str_starts_with($line, 'Recorded at ') || $line === '') {
            continue;
        }

        print $line . PHP_EOL;
    }
}

run();

file_put_contents($added, '<?php final class Added {}');

print PHP_EOL . 'Added.php was added:' . PHP_EOL;

run(['--explain-impacted']);

print PHP_EOL . 'Added.php was added, and is named:' . PHP_EOL;

run(['--explain-impacted', '--impacted-by', $added]);

mkdir($discovered);
file_put_contents($discovered . '/Handler.php', '<?php final class Handler {}');

print PHP_EOL . 'Handler.php was added where added files are discovered:' . PHP_EOL;

run(['--explain-impacted']);

print PHP_EOL . 'Handler.php was added where added files are discovered, and is named:' . PHP_EOL;

run(['--explain-impacted', '--impacted-by', $discovered . '/Handler.php']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

$added = __DIR__ . '/_files/added-files-reached-through-changes/src/Added.php';

if (is_file($added)) {
    unlink($added);
}

delete_directory(__DIR__ . '/_files/added-files-reached-through-changes/src/Discovered');
delete_directory(__DIR__ . '/_files/added-files-reached-through-changes/.phpunit.cache');
--EXPECTF--
OK (1 test, 1 assertion)

Added.php was added:
0 of 1 tests can be affected by what changed.
1 file was added that can only affect a test through a file that was changed to use it, as configured in <addedFilesAreReachedThroughChanges>:
 - %sAdded.php

Added.php was added, and is named:
0 of 1 tests can be affected by what changed.
1 file was added that can only affect a test through a file that was changed to use it, as configured in <addedFilesAreReachedThroughChanges>:
 - %sAdded.php

Handler.php was added where added files are discovered:
Every test is run: %sHandler.php was not there, or was not first-party code, when what is known was recorded

Handler.php was added where added files are discovered, and is named:
Every test is run: %sHandler.php is not among the files that were recorded
