--TEST--
PHPT runner supports STDIN section
--STDIN--
Hello World
--FILE--
<?php
$input = \file_get_contents('php://stdin');
print $input;
?>
--EXPECT--
Hello World
