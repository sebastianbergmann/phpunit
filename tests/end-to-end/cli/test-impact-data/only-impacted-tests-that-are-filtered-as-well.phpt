--TEST--
How many tests are not run is not said when the tests that are run are filtered as well
--FILE--
<?php declare(strict_types=1);
$fixture        = __DIR__ . '/_files/fixture-directory/one.txt';
$backup         = __DIR__ . '/_files/fixture-directory/one.txt.backup';
$cacheDirectory = __DIR__ . '/_files/.phpunit.cache.filtered-as-well';

copy($fixture, $backup);

$process = proc_open(
    [
        PHP_BINARY,
        __DIR__ . '/../../../../phpunit',
        '--no-progress',
        '--colors=never',
        '--configuration',
        __DIR__ . '/_files/phpunit-fixture-directory.xml',
        '--cache-directory',
        $cacheDirectory,
    ],
    [1 => ['pipe', 'w']],
    $pipes,
);

stream_get_contents($pipes[1]);

fclose($pipes[1]);
proc_close($process);

file_put_contents($fixture, '4,5,9' . PHP_EOL);

/*
 * The test that uses the fixture is the test that can be affected by what
 * changed, and it is the test the filter keeps from being run: what test
 * impact analysis selected is not what this test run runs.
 */
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/phpunit-fixture-directory.xml';
$_SERVER['argv'][] = '--cache-directory';
$_SERVER['argv'][] = $cacheDirectory;
$_SERVER['argv'][] = '--only-impacted';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'PlainTest';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv'], false);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

$fixture = __DIR__ . '/_files/fixture-directory/one.txt';
$backup  = __DIR__ . '/_files/fixture-directory/one.txt.backup';

if (file_exists($backup)) {
    copy($backup, $fixture);
    unlink($backup);
}

delete_directory(__DIR__ . '/_files/.phpunit.cache.filtered-as-well');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %s
Impact:        1 of 2 tests can be affected by what changed; the tests that are run are filtered as well
Recorded:      %s

No tests executed!
