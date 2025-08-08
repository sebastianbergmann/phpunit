--TEST--
phpunit --check-php-configuration (success, Xdebug not loaded)
--SKIPIF--
<?php declare(strict_types=1);
if (extension_loaded('xdebug')) {
    print 'skip: Extension Xdebug must not be loaded.';
}
--FILE--
<?php
$_SERVER['argv'][] = '--check-php-configuration';

require_once __DIR__ . '/../../bootstrap.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('error_reporting', -1);
ini_set('zend.assertions', 1);
ini_set('assert.exception', 1);
ini_set('memory_limit', -1);

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
