--TEST--
phpunit --warn-when-php-is-not-configured-for-development
--INI--
display_errors=0
display_startup_errors=1
error_reporting=-1
xdebug.show_exception_trace=0
zend.assertions=1
assert.exception=1
memory_limit=-1
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--warn-when-php-is-not-configured-for-development';
$_SERVER['argv'][] = __DIR__ . '/../_files/basic/SuccessTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) PHP is not configured for development: display_errors should be On, but is 0

OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Warnings: 1.
