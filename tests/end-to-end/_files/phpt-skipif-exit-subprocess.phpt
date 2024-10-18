--TEST--
PHPT skip condition which exits the subprocess (side-effect)
--FILE--
<?php declare(strict_types=1);
--SKIPIF--
<?php declare(strict_types=1);
echo "skip this test\n";
exit(1);
--EXPECT--
