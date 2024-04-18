--TEST--
PHPT runner should support ENV section
--ENV--
FOO=bar
--FILE--
<?php declare(strict_types=1);
print $_SERVER['FOO'];
--EXPECT--
bar
