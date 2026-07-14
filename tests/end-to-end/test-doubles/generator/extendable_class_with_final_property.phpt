--TEST--
Extendable class with property with final property
--FILE--
<?php declare(strict_types=1);
class Foo
{
    public final string $bar {
        get {
            return 'value';
        }
    }
}

require_once __DIR__ . '/../../../bootstrap.php';

$generator = new \PHPUnit\Framework\MockObject\Generator\Generator;

$testDoubleClass = $generator->generate(
    type: Foo::class,
    mockObject: false,
    methods: [],
    mockClassName: 'TestStubFoo',
);

print $testDoubleClass->classCode();
--EXPECT--
declare(strict_types=1);

class TestStubFoo extends Foo implements PHPUnit\Framework\MockObject\StubInternal
{
    use PHPUnit\Framework\MockObject\StubApi;
    use PHPUnit\Framework\MockObject\Method;
    use PHPUnit\Framework\MockObject\DoubledCloneMethod;
}
