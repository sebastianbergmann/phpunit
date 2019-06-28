--TEST--
\PHPUnit\Framework\MockObject\Generator::generate('NonExistentClass', [], 'MockFoo', true, true)
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/../../../../vendor/autoload.php';

$generator = new \PHPUnit\Framework\MockObject\Generator;

$mock = $generator->generate(
    'NonExistentClass',
    [],
    'MockFoo',
    true,
    true
);

print $mock->getClassCode();
--EXPECTF--
declare(strict_types=1);

class NonExistentClass
{
}

class MockFoo extends NonExistentClass implements PHPUnit\Framework\MockObject\MockObject
{
    use \PHPUnit\Framework\MockObject\TestDoubleApi;

    public function __clone()
    {
        $this->__phpunit_invocationMocker = clone $this->__phpunit_getInvocationMocker();
    }

    public function method()
    {
        $any     = new \PHPUnit\Framework\MockObject\Matcher\AnyInvokedCount;
        $expects = $this->expects($any);

        return call_user_func_array([$expects, 'method'], func_get_args());
    }
}
