--TEST--
\PHPUnit\Framework\MockObject\Generator\Generator::generate('Foo', [], 'MockFoo', true, true)
--FILE--
<?php declare(strict_types=1);
abstract class Foo
{
    public function one()
    {
    }

    abstract public function two();

    abstract protected function three();
}

require_once __DIR__ . '/../../../bootstrap.php';

$generator = new \PHPUnit\Framework\MockObject\Generator\Generator;

$mock = $generator->generate(
    'Foo',
    true,
    true,
    [],
    'MockFoo',
    true,
    true
);

print $mock->classCode();
--EXPECTF--
declare(strict_types=1);

class MockFoo extends Foo implements PHPUnit\Framework\MockObject\MockObjectInternal
{
    use PHPUnit\Framework\MockObject\%SStubApi;
    use PHPUnit\Framework\MockObject\MockObjectApi;
    use PHPUnit\Framework\MockObject\GeneratedAsMockObject;
    use PHPUnit\Framework\MockObject\Method;
    use PHPUnit\Framework\MockObject\DoubledCloneMethod;

    public function one()
    {
        $__phpunit_definedVariables        = get_defined_vars();
        $__phpunit_namedVariadicParameters = [];

        foreach ($__phpunit_definedVariables as $__phpunit_definedVariableName => $__phpunit_definedVariableValue) {
            if ((new ReflectionParameter([__CLASS__, __FUNCTION__], $__phpunit_definedVariableName))->isVariadic()) {
                foreach ($__phpunit_definedVariableValue as $__phpunit_key => $__phpunit_namedValue) {
                    if (is_string($__phpunit_key)) {
                        $__phpunit_namedVariadicParameters[$__phpunit_key] = $__phpunit_namedValue;
                    }
                }
            }
        }

        $__phpunit_arguments = [];
        $__phpunit_count     = func_num_args();

        if (0 !== null && $__phpunit_count > 0) {
            $__phpunit_arguments_tmp = func_get_args();

            for ($__phpunit_i = 0; $__phpunit_i < $__phpunit_count; $__phpunit_i++) {
                $__phpunit_arguments[] = $__phpunit_arguments_tmp[$__phpunit_i];
            }
        }

        $__phpunit_arguments = array_merge($__phpunit_arguments, $__phpunit_namedVariadicParameters);

        $__phpunit_result = $this->__phpunit_getInvocationHandler()->invoke(
            new \PHPUnit\Framework\MockObject\Invocation(
                'Foo', 'one', $__phpunit_arguments, '', $this, true
            )
        );

        return $__phpunit_result;
    }

    public function two()
    {
        $__phpunit_definedVariables        = get_defined_vars();
        $__phpunit_namedVariadicParameters = [];

        foreach ($__phpunit_definedVariables as $__phpunit_definedVariableName => $__phpunit_definedVariableValue) {
            if ((new ReflectionParameter([__CLASS__, __FUNCTION__], $__phpunit_definedVariableName))->isVariadic()) {
                foreach ($__phpunit_definedVariableValue as $__phpunit_key => $__phpunit_namedValue) {
                    if (is_string($__phpunit_key)) {
                        $__phpunit_namedVariadicParameters[$__phpunit_key] = $__phpunit_namedValue;
                    }
                }
            }
        }

        $__phpunit_arguments = [];
        $__phpunit_count     = func_num_args();

        if (0 !== null && $__phpunit_count > 0) {
            $__phpunit_arguments_tmp = func_get_args();

            for ($__phpunit_i = 0; $__phpunit_i < $__phpunit_count; $__phpunit_i++) {
                $__phpunit_arguments[] = $__phpunit_arguments_tmp[$__phpunit_i];
            }
        }

        $__phpunit_arguments = array_merge($__phpunit_arguments, $__phpunit_namedVariadicParameters);

        $__phpunit_result = $this->__phpunit_getInvocationHandler()->invoke(
            new \PHPUnit\Framework\MockObject\Invocation(
                'Foo', 'two', $__phpunit_arguments, '', $this, true
            )
        );

        return $__phpunit_result;
    }

    protected function three()
    {
        $__phpunit_definedVariables        = get_defined_vars();
        $__phpunit_namedVariadicParameters = [];

        foreach ($__phpunit_definedVariables as $__phpunit_definedVariableName => $__phpunit_definedVariableValue) {
            if ((new ReflectionParameter([__CLASS__, __FUNCTION__], $__phpunit_definedVariableName))->isVariadic()) {
                foreach ($__phpunit_definedVariableValue as $__phpunit_key => $__phpunit_namedValue) {
                    if (is_string($__phpunit_key)) {
                        $__phpunit_namedVariadicParameters[$__phpunit_key] = $__phpunit_namedValue;
                    }
                }
            }
        }

        $__phpunit_arguments = [];
        $__phpunit_count     = func_num_args();

        if (0 !== null && $__phpunit_count > 0) {
            $__phpunit_arguments_tmp = func_get_args();

            for ($__phpunit_i = 0; $__phpunit_i < $__phpunit_count; $__phpunit_i++) {
                $__phpunit_arguments[] = $__phpunit_arguments_tmp[$__phpunit_i];
            }
        }

        $__phpunit_arguments = array_merge($__phpunit_arguments, $__phpunit_namedVariadicParameters);

        $__phpunit_result = $this->__phpunit_getInvocationHandler()->invoke(
            new \PHPUnit\Framework\MockObject\Invocation(
                'Foo', 'three', $__phpunit_arguments, '', $this, true
            )
        );

        return $__phpunit_result;
    }
}
