--TEST--
The temporary files used for collecting code coverage are not created in this directory
--FILE--
<?php declare(strict_types=1);
use PHPUnit\TestFixture\PhptCodeCoverage\Foo;

$files = array_values(array_diff(scandir(__DIR__), ['.', '..']));

sort($files);

print implode("\n", $files) . "\n";

var_dump((new Foo)->doSomething());
--EXPECT--
FooTest.phpt
bool(true)
