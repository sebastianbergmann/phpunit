--TEST--
PHPT runner passes arguments that start with a dash to the script when the test has a STDIN section
--ARGS--
--option -o
--STDIN--
Hello World
--FILE--
<?php declare(strict_types=1);
print $argv[1] . ' ' . $argv[2] . ' ' . file_get_contents('php://stdin');
--EXPECT--
--option -o Hello World
