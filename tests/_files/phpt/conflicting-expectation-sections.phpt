--TEST--
PHPT file with both an EXPECT and an EXPECTF section
--FILE--
<?php declare(strict_types=1);
print '*';
--EXPECT--
*
--EXPECTF--
%s
