--TEST--
A PHPT file is not loaded as a PHP file in order to index it
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/_files/setup.php';

$directory = setUpTestFiles();

writePhptFile($directory, 'PhptTest');

// The first run builds the index
print resultFor($directory);

// The second run has the index
print resultFor($directory);

tearDownTestFiles($directory);
--EXPECT--
OK (3 tests, 3 assertions)
OK (3 tests, 3 assertions)
