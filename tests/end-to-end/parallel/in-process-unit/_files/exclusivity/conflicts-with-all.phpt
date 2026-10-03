--TEST--
PHPT fixture that conflicts with every other test and records when it ran
--CONFLICTS--
all
--FILE--
<?php declare(strict_types=1);
$start = microtime(true);

usleep(500000);

file_put_contents(getenv('PHPUNIT_TEST_INTERVALS') . '.phpt', $start . ' ' . microtime(true));

print 'ok';
--EXPECT--
ok
