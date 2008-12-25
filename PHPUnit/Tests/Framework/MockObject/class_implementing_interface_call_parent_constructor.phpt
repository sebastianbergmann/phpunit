--TEST--
PHPUnit_Framework_MockObject_Generator::generate('Foo', array(), 'MockFoo', TRUE, TRUE)
--FILE--
<?php
interface IFoo
{
    public function __construct($bar);
}

class Foo implements IFoo
{
    public function __construct($bar)
    {
    }
}

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/Framework/MockObject/Generator.php';

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
class MockFoo extends Foo
{
    public $invocationMocker;

    public function __construct($bar)
    {
        $args = func_get_args();

        $this->invocationMocker = new PHPUnit_Framework_MockObject_InvocationMocker($this);

        $class = new ReflectionClass('MockFoo');
        $class->getParentClass()->getConstructor()->invokeArgs($this, $args);
    }

    public function __clone()
    {
        $this->invocationMocker = clone $this->invocationMocker;
    }

    public function expects(PHPUnit_Framework_MockObject_Matcher_Invocation $matcher)
    {
        return $this->invocationMocker->expects($matcher);
    }

    public function __phpunit_getInvocationMocker()
    {
        return $this->invocationMocker;
    }

    public function __phpunit_verify()
    {
        $this->invocationMocker->verify();
    }
}

