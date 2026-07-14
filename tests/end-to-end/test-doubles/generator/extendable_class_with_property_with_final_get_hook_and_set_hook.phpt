--TEST--
Extendable class with property with final get property hook and non-final set property hook
--FILE--
<?php declare(strict_types=1);
class Foo
{
    public string $bar {
        final get {
            return $this->bar;
        }

        set (string $value) {
            $this->bar = $value;
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

    public string $bar {
        set (string $value) {
            $this->__phpunit_getInvocationHandler()->invoke(
                new \PHPUnit\Framework\MockObject\Invocation(
                    'TestStubFoo', '$bar::set', [$value], 'void', $this
                )
            );
        }
    }
}
