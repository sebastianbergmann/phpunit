--TEST--
PHPUnit_Framework_MockObject_Generator::generate('NS\Foo', array(), 'MockFoo', TRUE, TRUE)
--FILE--
<?php
namespace NS;

class Foo
{
    public function bar(Bar $foo)
    {
    }

    public function baz(Bar $foo)
    {
    }
}

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/Framework.php';

$mock = \PHPUnit_Framework_MockObject_Generator::generate(
  'NS\Foo',
  array(),
  'MockFoo',
  TRUE,
  TRUE
);

print $mock['code'];
?>
--EXPECTF--
class MockFoo extends NS\Foo implements PHPUnit_Framework_MockObject_MockObject
{
    protected static $staticInvocationMocker;
    protected $invocationMocker;

    public function __clone()
    {
        $this->invocationMocker = clone $this->__phpunit_getInvocationMocker();
    }

    public function bar(Bar $foo)
    {
        $args = func_get_args();

        $result = $this->__phpunit_getInvocationMocker()->invoke(
          new PHPUnit_Framework_MockObject_Invocation_Object(
            'NS\Foo', 'bar', $args, $this
          )
        );

        return $result;
    }

    public function baz(Bar $foo)
    {
        $args = func_get_args();

        $result = $this->__phpunit_getInvocationMocker()->invoke(
          new PHPUnit_Framework_MockObject_Invocation_Object(
            'NS\Foo', 'baz', $args, $this
          )
        );

        return $result;
    }

    public function expects(PHPUnit_Framework_MockObject_Matcher_Invocation $matcher)
    {
        return $this->__phpunit_getInvocationMocker()->expects($matcher);
    }

    public static function staticExpects(PHPUnit_Framework_MockObject_Matcher_Invocation $matcher)
    {
        return self::__phpunit_getStaticInvocationMocker()->expects($matcher);
    }

    public function __phpunit_getInvocationMocker()
    {
        if ($this->invocationMocker === NULL) {
            $this->invocationMocker = new PHPUnit_Framework_MockObject_InvocationMocker($this);
        }

        return $this->invocationMocker;
    }

    public static function __phpunit_getStaticInvocationMocker()
    {
        if (self::$staticInvocationMocker === NULL) {
            self::$staticInvocationMocker = new PHPUnit_Framework_MockObject_InvocationMocker;
        }

        return self::$staticInvocationMocker;
    }

    public function __phpunit_verify()
    {
        self::__phpunit_getStaticInvocationMocker()->verify();
        $this->__phpunit_getInvocationMocker()->verify();
    }
}
