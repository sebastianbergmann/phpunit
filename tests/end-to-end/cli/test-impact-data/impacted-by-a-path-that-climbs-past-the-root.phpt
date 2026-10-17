--TEST--
A path that names where it is from the root does not name anything above the root
--FILE--
<?php declare(strict_types=1);
$cacheDirectory = __DIR__ . '/_files/.phpunit.cache.past-the-root';

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

/*
 * The path is not there, so it is worked out here instead of by realpath():
 * what is left of it once the '..' are worked out still names where it is
 * from the root.
 */
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/phpunit-fixture-directory.xml';
$_SERVER['argv'][] = '--cache-directory';
$_SERVER['argv'][] = $cacheDirectory;
$_SERVER['argv'][] = '--impacted-by';
$_SERVER['argv'][] = '/does-not-exist/./../../deleted.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv'], false);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/.phpunit.cache.past-the-root');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %s
Impact:        every test is run: %edeleted.php is not among the files that were recorded
Recorded:      %s

Time: %s, Memory: %s

OK (2 tests, 2 assertions)
