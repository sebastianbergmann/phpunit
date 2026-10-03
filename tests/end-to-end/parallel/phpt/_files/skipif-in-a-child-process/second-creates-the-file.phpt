--TEST--
PHPT fixture whose FILE section creates the file that the SKIPIF section of the other PHPT fixture waits for
--INI--
display_errors=1
--SKIPIF--
<?php declare(strict_types=1);
if (getenv('PHPUNIT_TEST_HANDSHAKE') === false) {
    print 'skip: PHPUNIT_TEST_HANDSHAKE is not set';
}
--FILE--
<?php declare(strict_types=1);
file_put_contents(getenv('PHPUNIT_TEST_HANDSHAKE') . '.created', '');

print 'ok';
--EXPECT--
ok
