--TEST--
PHPT fixture that sleeps and then writes a marker file, for the stop-on-failure tests
--FILE--
<?php declare(strict_types=1);
sleep(3);

file_put_contents(
    sys_get_temp_dir() . '/phpunit-parallel-stop-on-failure.marker',
    'the run was not stopped early',
);

print 'ok';
--EXPECT--
ok
