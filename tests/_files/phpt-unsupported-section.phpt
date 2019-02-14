--TEST--
PHPT runner handles unsupported --SECTION-- gracefully
--FILE--
<?php
echo "Hello world";
?>
--GET--
Gerste, Hopfen und Wasser
--EXPECT--
Hello world
