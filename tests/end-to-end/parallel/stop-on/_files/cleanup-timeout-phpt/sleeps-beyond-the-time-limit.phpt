--TEST--
PHPT fixture whose FILE section is still running when the time limit for the test run is exceeded, for the stop-on-failure-cleanup-timeout-phpt test
--FILE--
<?php declare(strict_types=1);
file_put_contents(sys_get_temp_dir() . '/phpunit-parallel-stop-on-failure-timeout-phpt.marker', "FILE started\n", FILE_APPEND);

sleep(10);

file_put_contents(sys_get_temp_dir() . '/phpunit-parallel-stop-on-failure-timeout-phpt.marker', "FILE finished\n", FILE_APPEND);

print 'ok';
--CLEAN--
<?php declare(strict_types=1);
file_put_contents(sys_get_temp_dir() . '/phpunit-parallel-stop-on-failure-timeout-phpt.marker', "CLEAN\n", FILE_APPEND);
--EXPECT--
ok
