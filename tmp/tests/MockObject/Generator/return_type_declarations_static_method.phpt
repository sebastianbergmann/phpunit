--TEST--
PHPUnit_Framework_MockObject_Generator::generate('Foo', array(), 'MockFoo', true, true)
--SKIPIF--
<?php
if (!method_exists('ReflectionMethod', 'getReturnType')) print 'skip: PHP >= 7.0.0 required';
?>
--FILE--
<?php
class Foo
{
    public static function bar(string $baz): Bar
    {
        return 'test';
    }
}

require __DIR__ . '/../../../vendor/autoload.php';

$generator = new PHPUnit_Framework_MockObject_Generator;

$mock = $generator->generate(
    'Foo',
    array(),
    'MockFoo',
    true,
    true
);

print $mock['code'];
?>
--EXPECTF--
class MockFoo extends Foo implements PHPUnit_Framework_MockObject_MockObject
{
    private $__phpunit_invocationMocker;
    private $__phpunit_originalObject;
    private $__phpunit_configurable = ['bar'];

    public function __clone()
    {
        $this->__phpunit_invocationMocker = clone $this->__phpunit_getInvocationMocker();
    }

    public static function bar(string $baz): Bar
    {
        throw new PHPUnit_Framework_MockObject_BadMethodCallException('Static method "bar" cannot be invoked on mock object');
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
