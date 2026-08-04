--TEST--
EXPECTREGEX that matches a substring of the output only
--FILE--
<?php declare(strict_types=1);
print 'prefix match 123 suffix';
--EXPECTREGEX--
match [0-9]+
