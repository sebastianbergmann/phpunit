--TEST--
PHPT fixture whose FILE section is still running when the PhptRunner is asked to halt, and whose FILE and CLEAN sections write a marker file
--FILE--
<?php declare(strict_types=1);
usleep(300000);

file_put_contents(sys_get_temp_dir() . '/phpunit-parallel-halted-phpt.marker', "FILE\n", FILE_APPEND);

print 'ok';
--CLEAN--
<?php declare(strict_types=1);
file_put_contents(sys_get_temp_dir() . '/phpunit-parallel-halted-phpt.marker', "CLEAN\n", FILE_APPEND);
--EXPECT--
ok
