--TEST--
A PHPT test with no --CONFLICTS-- section that may run alongside any other test
--FILE--
<?php declare(strict_types=1);
print 'the plain phpt test ran';
--EXPECT--
the plain phpt test ran
