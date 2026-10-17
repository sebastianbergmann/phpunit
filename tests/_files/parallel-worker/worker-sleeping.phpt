--TEST--
PHPT fixture that sleeps, for the tests of the halted PhptRunner
--FILE--
<?php declare(strict_types=1);
sleep(5);

print 'ok';
--EXPECT--
ok
