--TEST--
PHPT uses a subprocess when --INI-- is present, even if --SKIPIF-- has no side-effect
--INI--
error_reporting=-1
--SKIPIF--
<?php declare(strict_types=1);
if (1 == 2) {
   echo "skip this test\n";
}
--FILE--
<?php declare(strict_types=1);
echo "Hello, World!\n";
--EXPECT--
Hello, World!
