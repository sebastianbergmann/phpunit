--TEST--
PHPT fixture whose SKIPIF section sleeps, for the stop-on-clean test
--INI--
memory_limit=-1
--SKIPIF--
<?php declare(strict_types=1);
sleep(3);
--FILE--
<?php declare(strict_types=1);
file_put_contents(
    sys_get_temp_dir() . '/phpunit-parallel-stop-on-clean.file-ran',
    'the FILE section ran',
);

print 'ok';
--EXPECT--
ok
