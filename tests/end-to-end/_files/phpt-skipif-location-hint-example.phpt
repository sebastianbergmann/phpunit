--TEST--
PHPT skip condition results in correct code location hint
--FILE--
<?php
print "Nothing to see here, move along";
?>
--SKIPIF--
<?php
// Force skip by writing 'skip:' to STDOUT at the start a line
print "skip: something terrible happened\n";
?>
--EXPECT--
Nothing to see here, move along
