--TEST--
PHPT test that fails
--FILE--
<?php declare(strict_types=1);
print 'not ok';
--EXPECT--
ok
