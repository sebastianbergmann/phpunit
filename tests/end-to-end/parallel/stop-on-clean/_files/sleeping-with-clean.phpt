--TEST--
PHPT fixture whose FILE section leaves state behind that its CLEAN section cleans up, for the stop-on-clean test
--FILE--
<?php declare(strict_types=1);
file_put_contents(
    sys_get_temp_dir() . '/phpunit-parallel-stop-on-clean.dirty',
    'state the FILE section left behind',
);

sleep(3);

print 'ok';
--CLEAN--
<?php declare(strict_types=1);
@unlink(sys_get_temp_dir() . '/phpunit-parallel-stop-on-clean.dirty');

file_put_contents(
    sys_get_temp_dir() . '/phpunit-parallel-stop-on-clean.cleaned',
    'the CLEAN section ran',
);
--EXPECT--
ok
