--TEST--
PHPUnit_Framework_MockObject_Generator::generate('NonExistentClass', array(), 'MockFoo', true, true)
--FILE--
<?php
require __DIR__ . '/../../vendor/autoload.php';

$generator = new PHPUnit_Framework_MockObject_Generator;

$mock = $generator->generate(
    'NonExistentClass',
    array(),
    'MockFoo',
    true,
    true
);

print $mock['code'];
?>
--EXPECTF--
class NonExistentClass
{
}

class MockFoo extends NonExistentClass implements PHPUnit_Framework_MockObject_MockObject
{
    private $__phpunit_invocationMocker;
    private $__phpunit_originalObject;
    private $__phpunit_unsetInvocationMocker = true;

    public function __clone()
    {
        $this->__phpunit_invocationMocker = clone $this->__phpunit_getInvocationMocker();
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
            $this->__phpunit_invocationMocker = new PHPUnit_Framework_MockObject_InvocationMocker;
        }

        return $this->__phpunit_invocationMocker;
    }

    public function __phpunit_hasMatchers()
    {
        return $this->__phpunit_getInvocationMocker()->hasMatchers();
    }

    public function __phpunit_unsetInvocationMocker($flag)
    {
        $this->__phpunit_unsetInvocationMocker = $flag;
    }

    public function __phpunit_verify()
    {
        $this->__phpunit_getInvocationMocker()->verify();

        if ($this->__phpunit_unsetInvocationMocker) {
            $this->__phpunit_invocationMocker = null;
        }
    }
}
