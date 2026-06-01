--TEST--
SKIPIF outputs 'skip' without a message
--SKIPIF--
<?php echo 'skip';
--FILE--
<?php echo 'never';
--EXPECT--
never
