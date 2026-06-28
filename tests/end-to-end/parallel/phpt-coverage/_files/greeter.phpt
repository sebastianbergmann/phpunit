--TEST--
A PHPT test that exercises a source file so that code coverage is collected for it
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/src/Greeter.php';

print (new PHPUnit\TestFixture\ParallelPhptCoverage\Greeter)->greet();
--EXPECT--
Hello
