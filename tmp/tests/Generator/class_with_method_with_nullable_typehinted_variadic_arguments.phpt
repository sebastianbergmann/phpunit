--TEST--
PHPUnit_Framework_MockObject_Generator::generate('ClassWithMethodWithVariadicArguments', array(), 'MockFoo', true, true)
--SKIPIF--
<?php
if (!version_compare(PHP_VERSION, '7.1', '>=')) print 'skip: PHP >= 7.1 required';
?>
--FILE--
<?php
class ClassWithMethodWithNullableTypehintedVariadicArguments
{
    public function methodWithNullableTypehintedVariadicArguments($a, ?string ...$parameters)
    {
    }
}

require __DIR__ . '/../../vendor/autoload.php';

$generator = new PHPUnit_Framework_MockObject_Generator;

$mock = $generator->generate(
    'ClassWithMethodWithNullableTypehintedVariadicArguments',
    array(),
    'MockFoo',
    true,
    true
);

print $mock['code'];
?>
--EXPECTF--
class MockFoo extends ClassWithMethodWithNullableTypehintedVariadicArguments implements PHPUnit_Framework_MockObject_MockObject
{
    private $__phpunit_invocationMocker;
    private $__phpunit_originalObject;
    private $__phpunit_configurable = ['methodwithnullabletypehintedvariadicarguments'];

    public function __clone()
    {
        $this->__phpunit_invocationMocker = clone $this->__phpunit_getInvocationMocker();
    }

    public function methodWithNullableTypehintedVariadicArguments($a, ?string ...$parameters)
    {
        $arguments = array($a);
        $count     = func_num_args();

        if ($count > 1) {
            $_arguments = func_get_args();

            for ($i = 1; $i < $count; $i++) {
                $arguments[] = $_arguments[$i];
            }
        }

        $result = $this->__phpunit_getInvocationMocker()->invoke(
            new PHPUnit_Framework_MockObject_Invocation_Object(
                'ClassWithMethodWithNullableTypehintedVariadicArguments', 'methodWithNullableTypehintedVariadicArguments', $arguments, '', $this, true
            )
        );

        return $result;
    }

    public function expects(PHPUnit_Framework_MockObject_Matcher_Invocation $matcher)
    {
        return $this->__phpunit_getInvocationMocker()->expects($matcher);
    }

    public function method()
    {
        $any = new PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount;
        $expects = $this->expects($any);
        return call_user_func_array(array($expects, 'method'), func_get_args());
    }

    public function __phpunit_setOriginalObject($originalObject)
    {
        $this->__phpunit_originalObject = $originalObject;
    }

    public function __phpunit_getInvocationMocker()
    {
        if ($this->__phpunit_invocationMocker === null) {
            $this->__phpunit_invocationMocker = new PHPUnit_Framework_MockObject_InvocationMocker($this->__phpunit_configurable);
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
