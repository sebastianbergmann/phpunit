--TEST--
PHPT skip condition with IO operations run in main process
--SKIPIF--
<?php declare(strict_types=1);
file_put_contents(__DIR__ . '/skipif-io.log', 'some content');
unlink(__DIR__ . '/skipif-io.log');
--FILE--
<?php declare(strict_types=1);
echo "Hello, World!\n";
--EXPECT--
Hello, World!
