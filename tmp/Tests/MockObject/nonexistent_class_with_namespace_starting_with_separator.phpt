--TEST--
PHPUnit_Framework_MockObject_Generator::generate('Foo', array(), 'MockFoo', TRUE, TRUE)
--FILE--
<?php
require_once 'PHPUnit/Autoload.php';
require_once 'Text/Template.php';

$generator = new PHPUnit_Framework_MockObject_Generator;

$mock = $generator->generate(
  '\NS\Foo',
  array(),
  'MockFoo',
  TRUE,
  TRUE
);

print $mock['code'];
?>
--EXPECTF--
namespace NS {

class Foo
{
}

}

namespace {

class MockFoo extends NS\Foo implements PHPUnit_Framework_MockObject_MockObject
{
    private static $__phpunit_staticInvocationMocker;
    private $__phpunit_invocationMocker;
    private $__phpunit_originalObject;

    public function __clone()
    {
        $this->__phpunit_invocationMocker = clone $this->__phpunit_getInvocationMocker();
    }

    public function expects(PHPUnit_Framework_MockObject_Matcher_Invocation $matcher)
    {
        return $this->__phpunit_getInvocationMocker()->expects($matcher);
    }

    public static function staticExpects(PHPUnit_Framework_MockObject_Matcher_Invocation $matcher)
    {
        PHPUnit_Util_DeprecatedFeature_Logger::log('The stubbing and mocking of static methods is deprecated and will be removed in PHPUnit 3.9.');

        return self::__phpunit_getStaticInvocationMocker()->expects($matcher);
    }

    public function __phpunit_setOriginalObject($originalObject)
    {
        $this->__phpunit_originalObject = $originalObject;
    }

    public function __phpunit_getInvocationMocker()
    {
        if ($this->__phpunit_invocationMocker === NULL) {
            $this->__phpunit_invocationMocker = new PHPUnit_Framework_MockObject_InvocationMocker;
        }

        return $this->__phpunit_invocationMocker;
    }

    public static function __phpunit_getStaticInvocationMocker()
    {
        if (self::$__phpunit_staticInvocationMocker === NULL) {
            self::$__phpunit_staticInvocationMocker = new PHPUnit_Framework_MockObject_InvocationMocker;
        }

        return self::$__phpunit_staticInvocationMocker;
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
}

}
