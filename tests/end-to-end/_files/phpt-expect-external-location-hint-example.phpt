--TEST--
PHPT skip condition results in correct code location hint
--FILE--
<?php declare(strict_types=1);
print "Hello world" . \PHP_EOL;
print "This is line 2" . \PHP_EOL;
print "and this is line 3" . \PHP_EOL;
--EXPECT_EXTERNAL--
../_files/expect_external.txt
