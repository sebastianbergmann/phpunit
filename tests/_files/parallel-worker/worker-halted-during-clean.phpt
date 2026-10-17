--TEST--
PHPT fixture whose CLEAN section is still running when the PhptRunner is asked to halt, and writes a marker file when it starts and when it finishes
--FILE--
<?php declare(strict_types=1);
print 'ok';
--CLEAN--
<?php declare(strict_types=1);
file_put_contents(sys_get_temp_dir() . '/phpunit-parallel-halted-during-clean.marker', "started\n", FILE_APPEND);

usleep(300000);

file_put_contents(sys_get_temp_dir() . '/phpunit-parallel-halted-during-clean.marker', "finished\n", FILE_APPEND);
--EXPECT--
ok
