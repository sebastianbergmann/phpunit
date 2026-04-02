--TEST--
ARGS support
--ARGS--
hello
--FILE--
<?php echo $argv[1];
--EXPECT--
hello
