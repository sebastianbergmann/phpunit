--TEST--
\PHPUnit\Framework\MockObject\Generator::generate('Bar', [], 'MockBar', true, true)
--FILE--
<?php
abstract class Foo
{
    abstract public function baz();
}

class Bar extends Foo
{
    public function baz(): parent
    {
    }
}

require __DIR__ . '/../../vendor/autoload.php';

$generator = new \PHPUnit\Framework\MockObject\Generator;

$mock = $generator->generate(
    'Bar',
    [],
    'MockBar',
    true,
    true
);

print $mock['code'];
--EXPECT--
class MockBar extends Bar implements PHPUnit\Framework\MockObject\MockObject
{
    private $__phpunit_invocationMocker;
    private $__phpunit_originalObject;
    private $__phpunit_configurable = ['baz'];
    private $__phpunit_returnValueGeneration = true;

    public function __clone()
    {
        $this->__phpunit_invocationMocker = clone $this->__phpunit_getInvocationMocker();
    }

    public function baz(): Foo
    {
        $arguments = [];
        $count     = func_num_args();

        if ($count > 0) {
            $_arguments = func_get_args();

            for ($i = 0; $i < $count; $i++) {
                $arguments[] = $_arguments[$i];
            }
        }

        $result = $this->__phpunit_getInvocationMocker()->invoke(
            new \PHPUnit\Framework\MockObject\Invocation\ObjectInvocation(
                'Bar', 'baz', $arguments, 'Foo', $this, true
            )
        );

        return $result;
    }

    public function expects(\PHPUnit\Framework\MockObject\Matcher\Invocation $matcher)
    {
        return $this->__phpunit_getInvocationMocker()->expects($matcher);
    }

    public function method()
    {
        $any     = new \PHPUnit\Framework\MockObject\Matcher\AnyInvokedCount;
        $expects = $this->expects($any);

        return call_user_func_array([$expects, 'method'], func_get_args());
    }

    public function __phpunit_setOriginalObject($originalObject)
    {
        $this->__phpunit_originalObject = $originalObject;
    }

    public function __phpunit_setReturnValueGeneration(bool $returnValueGeneration)
    {
        $this->__phpunit_returnValueGeneration = $returnValueGeneration;
    }

    public function __phpunit_getInvocationMocker()
    {
        if ($this->__phpunit_invocationMocker === null) {
            $this->__phpunit_invocationMocker = new \PHPUnit\Framework\MockObject\InvocationMocker($this->__phpunit_configurable, $this->__phpunit_returnValueGeneration);
        }

        return $this->__phpunit_invocationMocker;
    }

    public function __phpunit_hasMatchers()
    {
        return $this->__phpunit_getInvocationMocker()->hasMatchers();
    }

    public function __phpunit_verify($unsetInvocationMocker = true)
    {
        $this->__phpunit_getInvocationMocker()->verify();

        if ($unsetInvocationMocker) {
            $this->__phpunit_invocationMocker = null;
        }
    }
}
