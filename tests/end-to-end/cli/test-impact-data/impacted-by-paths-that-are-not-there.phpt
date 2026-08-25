--TEST--
A path that is not there is resolved all the same, and a path nothing is known about means that every test is run
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

/*
 * A source file that was deleted is a change like any other. The first path
 * names where such a file was, the second names one relative to the working
 * directory, which is where a path that comes from version control is
 * relative to.
 */
file_put_contents(
    $listOfPaths,
    __DIR__ . '/_files/src/DeletedCalculator.php' . PHP_EOL .
    'src/DeletedRounder.php' . PHP_EOL,
);

$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = $configuration;
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
Impact:        every test is run: %sDeletedCalculator.php is not among the files that were recorded

Time: %s, Memory: %s

OK (2 tests, 2 assertions)
