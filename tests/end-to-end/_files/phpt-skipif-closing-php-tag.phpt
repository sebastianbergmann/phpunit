--TEST--
PHPT skip condition which is terminated with a closing PHP tag
--FILE--
<?php declare(strict_types=1);
print "Nothing to see here, move along";
--SKIPIF--
<?php declare(strict_types=1);
print "skip: something terrible happened\n";
?>
--EXPECT--
