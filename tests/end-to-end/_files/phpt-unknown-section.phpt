--TEST--
PHPT file with a misspelled SKIPIF section
--SKIPFI--
<?php declare(strict_types=1);
print 'skip always';
--FILE--
<?php declare(strict_types=1);
print '*';
--EXPECT--
*
