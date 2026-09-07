--TEST--
Test impact data cannot be recorded when no code coverage driver is available
--SKIPIF--
<?php declare(strict_types=1);
if (extension_loaded('xdebug') || extension_loaded('pcov')) {
    print 'skip: There must be no code coverage driver.';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/phpunit-without-coverage-driver.xml';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv'], false);

if (file_exists(__DIR__ . '/_files/.phpunit.cache.without-coverage-driver/test-impact-data')) {
    print PHP_EOL . 'Test impact data was written' . PHP_EOL;
} else {
    print PHP_EOL . 'No test impact data was written' . PHP_EOL;
}
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/.phpunit.cache.without-coverage-driver');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %s

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) No code coverage driver available that supports line coverage, test impact data will not be recorded

OK, but there were issues!
Tests: 4, Assertions: 3, PHPUnit Warnings: 1, Skipped: 1.

No test impact data was written
