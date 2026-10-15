--TEST--
PHPT test that errors because it has a section that is not supported
--REDIRECTTEST--
return [];
--FILE--
<?php declare(strict_types=1);
print 'ok';
--EXPECT--
ok
