--TEST--
PHPUnit_Framework_MockObject_Generator::generate('Foo', array(), 'MockFoo', TRUE, TRUE)
--FILE--
<?php
interface Foo
{
    public function bar(Foo $foo);
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
class MockFoo implements Foo
{
    public $invocationMocker;

    public function __clone()
    {
        $this->invocationMocker = clone $this->invocationMocker;
    }

    public function bar(Foo $foo)
    {
        $args = func_get_args();

        $result = $this->invocationMocker->invoke(
          new PHPUnit_Framework_MockObject_Invocation(
            $this, 'Foo', 'bar', $args
          )
        );

        return $result;
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
