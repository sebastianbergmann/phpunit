--TEST--
Test impact data cannot be derived from the code coverage targets the tests declare when code coverage metadata is not required for all tests
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/phpunit-coverage-metadata-that-is-not-required-for-all-tests.xml';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv'], false);

if (file_exists(__DIR__ . '/_files/.phpunit.cache.coverage-metadata-that-is-not-required-for-all-tests/test-impact-data')) {
    print PHP_EOL . 'Test impact data was written' . PHP_EOL;
} else {
    print PHP_EOL . 'No test impact data was written' . PHP_EOL;
}
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/.phpunit.cache.coverage-metadata-that-is-not-required-for-all-tests');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %s

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Cannot derive test impact data from code coverage targets because code coverage metadata is not required for all tests

OK, but there were issues!
Tests: 4, Assertions: 3, PHPUnit Warnings: 1, Skipped: 1.

No test impact data was written
