--TEST--
\PHPUnit\Framework\MockObject\Generator::generate('Foo', [], 'MockFoo', true, true)
--FILE--
<?php declare(strict_types=1);
class Foo
{
    public static function bar(string $baz): Bar
    {
        return 'test';
    }
}

require __DIR__ . '/../../../../vendor/autoload.php';

$generator = new \PHPUnit\Framework\MockObject\Generator;

$mock = $generator->generate(
    'Foo',
    [],
    'MockFoo',
    true,
    true
);

print $mock['code'];
?>
--EXPECT--
class MockFoo extends Foo implements PHPUnit\Framework\MockObject\MockObject
{
    private $__phpunit_invocationMocker;
    private $__phpunit_originalObject;
    private $__phpunit_configurable = ['bar'];
    private $__phpunit_returnValueGeneration = true;

    public function __clone()
    {
        $this->__phpunit_invocationMocker = clone $this->__phpunit_getInvocationMocker();
    }

    public static function bar(string $baz): Bar
    {
        throw new \PHPUnit\Framework\MockObject\BadMethodCallException('Static method "bar" cannot be invoked on mock object');
    }

    public function expects(\PHPUnit\Framework\MockObject\Matcher\Invocation $matcher): \PHPUnit\Framework\MockObject\Builder\InvocationMocker
    {
        return $this->__phpunit_getInvocationMocker()->expects($matcher);
    }

    public function method()
    {
        $any     = new \PHPUnit\Framework\MockObject\Matcher\AnyInvokedCount;
        $expects = $this->expects($any);

        return call_user_func_array([$expects, 'method'], func_get_args());
    }

    public function __phpunit_setOriginalObject($originalObject): void
    {
        $this->__phpunit_originalObject = $originalObject;
    }

    public function __phpunit_setReturnValueGeneration(bool $returnValueGeneration): void
    {
        $this->__phpunit_returnValueGeneration = $returnValueGeneration;
    }

    public function __phpunit_getInvocationMocker(): \PHPUnit\Framework\MockObject\InvocationMocker
    {
        if ($this->__phpunit_invocationMocker === null) {
            $this->__phpunit_invocationMocker = new \PHPUnit\Framework\MockObject\InvocationMocker($this->__phpunit_configurable, $this->__phpunit_returnValueGeneration);
        }

        return $this->__phpunit_invocationMocker;
    }

    public function __phpunit_hasMatchers(): bool
    {
        return $this->__phpunit_getInvocationMocker()->hasMatchers();
    }

    public function __phpunit_verify(bool $unsetInvocationMocker = true): void
    {
        $this->__phpunit_getInvocationMocker()->verify();

        if ($unsetInvocationMocker) {
            $this->__phpunit_invocationMocker = null;
        }
    }
}
