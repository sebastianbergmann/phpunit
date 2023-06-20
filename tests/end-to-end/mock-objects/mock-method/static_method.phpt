--TEST--
Mock static method
--FILE--
<?php declare(strict_types=1);
class Foo
{
    public static function bar(){}
}

require_once __DIR__ . '/../../../bootstrap.php';

$class = new ReflectionClass('Foo');
$mockMethod = \PHPUnit\Framework\MockObject\Generator\MockMethod::fromReflection(
    $class->getMethod('bar'),
    false,
    false
);

$code = $mockMethod->generateCode();

print $code;
--EXPECT--

public static function bar()
    {
        throw new \PHPUnit\Framework\MockObject\BadMethodCallException('Static method "bar" cannot be invoked on mock object');
    }
