--TEST--
PHPT test that is expected to fail
--FILE--
<?php declare(strict_types=1);
print 'not ok';
--XFAIL--
This test is expected to fail
--EXPECT--
ok
