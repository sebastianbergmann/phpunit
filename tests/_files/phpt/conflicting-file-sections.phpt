--TEST--
PHPT file with both a FILE and a FILEEOF section
--FILE--
<?php declare(strict_types=1);
print '*';
--FILEEOF--
<?php declare(strict_types=1);
print '*';
--EXPECT--
*
