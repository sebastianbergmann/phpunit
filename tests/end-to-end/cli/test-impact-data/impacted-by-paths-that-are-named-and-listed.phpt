--TEST--
The paths that are named on the command line and the paths that are listed in a file are both taken into account
--FILE--
<?php declare(strict_types=1);
$configuration = __DIR__ . '/_files/phpunit-selection.xml';
$listOfPaths   = __DIR__ . '/_files/paths-that-changed.txt';

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

file_put_contents($listOfPaths, __DIR__ . '/_files/src/Calculator.php' . PHP_EOL);

$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = $configuration;
$_SERVER['argv'][] = '--impacted-by';
$_SERVER['argv'][] = __DIR__ . '/_files/src/Rounder.php';
$_SERVER['argv'][] = '--impacted-by-file';
$_SERVER['argv'][] = $listOfPaths;

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv'], false);

unlink($listOfPaths);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/.phpunit.cache.selection');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %s
Impact:        2 of 2 tests can be affected by what changed; 0 tests are not run

Time: %s, Memory: %s

OK (2 tests, 2 assertions)
