--TEST--
https://github.com/sebastianbergmann/php-code-coverage/issues/1022
--INI--
opcache.enable_cli=1
opcache.jit=disable
--ENV--
XDEBUG_MODE=coverage
--SKIPIF--
<?php declare(strict_types=1);
require __DIR__ . '/../../_files/skip-if-requires-code-coverage-driver.php';

if (!extension_loaded('Zend OPcache')) {
    echo 'skip: opcache extension is not loaded';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--coverage-text';
$_SERVER['argv'][] = '--bootstrap';
$_SERVER['argv'][] = __DIR__.'/src/autoload.php';
$_SERVER['argv'][] = '--coverage-filter';
$_SERVER['argv'][] = __DIR__.'/src/';
$_SERVER['argv'][] = __DIR__.'/tests/GreeterTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s MB

There was 1 PHPUnit test runner warning:

1) Code coverage might produce unreliable results when OPCache is enabled
%A
