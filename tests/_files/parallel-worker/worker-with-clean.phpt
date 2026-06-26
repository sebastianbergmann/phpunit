--TEST--
A PHPT test with INI and CLEAN sections executed by a parallel worker
--INI--
display_errors=1
--FILE--
<?php declare(strict_types=1);
print 'the phpt test with a clean section ran in a worker';
--CLEAN--
<?php declare(strict_types=1);
print 'cleaned up';
--EXPECT--
the phpt test with a clean section ran in a worker
