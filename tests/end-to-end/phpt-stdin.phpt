--TEST--
PHPT runner supports STDIN section
--STDIN--
Hello World
--FILE--
<?php declare(strict_types=1);
$input = \file_get_contents('php://stdin');
print $input;
?>
--EXPECT--
Hello World
