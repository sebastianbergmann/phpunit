--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6105
--SKIPIF--
<?php if(!extension_loaded('xdebug')) {
    print 'skip: xdebug is not loaded';
}

if (ini_get('xdebug.start_with_request') === "1") {
    print 'skip: xdebug emits a warning when xdebug.start_with_request=1 which breaks output expectations';
}

$mode = getenv('XDEBUG_MODE');

if ($mode === false || $mode === '') {
    $mode = ini_get('xdebug.mode');
}

if ($mode === '') {
    print 'skip: requires XDEBUG_MODE or xdebug.mode to be different from "off"';
}

--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = __DIR__ . '/6105/IssueTest6105.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

..                                                                  2 / 2 (100%)

Time: %s, Memory: %s

OK (2 tests, 3 assertions)
