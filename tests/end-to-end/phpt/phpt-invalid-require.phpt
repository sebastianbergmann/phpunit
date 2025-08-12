--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6311
--SKIPIF--
<?php declare(strict_types=1);
require __DIR__ . '/some/non/existing/file.php';

--FILE--
<?php declare(strict_types=1);

echo "hello world\n";
--EXPECT--
hello world
