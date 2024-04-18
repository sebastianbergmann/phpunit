--TEST--
PHPT runner should support ENV section
--SKIPIF--
<?php declare(strict_types=1);
if (extension_loaded('xdebug')) {
    print 'skip: Extension xdebug must not be loaded.';
}
--ENV--
FOO=bar
--FILE--
<?php declare(strict_types=1);
print $_SERVER['FOO'];
--EXPECT--
bar
