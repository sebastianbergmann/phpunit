--TEST--
PHPUnit_Framework_MockObject_Generator::generate('Foo', array(), 'MockFoo', TRUE, FALSE)
--FILE--
<?php
class Foo
{
    public function __clone()
    {
    }
}

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/Framework/MockObject/Generator.php';

$mock = PHPUnit_Framework_MockObject_Generator::generate(
  'Foo',
  array(),
  'MockFoo',
  TRUE,
  FALSE
);

print $mock['code'];
?>
--EXPECTF--
class MockFoo extends Foo
{
    public static $staticInvocationMocker;
    public $invocationMocker;

    public function __clone()
    {
        $this->invocationMocker = clone $this->invocationMocker;
    }

    public function expects(PHPUnit_Framework_MockObject_Matcher_Invocation $matcher)
    {
        return $this->invocationMocker->expects($matcher);
    }

    public static function staticExpects(PHPUnit_Framework_MockObject_Matcher_Invocation $matcher)
    {
        return self::$staticInvocationMocker->expects($matcher);
    }

    public function __phpunit_getInvocationMocker()
    {
        return $this->invocationMocker;
    }

    public function __phpunit_verify()
    {
        self::$staticInvocationMocker->verify();
        $this->invocationMocker->verify();
    }
}

MockFoo::$staticInvocationMocker = new PHPUnit_Framework_MockObject_InvocationMocker;
