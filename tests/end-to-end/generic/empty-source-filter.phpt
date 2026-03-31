--TEST--
Warning when configured source filter does not match any files
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('xdebug') && !extension_loaded('pcov')) {
    print 'skip: Extension Xdebug or PCOV must be loaded.';
}
--INI--
xdebug.mode=coverage
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--coverage-text';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/empty-source-filter';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Configured source filter (include-path: %snonexistent-directory) does not match any files, code coverage will not be processed

OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Warnings: 1.
