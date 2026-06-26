This line before the first section makes the PHPT file invalid.
--TEST--
PHPT that errors on every attempt
--FILE--
<?php declare(strict_types=1);
print 'OK';
--EXPECT--
OK
