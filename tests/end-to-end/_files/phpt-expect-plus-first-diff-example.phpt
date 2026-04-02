--TEST--
Actual output has inserted line
--FILE--
<?php
echo "aaa\n";
echo "inserted\n";
echo "bbb";
--EXPECT--
aaa
bbb
