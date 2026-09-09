--TEST--
PHPT fixture that sleeps and has a CLEAN section, for the tests of the halted PhptRunner
--INI--
memory_limit=-1
--FILE--
<?php declare(strict_types=1);
sleep(5);

print 'ok';
--CLEAN--
<?php declare(strict_types=1);
file_put_contents(
    sys_get_temp_dir() . '/phpunit-parallel-halt-clean.marker',
    'the CLEAN section ran',
);
--EXPECT--
ok
