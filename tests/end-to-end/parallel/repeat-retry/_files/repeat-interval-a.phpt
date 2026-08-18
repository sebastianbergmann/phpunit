--TEST--
PHPT fixture that records the interval of each of its runs, for the concurrency test of the repeated PHPT tests (a)
--FILE--
<?php declare(strict_types=1);
$start = microtime(true);

usleep(300000);

file_put_contents(
    sys_get_temp_dir() . '/phpunit-parallel-repeat-interval-a.intervals',
    $start . ' ' . microtime(true) . PHP_EOL,
    FILE_APPEND,
);

print 'ok';
--EXPECT--
ok
