--TEST--
STDIN support
--STDIN--
hello from stdin
--FILE--
<?php echo file_get_contents('php://stdin');
--EXPECT--
hello from stdin
