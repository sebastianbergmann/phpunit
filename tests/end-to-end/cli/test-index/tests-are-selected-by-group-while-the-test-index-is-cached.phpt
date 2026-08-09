--TEST--
Selecting tests by group works while the test index is cached and no filter for the name of a test is used
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/_files/setup.php';

$cacheDirectory = \sys_get_temp_dir() . \DIRECTORY_SEPARATOR . 'phpunit-test-index-' . \uniqid();

\register_shutdown_function(
    static function () use ($cacheDirectory): void {
        if (!\is_dir($cacheDirectory)) {
            return;
        }

        foreach (\scandir($cacheDirectory) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            \unlink($cacheDirectory . \DIRECTORY_SEPARATOR . $entry);
        }

        \rmdir($cacheDirectory);
    },
);

$arguments = [
    '--do-not-record-test-run-history',
    '--no-configuration',
    '--cache-directory',
    $cacheDirectory,
    '--cache-test-index',
    '--group',
    'a-group',
    '--list-tests',
    __DIR__ . '/_files/selection',
];

// The run below is the one that has an index to skip test files by
warmTestIndex($arguments);

foreach ($arguments as $argument) {
    $_SERVER['argv'][] = $argument;
}

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Available test:
 - PHPUnit\TestFixture\TestIndexSelection\SelectedTest::testOne
