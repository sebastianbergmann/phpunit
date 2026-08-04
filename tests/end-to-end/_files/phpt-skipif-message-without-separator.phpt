--TEST--
SKIPIF message without separator between skip and message
--SKIPIF--
<?php declare(strict_types=1);
print 'skip only for demonstration purposes';
--FILE--
<?php declare(strict_types=1);
print '*';
--EXPECT--
*
