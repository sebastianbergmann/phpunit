--TEST--
PHPT test with a CLEAN section which pollutes process scope
--FILE--
<?php declare(strict_types=1);
print 'success';
--EXPECT--
success
--CLEAN--
<?php declare(strict_types=1);
exit(1);
