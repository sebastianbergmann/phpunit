--TEST--
PHPUnit_Framework_MockObject_Generator::generate('NS\Foo', array(), 'MockFoo', TRUE)
--SKIPIF--
<?php
if (!version_compare(PHP_VERSION, '5.3.0', '>=')) echo 'skip: PHP 5.3 only';
?>
--FILE--
<?php
namespace NS;

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

require_once 'PHPUnit/Autoload.php';
require_once 'Text/Template.php';

$mock = \PHPUnit_Framework_MockObject_Generator::generate(
  'NS\Foo',
  array(),
  'MockFoo',
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
            $this->invocationMocker = new PHPUnit_Framework_MockObject_InvocationMocker;
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

    public function __phpunit_hasMatchers()
    {
        return self::__phpunit_getStaticInvocationMocker()->hasMatchers() ||
               $this->__phpunit_getInvocationMocker()->hasMatchers();
    }

    public function __phpunit_verify()
    {
        self::__phpunit_getStaticInvocationMocker()->verify();
        $this->__phpunit_getInvocationMocker()->verify();
    }

    public function __phpunit_cleanup()
    {
        self::$staticInvocationMocker = NULL;
        $this->invocationMocker       = NULL;
    }
}

