--TEST--
phpunit --check-php-configuration (success, Xdebug not loaded)
--SKIPIF--
<?php declare(strict_types=1);
if (extension_loaded('xdebug')) {
    print 'skip: Extension Xdebug must not be loaded.';
}
--INI--
display_errors=1
display_startup_errors=1
error_reporting=-1
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

display_errors = On         ... ok
display_startup_errors = On ... ok
error_reporting = -1        ... ok
zend.assertions = 1         ... ok
assert.exception = 1        ... ok
memory_limit = -1           ... ok
