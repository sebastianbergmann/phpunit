--TEST--
Interface with property with set property hook
--SKIPIF--
<?php declare(strict_types=1);
if (!method_exists(ReflectionProperty::class, 'isFinal')) {
    print 'skip: PHP 8.4 is required.';
}
--FILE--
<?php declare(strict_types=1);
interface Foo
{
    public string $bar { set; }
}

require_once __DIR__ . '/../../../bootstrap.php';

$generator = new \PHPUnit\Framework\MockObject\Generator\Generator;

$testDoubleClass = $generator->generate(
    Foo::class,
    false,
    false,
    [],
    'TestStubFoo',
);

print $testDoubleClass->classCode();
--EXPECTF--
declare(strict_types=1);

class TestStubFoo implements PHPUnit\Framework\MockObject\StubInternal, Foo
{
    use PHPUnit\Framework\MockObject\StubApi;
    use PHPUnit\Framework\MockObject\GeneratedAsTestStub;
    use PHPUnit\Framework\MockObject\Method;
    use PHPUnit\Framework\MockObject\DoubledCloneMethod;

    public string $bar {
        set (string $value) {
            $this->__phpunit_getInvocationHandler()->invoke(
                new \PHPUnit\Framework\MockObject\Invocation(
                    'TestStubFoo', '$bar::set', [$value], 'void', $this, false
                )
            );
        }
    }
}
