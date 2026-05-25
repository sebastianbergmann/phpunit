--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6673
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcov')) {
    print 'skip: This test requires the PCOV extension';
}
--INI--
pcov.enabled=1
pcov.directory=the-directory-configured-for-this-test
--FILE--
<?php declare(strict_types=1);
print ini_get('pcov.directory');
--EXPECT--
the-directory-configured-for-this-test
