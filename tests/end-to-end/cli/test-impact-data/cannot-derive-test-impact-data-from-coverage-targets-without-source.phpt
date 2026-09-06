--TEST--
Test impact data cannot be derived from the code coverage targets the tests declare when no source filter is configured
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--derive-test-impact-data-from-coverage-targets';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/phpunit-without-source.xml';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv'], false);

if (file_exists(__DIR__ . '/_files/.phpunit.cache.without-source/test-impact-data')) {
    print PHP_EOL . 'Test impact data was written' . PHP_EOL;
} else {
    print PHP_EOL . 'No test impact data was written' . PHP_EOL;
}
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/.phpunit.cache.without-source');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %s

Time: %s, Memory: %s

There were 2 PHPUnit test runner warnings:

1) Test impact data derived from code coverage targets is only as complete as those targets are, and they are not checked because tests that execute code they do not declare are not considered risky

2) No filter is configured, test impact data will not be recorded

OK, but there were issues!
Tests: 4, Assertions: 3, PHPUnit Warnings: 2, Skipped: 1.

No test impact data was written
