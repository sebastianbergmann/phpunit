--TEST--
PHPT skip condition results in correct code location hint
--FILE--
<?php
print "Nothing to see here, move along";
?>
--SKIPIF--
<?php
print "skip: something terrible happened\n";
?>
--EXPECT--
Nothing to see here, move along
