--TEST--
PHPT fixture that takes noticeably longer than the other fixtures, for the tests of the PhptRunner's start order
--FILE--
<?php declare(strict_types=1);
usleep(300000);

print 'ok';
--EXPECT--
ok
