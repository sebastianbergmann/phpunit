--TEST--
\PHPUnit\Framework\MockObject\Generator::generate('NS\Foo', [], 'MockFoo', true, true)
--FILE--
<?php declare(strict_types=1);
namespace NS;

class Foo
{
    public function bar(Foo $foo)
    {
    }

    public function baz(Foo $foo)
    {
    }
}

require __DIR__ . '/../../../../vendor/autoload.php';

$generator = new \PHPUnit\Framework\MockObject\Generator;

$mock = $generator->generate(
    'NS\Foo',
    [],
    'MockFoo',
    true,
    true
);

print $mock['code'];
?>
--EXPECT--
class MockFoo extends NS\Foo implements PHPUnit\Framework\MockObject\MockObject
{
    private $__phpunit_invocationMocker;
    private $__phpunit_originalObject;
    private $__phpunit_configurable = ['bar', 'baz'];
    private $__phpunit_returnValueGeneration = true;

    public function __clone()
    {
        $this->__phpunit_invocationMocker = clone $this->__phpunit_getInvocationMocker();
    }

    public function bar(NS\Foo $foo)
    {
        $__phpunit_arguments = [$foo];
        $__phpunit_count     = func_num_args();

        if ($__phpunit_count > 1) {
            $__phpunit_arguments_tmp = func_get_args();

            for ($__phpunit_i = 1; $__phpunit_i < $__phpunit_count; $__phpunit_i++) {
                $__phpunit_arguments[] = $__phpunit_arguments_tmp[$__phpunit_i];
            }
        }

        $__phpunit_result = $this->__phpunit_getInvocationMocker()->invoke(
            new \PHPUnit\Framework\MockObject\Invocation\ObjectInvocation(
                'NS\Foo', 'bar', $__phpunit_arguments, '', $this, true
            )
        );

        return $__phpunit_result;
    }

    public function baz(NS\Foo $foo)
    {
        $__phpunit_arguments = [$foo];
        $__phpunit_count     = func_num_args();

        if ($__phpunit_count > 1) {
            $__phpunit_arguments_tmp = func_get_args();

            for ($__phpunit_i = 1; $__phpunit_i < $__phpunit_count; $__phpunit_i++) {
                $__phpunit_arguments[] = $__phpunit_arguments_tmp[$__phpunit_i];
            }
        }

        $__phpunit_result = $this->__phpunit_getInvocationMocker()->invoke(
            new \PHPUnit\Framework\MockObject\Invocation\ObjectInvocation(
                'NS\Foo', 'baz', $__phpunit_arguments, '', $this, true
            )
        );

        return $__phpunit_result;
    }

    public function expects(\PHPUnit\Framework\MockObject\Matcher\Invocation $matcher): \PHPUnit\Framework\MockObject\Builder\InvocationMocker
    {
        return $this->__phpunit_getInvocationMocker()->expects($matcher);
    }

    public function method()
    {
        $any     = new \PHPUnit\Framework\MockObject\Matcher\AnyInvokedCount;
        $expects = $this->expects($any);

        return call_user_func_array([$expects, 'method'], func_get_args());
    }

    public function __phpunit_setOriginalObject($originalObject): void
    {
        $this->__phpunit_originalObject = $originalObject;
    }

    public function __phpunit_setReturnValueGeneration(bool $returnValueGeneration): void
    {
        $this->__phpunit_returnValueGeneration = $returnValueGeneration;
    }

    public function __phpunit_getInvocationMocker(): \PHPUnit\Framework\MockObject\InvocationMocker
    {
        if ($this->__phpunit_invocationMocker === null) {
            $this->__phpunit_invocationMocker = new \PHPUnit\Framework\MockObject\InvocationMocker($this->__phpunit_configurable, $this->__phpunit_returnValueGeneration);
        }

        return $this->__phpunit_invocationMocker;
    }

    public function __phpunit_hasMatchers(): bool
    {
        return $this->__phpunit_getInvocationMocker()->hasMatchers();
    }

    public function __phpunit_verify(bool $unsetInvocationMocker = true): void
    {
        $this->__phpunit_getInvocationMocker()->verify();

        if ($unsetInvocationMocker) {
            $this->__phpunit_invocationMocker = null;
        }
    }
}
