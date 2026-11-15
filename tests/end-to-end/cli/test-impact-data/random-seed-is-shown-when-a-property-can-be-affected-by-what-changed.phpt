--TEST--
The random seed is shown for the properties of a test suite only when a property is among the tests that can be affected by what changed
--CONFLICTS--
test-impact-data
--FILE--
<?php declare(strict_types=1);
function run(array $additionalArguments, bool $print): void
{
    $process = proc_open(
        [
            PHP_BINARY,
            __DIR__ . '/../../../../phpunit',
            '--no-progress',
            '--colors=never',
            '--configuration',
            __DIR__ . '/_files/properties/phpunit-property-and-test.xml',
            ...$additionalArguments,
        ],
        [1 => ['pipe', 'w']],
        $pipes,
    );

    $output = stream_get_contents($pipes[1]);

    fclose($pipes[1]);
    proc_close($process);

    if (!$print) {
        return;
    }

    foreach (preg_split('/\R/', $output) as $line) {
        if (str_starts_with($line, 'Random Seed:') || str_starts_with($line, 'Impact:') || str_starts_with($line, 'OK') || str_starts_with($line, 'Tests:') || str_starts_with($line, 'No tests executed')) {
            print $line . PHP_EOL;
        }
    }
}

/*
 * A test run that consists of a single test records the source files its
 * process loaded for that test, so what the tests execute is recorded by a
 * run of every test before each path is named.
 */
function record(): void
{
    run([], false);
}

record();

print 'Negator.php named, which only the test executes:' . PHP_EOL;

run(['--impacted-by', __DIR__ . '/_files/properties/src/Negator.php'], true);

record();

print PHP_EOL . 'Doubler.php named, which only the property executes:' . PHP_EOL;

run(['--impacted-by', __DIR__ . '/_files/properties/src/Doubler.php'], true);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/properties/.phpunit.cache.property-and-test');
--EXPECTF--
Negator.php named, which only the test executes:
Impact:        1 of 2 tests can be affected by what changed; 1 test is not run
OK (1 test, 1 assertion)

Doubler.php named, which only the property executes:
Random Seed:   %d
Impact:        1 of 2 tests can be affected by what changed; 1 test is not run
OK (1 test, 3 assertions)
