--TEST--
PHPT file with a duplicated EXPECT section
--FILE--
<?php declare(strict_types=1);
print 'b';
--EXPECT--
a
--EXPECT--
b
