--TEST--
phpunit --check-php-configuration (failure, Xdebug loaded)
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('xdebug')) {
    print 'skip: Extension Xdebug must be loaded.';
}
--INI--
display_errors=0
display_startup_errors=1
error_reporting=-1
xdebug.show_exception_trace=0
zend.assertions=1
assert.exception=1
memory_limit=-1
--FILE--
<?php
$_SERVER['argv'][] = '--check-php-configuration';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Checking whether PHP is configured according to https://docs.phpunit.de/en/%s/installation.html#configuring-php-for-development

display_errors = On             ... not ok (0)
display_startup_errors = On     ... ok
error_reporting = -1            ... ok
xdebug.show_exception_trace = 0 ... ok
zend.assertions = 1             ... ok
assert.exception = 1            ... ok
memory_limit = -1               ... ok
