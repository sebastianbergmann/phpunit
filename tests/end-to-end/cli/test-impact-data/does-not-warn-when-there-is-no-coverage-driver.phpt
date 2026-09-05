--TEST--
A run that only needs the code coverage driver for recording test impact data does not warn when there is no driver
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
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/.phpunit.cache.without-coverage-driver');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %s

Time: %s, Memory: %s

OK, but some tests were skipped!
Tests: 4, Assertions: 3, Skipped: 1.
