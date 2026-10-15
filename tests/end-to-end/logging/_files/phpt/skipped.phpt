--TEST--
PHPT test that is skipped
--SKIPIF--
<?php declare(strict_types=1);
print 'skip: message';
--FILE--
<?php declare(strict_types=1);
print 'ok';
--EXPECT--
ok
