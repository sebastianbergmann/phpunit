--TEST--
A path that names where it is with "." and ".." names the same file as the path that was recorded
--FILE--
<?php declare(strict_types=1);
$configuration = __DIR__ . '/_files/phpunit-paths-with-relative-segments.xml';

/*
 * What each test depends on has to be recorded before it can be used, and a
 * test run can only be made once in a process, so the recording is made in a
 * child process. The test run that uses what was recorded is made in this
 * process.
 */
$process = proc_open(
    [
        PHP_BINARY,
        __DIR__ . '/../../../../phpunit',
        '--no-progress',
        '--colors=never',
        '--configuration',
        $configuration,
    ],
    [1 => ['pipe', 'w']],
    $pipes,
);

stream_get_contents($pipes[1]);

fclose($pipes[1]);
proc_close($process);

$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = $configuration;
$_SERVER['argv'][] = '--impacted-by';

/*
 * The directory that is not there is what keeps realpath() from resolving the
 * path, so that PHPUnit has to work out where the path names for itself. That
 * only the test that depends on Rounder.php is run is what says that it worked
 * it out the way realpath() would have.
 */
$_SERVER['argv'][] = __DIR__ . '/_files/./does-not-exist/../src//Rounder.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv'], false);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/.phpunit.cache.paths-with-relative-segments');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %s
Impact:        1 of 2 tests can be affected by what changed; 1 test is not run

Time: %s, Memory: %s

OK (1 test, 1 assertion)
