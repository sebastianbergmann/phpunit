--TEST--
\PHPUnit\Framework\MockObject\Generator\Generator::generate('ClassWithMethodWithVariadicArguments', [], 'MockFoo', true, true)
--FILE--
<?php declare(strict_types=1);
class ClassWithMethodWithNullableTypehintedVariadicArguments
{
    public function methodWithNullableTypehintedVariadicArguments($a, ?string ...$parameters)
    {
    }
}

require_once __DIR__ . '/../../../bootstrap.php';

$generator = new \PHPUnit\Framework\MockObject\Generator\Generator;

$mock = $generator->generate(
    'ClassWithMethodWithNullableTypehintedVariadicArguments',
    [],
    'MockFoo',
    true,
    true
);

print $mock->classCode();
--EXPECTF--
declare(strict_types=1);

class MockFoo extends ClassWithMethodWithNullableTypehintedVariadicArguments implements PHPUnit\Framework\MockObject\MockObjectInternal
{
    use \PHPUnit\Framework\MockObject\StubApi;
    use \PHPUnit\Framework\MockObject\MockObjectApi;
    use \PHPUnit\Framework\MockObject\Method;
    use \PHPUnit\Framework\MockObject\MockedCloneMethod;

    public function methodWithNullableTypehintedVariadicArguments($a, ?string ...$parameters)
    {
        $__phpunit_arguments = [$a];
        $__phpunit_count     = func_num_args();

        if ($__phpunit_count > 1) {
            $__phpunit_arguments_tmp = func_get_args();

            for ($__phpunit_i = 1; $__phpunit_i < $__phpunit_count; $__phpunit_i++) {
                $__phpunit_arguments[] = $__phpunit_arguments_tmp[$__phpunit_i];
            }
        }

        $__phpunit_result = $this->__phpunit_getInvocationHandler()->invoke(
            new \PHPUnit\Framework\MockObject\Invocation(
                'ClassWithMethodWithNullableTypehintedVariadicArguments', 'methodWithNullableTypehintedVariadicArguments', $__phpunit_arguments, '', $this, true
            )
        );

        return $__phpunit_result;
    }
}
