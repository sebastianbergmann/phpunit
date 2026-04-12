--TEST--
PHPT with --SKIPIF-- but without standard output side-effect is risky
--SKIPIF--
<?php declare(strict_types=1);
if (1 == 2) {
   exit(1);
}
--FILE--
<?php declare(strict_types=1);
echo "Hello, World!\n";
--EXPECT--
Hello, World!
