--TEST--
PHPT uses a subprocess when --INI-- is present, even if --CLEAN-- has IO side-effect
--INI--
error_reporting=-1
--FILE--
<?php declare(strict_types=1);
echo "Hello, World!\n";
--EXPECT--
Hello, World!
--CLEAN--
<?php declare(strict_types=1);
@unlink('/some/non/existing/file');
?>
