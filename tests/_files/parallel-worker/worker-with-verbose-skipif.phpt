--TEST--
PHPT fixture whose SKIPIF section writes more output than a pipe holds without skipping the test, for the tests of the PhptRunner
--INI--
display_errors=1
--SKIPIF--
<?php declare(strict_types=1);
print str_repeat('x', 1048576);
--FILE--
<?php declare(strict_types=1);
print 'ok';
--EXPECT--
ok
