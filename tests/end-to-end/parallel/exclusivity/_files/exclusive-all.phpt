--TEST--
PHPT fixture that conflicts with every other test, for the exclusivity test
--CONFLICTS--
all
--FILE--
<?php declare(strict_types=1);
$start = microtime(true);

usleep(100000);

file_put_contents(
    sys_get_temp_dir() . '/phpunit-parallel-exclusivity-phpt.interval',
    $start . ' ' . microtime(true),
);

print 'ok';
--EXPECT--
ok
