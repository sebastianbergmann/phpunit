--TEST--
PHPUnit_Framework_MockObject_Generator::generate('Foo', array(), 'MockFoo', TRUE, TRUE, TRUE)
--FILE--
<?php
class Foo
{
    public static function bar(Foo $foo)
    {
    }

    public static function baz(Foo $foo)
    {
    }
}

require __DIR__ . '/../../vendor/autoload.php';

$generator = new PHPUnit_Framework_MockObject_Generator;

$mock = $generator->generate(
  'Foo',
  array(),
  'MockFoo',
  TRUE,
  TRUE,
  TRUE
);

print $mock['code'];
?>
--EXPECTF--
class MockFoo extends Foo implements PHPUnit_Framework_MockObject_MockObject
{
    private static $__phpunit_staticInvocationMocker;
    private $__phpunit_invocationMocker;
    private $__phpunit_originalObject;

    public function __clone()
    {
        $this->__phpunit_invocationMocker = clone $this->__phpunit_getInvocationMocker();
    }

    public static function bar(Foo $foo)
    {
        $arguments = array($foo);
        $count     = func_num_args();

        if ($count > 1) {
            $_arguments = func_get_args();

            for ($i = 1; $i < $count; $i++) {
                $arguments[] = $_arguments[$i];
            }
        }

        $result = self::__phpunit_getStaticInvocationMocker()->invoke(
          new PHPUnit_Framework_MockObject_Invocation_Static(
            'Foo', 'bar', $arguments, TRUE
          )
        );

        return $result;
    }

    public static function baz(Foo $foo)
    {
        $arguments = array($foo);
        $count     = func_num_args();

        if ($count > 1) {
            $_arguments = func_get_args();

            for ($i = 1; $i < $count; $i++) {
                $arguments[] = $_arguments[$i];
            }
        }

        $result = self::__phpunit_getStaticInvocationMocker()->invoke(
          new PHPUnit_Framework_MockObject_Invocation_Static(
            'Foo', 'baz', $arguments, TRUE
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
