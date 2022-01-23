--TEST--
Greeter
--FILE--
<?php declare(strict_types=1);
require_once __DIR__ . '/../../src/Greeter.php';

use PHPUnit\TestFixture\Phar\Greeter;

print (new Greeter)->greet();
--EXPECT--
Hello world!
