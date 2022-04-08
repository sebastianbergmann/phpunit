--TEST--
Can parse --INI-- and --ENV-- sections
--SKIPIF--
<?php declare(strict_types=1);
if (extension_loaded('xdebug')) {
    print 'skip: Extension xdebug must not be loaded.';
}
--INI--
date.default_latitude=1.337
dummy.key
--ENV--
PHPUNIT_ENV=phpunit-env-section
--FILE--
<?php
var_dump(ini_get('date.default_latitude'));
var_dump(ini_get('dummy.key'));
var_dump(getenv('PHPUNIT_ENV'));
?>
--EXPECT--
string(5) "1.337"
bool(false)
string(19) "phpunit-env-section"
