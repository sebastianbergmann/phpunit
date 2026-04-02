--TEST--
EXPECTREGEX failure
--FILE--
<?php echo 'this does not match';
--EXPECTREGEX--
^completely different pattern [0-9]+$
