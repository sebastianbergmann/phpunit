--TEST--
A test run that selects the tests that can be affected by what changed runs no test when no test can be affected
--FILE--
<?php declare(strict_types=1);
$cacheDirectory = __DIR__ . '/_files/.phpunit.cache.no-test-can-be-affected';

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

$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/phpunit-fixture-directory.xml';
$_SERVER['argv'][] = '--cache-directory';
$_SERVER['argv'][] = $cacheDirectory;
$_SERVER['argv'][] = '--do-not-record-test-impact-data';
$_SERVER['argv'][] = '--only-impacted';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/.phpunit.cache.no-test-can-be-affected');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %s
Impact:        0 of 2 tests can be affected by what changed; 2 tests are not run

No tests executed!
