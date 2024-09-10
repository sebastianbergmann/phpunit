--TEST--
Interface with property with get and set property hooks
--SKIPIF--
<?php declare(strict_types=1);
if (!method_exists(ReflectionProperty::class, 'getHooks')) {
    print 'skip: PHP 8.4 is required.';
}
--FILE--
<?php declare(strict_types=1);
interface Foo
{
    public string $bar { get; set; }
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
        get {
        }

        set (string $value) {
        }
    }
}
