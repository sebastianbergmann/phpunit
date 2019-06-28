--TEST--
\PHPUnit\Framework\MockObject\Generator::generate('Foo', [], 'MockFoo', true)
--FILE--
<?php declare(strict_types=1);
class Foo
{
        public function __clone()
    {
    }
}

require __DIR__ . '/../../../../vendor/autoload.php';

$generator = new \PHPUnit\Framework\MockObject\Generator;

$mock = $generator->generate(
    'Foo',
    [],
    'MockFoo',
    true
);

print $mock->getClassCode();
--EXPECTF--
declare(strict_types=1);

class MockFoo extends Foo implements PHPUnit\Framework\MockObject\MockObject
{
    use \PHPUnit\Framework\MockObject\TestDoubleApi;

    public function __clone()
    {
        $this->__phpunit_invocationMocker = clone $this->__phpunit_getInvocationMocker();
        parent::__clone();
    }

    public function method()
    {
        $any     = new \PHPUnit\Framework\MockObject\Matcher\AnyInvokedCount;
        $expects = $this->expects($any);

        return call_user_func_array([$expects, 'method'], func_get_args());
    }
}
