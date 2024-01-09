--TEST--
\PHPUnit\Framework\MockObject\Generator\Generator::generate('ClassWithMethodWithVariadicArguments', [], 'MockFoo', true, true)
--FILE--
<?php declare(strict_types=1);
class ClassWithMethodWithTypehintedVariadicArguments
{
    public function methodWithTypehintedVariadicArguments($a, string ...$parameters)
    {
    }
}

require_once __DIR__ . '/../../../bootstrap.php';

$generator = new \PHPUnit\Framework\MockObject\Generator\Generator;

$mock = $generator->generate(
    'ClassWithMethodWithTypehintedVariadicArguments',
    true,
    [],
    'MockFoo',
    true,
    true
);

print $mock->classCode();
--EXPECTF--
declare(strict_types=1);

class MockFoo extends ClassWithMethodWithTypehintedVariadicArguments implements PHPUnit\Framework\MockObject\MockObjectInternal
{
    use PHPUnit\Framework\MockObject\StubApi;
    use PHPUnit\Framework\MockObject\MockObjectApi;
    use PHPUnit\Framework\MockObject\Method;
    use PHPUnit\Framework\MockObject\DoubledCloneMethod;

    public function methodWithTypehintedVariadicArguments($a, string ...$parameters)
    {
        $definedVariables = get_defined_vars();
        $namedVariadicParameters = [];
        foreach ($definedVariables as $name => $value) {
            $reflectionParam = new ReflectionParameter([__CLASS__, __FUNCTION__], $name);
            if ($reflectionParam->isVariadic()) {
                foreach ($value as $key => $namedValue) {
                    if (is_string($key)) {
                        $namedVariadicParameters[$key] = $namedValue;
                    }
                }
            }
        }
        $__phpunit_arguments = [$a];
        $__phpunit_count     = func_num_args();

        if ($__phpunit_count > 1) {
            $__phpunit_arguments_tmp = func_get_args();

            for ($__phpunit_i = 1; $__phpunit_i < $__phpunit_count; $__phpunit_i++) {
                $__phpunit_arguments[] = $__phpunit_arguments_tmp[$__phpunit_i];
            }
        }
        $__phpunit_arguments = array_merge($__phpunit_arguments, $namedVariadicParameters);

        $__phpunit_result = $this->__phpunit_getInvocationHandler()->invoke(
            new \PHPUnit\Framework\MockObject\Invocation(
                'ClassWithMethodWithTypehintedVariadicArguments', 'methodWithTypehintedVariadicArguments', $__phpunit_arguments, '', $this, true
            )
        );

        return $__phpunit_result;
    }
}
