--TEST--
PHPUnit_Framework_MockObject_Generator::generate('Foo', array(), 'MockFoo', TRUE, TRUE)
--FILE--
<?php
class Foo
{
    public function bar(Foo $foo)
    {
    }

    public function baz(Foo $foo)
    {
    }
}

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/Framework.php';

$mock = PHPUnit_Framework_MockObject_Generator::generate(
  'Foo',
  array(),
  'MockFoo',
  TRUE,
  TRUE
);

print $mock['code'];
?>
--EXPECTF--
class MockFoo extends Foo implements PHPUnit_Framework_MockObject_MockObject
{
    protected $invocationMocker;

    public function __clone()
    {
        $this->invocationMocker = clone $this->__phpunit_getInvocationMocker();
    }

    public function bar(Foo $foo)
    {
        $args = func_get_args();

        $result = $this->__phpunit_getInvocationMocker()->invoke(
          new PHPUnit_Framework_MockObject_Invocation(
            $this, 'Foo', 'bar', $args
          )
        );

        return $result;
    }

    public function baz(Foo $foo)
    {
        $args = func_get_args();

        $result = $this->__phpunit_getInvocationMocker()->invoke(
          new PHPUnit_Framework_MockObject_Invocation(
            $this, 'Foo', 'baz', $args
          )
        );

        return $result;
    }

    public function expects(PHPUnit_Framework_MockObject_Matcher_Invocation $matcher)
    {
        return $this->__phpunit_getInvocationMocker()->expects($matcher);
    }

    public function __phpunit_getInvocationMocker()
    {
        if ($this->invocationMocker === NULL) {
            $this->invocationMocker = new PHPUnit_Framework_MockObject_InvocationMocker($this);
        }

        return $this->invocationMocker;
    }

    public function __phpunit_verify()
    {
        $this->__phpunit_getInvocationMocker()->verify();
    }
}
