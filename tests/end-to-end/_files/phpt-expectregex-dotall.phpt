--TEST--
EXPECTREGEX where a dot must match a newline
--FILE--
<?php declare(strict_types=1);
print "first\nsecond";
--EXPECTREGEX--
first.second
