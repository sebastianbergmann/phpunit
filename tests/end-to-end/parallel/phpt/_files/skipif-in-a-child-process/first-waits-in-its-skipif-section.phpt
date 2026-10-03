--TEST--
PHPT fixture whose SKIPIF section, which runs in a child process, waits for a file that the FILE section of the other PHPT fixture creates
--INI--
display_errors=1
--SKIPIF--
<?php declare(strict_types=1);
$file = getenv('PHPUNIT_TEST_HANDSHAKE') . '.created';

$deadline = microtime(true) + 10;

while (!is_file($file) && microtime(true) < $deadline) {
    usleep(10000);

    clearstatcache();
}

if (!is_file($file)) {
    print 'skip: the other PHPT test did not run while this SKIPIF section ran';
}
--FILE--
<?php declare(strict_types=1);
print 'ok';
--EXPECT--
ok
