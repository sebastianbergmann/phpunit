--TEST--
https://github.com/sebastianbergmann/php-code-coverage/issues/1022
--INI--
opcache.enable_cli=1
opcache.jit=disable
--SKIPIF--
<?php declare(strict_types=1);
require __DIR__ . '/../../_files/skip-if-requires-code-coverage-driver.php';

if (!function_exists('opcache_compile_file')) {
    echo 'skip, opcache extension is not loaded';
} elseif (ini_get('opcache.enable_cli') !== '1') {
    echo 'skip, opcache not enabled for CLI';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--bootstrap';
$_SERVER['argv'][] = __DIR__ . '/ignore-class-using-attribute/src/CoveredClass.php';
$_SERVER['argv'][] = '--coverage-filter';
$_SERVER['argv'][] = __DIR__ . '/ignore-class-using-attribute/src';
$_SERVER['argv'][] = '--coverage-text';
$_SERVER['argv'][] = __DIR__ . '/ignore-class-using-attribute/tests';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s MB

There was 1 PHPUnit test runner warning:

1) Code coverage might produce unreliable results when OPCache is enabled.

OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Warnings: 1.
%A
