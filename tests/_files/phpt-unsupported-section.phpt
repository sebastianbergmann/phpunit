--TEST--
PHPT runner handles unsupported --SECTION-- gracefully
--FILE--
<?php declare(strict_types=1);
echo "Hello world";
--GET--
Gerste, Hopfen und Wasser
--EXPECT--
Hello world
