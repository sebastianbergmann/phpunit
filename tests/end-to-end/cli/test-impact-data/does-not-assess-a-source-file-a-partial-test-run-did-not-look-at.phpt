--TEST--
A test run that ran only some of the tests does not assess a source file that no test refers to
--FILE--
<?php declare(strict_types=1);
$addedSourceFile = __DIR__ . '/_files/source-file-no-test-refers-to/src/Added.php';

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
        if (str_starts_with($line, 'Impact:') || str_starts_with($line, 'OK') || str_starts_with($line, 'No tests executed')) {
            print $line . PHP_EOL;
        }
    }
}

run();

file_put_contents(
    $addedSourceFile,
    <<<'PHP'
        <?php declare(strict_types=1);
        namespace PHPUnit\TestFixture\TestImpactData\SourceFileNoTestRefersTo;

        final class Added
        {
        }
        PHP,
);

/*
 * A run that ran only some of the tests did not look at the source file that
 * was added, and must not record it as if it had.
 */
run(['--filter', 'CoveredTest']);

print PHP_EOL . 'Added.php is there, and only some of the tests were run:' . PHP_EOL;

run(['--only-impacted']);

print PHP_EOL . 'Added.php is there, and the test run that ran every test there is assessed it:' . PHP_EOL;

run(['--only-impacted']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

@unlink(__DIR__ . '/_files/source-file-no-test-refers-to/src/Added.php');

delete_directory(__DIR__ . '/_files/source-file-no-test-refers-to/.phpunit.cache');
--EXPECTF--
OK (1 test, 1 assertion)
OK (1 test, 1 assertion)

Added.php is there, and only some of the tests were run:
Impact:        every test is run: %s was not there, or was not first-party code, when what is known was recorded
OK (1 test, 1 assertion)

Added.php is there, and the test run that ran every test there is assessed it:
Impact:        0 of 1 tests can be affected by what changed; 1 test is not run
No tests executed!
