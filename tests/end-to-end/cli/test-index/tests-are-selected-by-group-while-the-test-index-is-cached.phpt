--TEST--
Selecting tests by group works while the test index is cached and no filter for the name of a test is used
--FILE--
<?php declare(strict_types=1);
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

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--cache-directory';
$_SERVER['argv'][] = $cacheDirectory;
$_SERVER['argv'][] = '--cache-test-index';
$_SERVER['argv'][] = '--group';
$_SERVER['argv'][] = 'a-group';
$_SERVER['argv'][] = '--list-tests';
$_SERVER['argv'][] = __DIR__ . '/_files/selection';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Available test:
 - PHPUnit\TestFixture\TestIndexSelection\SelectedTest::testOne
