--TEST--
PHPT skip condition with require() runs in subprocess
--SKIPIF--
<?php declare(strict_types=1);
require (__DIR__ . '/phpt-skipif-required-file.php');
--FILE--
<?php declare(strict_types=1);
echo "Hello, World!\n";
--EXPECT--
Hello, World!
