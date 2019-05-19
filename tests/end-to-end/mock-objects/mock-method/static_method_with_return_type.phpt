--TEST--
Mock static method
--FILE--
<?php declare(strict_types=1);
class Foo
{
    public static function bar(): bool{}
}

require __DIR__ . '/../../../../vendor/autoload.php';

$class = new ReflectionClass('Foo');
$mockMethod = \PHPUnit\Framework\MockObject\MockMethod::fromReflection(
    $class->getMethod('bar'),
    false,
    false
);

$code = $mockMethod->generateCode();

print $code;
--EXPECT--

public static function bar(): bool
    {
        throw new \PHPUnit\Framework\MockObject\BadMethodCallException('Static method "bar" cannot be invoked on mock object');
    }
