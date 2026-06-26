--TEST--
PHPT that always fails (first)
--FILE--
<?php declare(strict_types=1);
print 'FAIL';
--EXPECT--
OK
