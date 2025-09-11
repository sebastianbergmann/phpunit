--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6105
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('xdebug')) {
    print 'skip: Xdebug is not loaded';
}

if (!in_array('develop', xdebug_info('mode'), true) &&
    !in_array('debug', xdebug_info('mode'), true) &&
    !in_array('coverage', xdebug_info('mode'), true)) {
    print 'skip: Xdebug mode must include develop, debug, or coverage';
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
