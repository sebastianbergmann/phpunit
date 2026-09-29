--TEST--
What was recorded in one checkout of a project is used in another checkout of it, the checkout of another CI runner for instance
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

$checkouts       = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-uses-what-was-recorded-in-another-checkout';
$checkout        = $checkouts . DIRECTORY_SEPARATOR . 'runner-a' . DIRECTORY_SEPARATOR . 'project';
$anotherCheckout = $checkouts . DIRECTORY_SEPARATOR . 'runner-b' . DIRECTORY_SEPARATOR . 'project';

function copy_path(string $from, string $to): void
{
    if (is_file($from)) {
        copy($from, $to);

        return;
    }

    mkdir($to, 0777, true);

    foreach (scandir($from) as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        copy_path($from . DIRECTORY_SEPARATOR . $entry, $to . DIRECTORY_SEPARATOR . $entry);
    }
}

function run(string $checkout, array $additionalArguments = []): void
{
    $process = proc_open(
        [
            PHP_BINARY,
            __DIR__ . '/../../../../phpunit',
            '--no-progress',
            '--colors=never',
            '--configuration',
            $checkout . DIRECTORY_SEPARATOR . 'phpunit-fixture-directory.xml',
            '--cache-directory',
            $checkout . DIRECTORY_SEPARATOR . 'cache',
            ...$additionalArguments,
        ],
        [1 => ['pipe', 'w']],
        $pipes,
    );

    $output = stream_get_contents($pipes[1]);

    fclose($pipes[1]);
    proc_close($process);

    foreach (preg_split('/\R/', $output) as $line) {
        if (str_starts_with($line, 'Impact:') || str_starts_with($line, 'OK') || str_starts_with($line, 'No tests executed')) {
            print $line . PHP_EOL;
        }
    }
}

delete_directory($checkouts);

mkdir($checkout, 0777, true);

foreach (['phpunit-fixture-directory.xml', 'fixture-directory', 'src', 'tests-that-use-a-fixture-directory', 'vendor'] as $entry) {
    copy_path(__DIR__ . '/_files/' . $entry, $checkout . DIRECTORY_SEPARATOR . $entry);
}

run($checkout);

/*
 * The other runner has a checkout of its own, somewhere else, and restores
 * the cache directory the first runner saved. It cannot see the checkout of
 * the first runner.
 */
copy_path($checkout, $anotherCheckout);
delete_directory($checkout);

print PHP_EOL . 'Nothing changed:' . PHP_EOL;

run($anotherCheckout, ['--only-impacted']);

file_put_contents($anotherCheckout . DIRECTORY_SEPARATOR . 'fixture-directory' . DIRECTORY_SEPARATOR . 'one.txt', 'changed' . PHP_EOL, FILE_APPEND);

print PHP_EOL . 'A file in the fixture directory changed:' . PHP_EOL;

run($anotherCheckout, ['--only-impacted']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-uses-what-was-recorded-in-another-checkout');
--EXPECT--
OK (2 tests, 2 assertions)

Nothing changed:
Impact:        0 of 2 tests can be affected by what changed; 2 tests are not run
No tests executed!

A file in the fixture directory changed:
Impact:        1 of 2 tests can be affected by what changed; 1 test is not run
OK (1 test, 1 assertion)
