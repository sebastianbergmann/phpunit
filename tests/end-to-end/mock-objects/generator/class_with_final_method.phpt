--TEST--
\PHPUnit\Framework\MockObject\Generator::generate('ClassWithFinalMethod', [], 'MockFoo', true, true)
--FILE--
<?php declare(strict_types=1);
class ClassWithFinalMethod
{
    final public function finalMethod()
    {
    }
}

require __DIR__ . '/../../../../vendor/autoload.php';

$generator = new \PHPUnit\Framework\MockObject\Generator;

$mock = $generator->generate(
    'ClassWithFinalMethod',
    [],
    'MockFoo',
    true,
    true
);

print $mock->getClassCode();
--EXPECTF--
declare(strict_types=1);

class MockFoo extends ClassWithFinalMethod implements PHPUnit\Framework\MockObject\MockObject
{
    use \PHPUnit\Framework\MockObject\TestDoubleApi;
    use \PHPUnit\Framework\MockObject\TestDoubleApiMethod;

    public function __clone()
    {
        $this->__phpunit_invocationMocker = clone $this->__phpunit_getInvocationMocker();
    }
}
