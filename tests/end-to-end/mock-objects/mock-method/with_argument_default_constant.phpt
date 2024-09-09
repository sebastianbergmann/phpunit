--TEST--
Mock static method
--FILE--
<?php declare(strict_types=1);
define('GLOBAL_CONSTANT', 1);

class Foo
{
    public const CLASS_CONSTANT_PUBLIC = 2;
    protected const CLASS_CONSTANT_PROTECTED = 3;
    private const CLASS_CONSTANT_PRIVATE = 4;
    private function bar($a = GLOBAL_CONSTANT, $b = self::CLASS_CONSTANT_PUBLIC, $c = self::CLASS_CONSTANT_PROTECTED, $d = self::CLASS_CONSTANT_PRIVATE){}
}

require_once __DIR__ . '/../../../bootstrap.php';

$class = new ReflectionClass('Foo');
$mockMethod = \PHPUnit\Framework\MockObject\Generator\MockMethod::fromReflection(
    $class->getMethod('bar'),
    false,
    false
);

$code = $mockMethod->generateCode();

print $code;
--EXPECT--

private function bar($a = 1, $b = 2, $c = 3, $d = 4)
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

        $__phpunit_arguments = [$a, $b, $c, $d];
        $__phpunit_count     = func_num_args();

        if (4 !== null && $__phpunit_count > 4) {
            $__phpunit_arguments_tmp = func_get_args();

            for ($__phpunit_i = 4; $__phpunit_i < $__phpunit_count; $__phpunit_i++) {
                $__phpunit_arguments[] = $__phpunit_arguments_tmp[$__phpunit_i];
            }
        }

        $__phpunit_arguments = array_merge($__phpunit_arguments, $__phpunit_namedVariadicParameters);

        $__phpunit_result = $this->__phpunit_getInvocationHandler()->invoke(
            new \PHPUnit\Framework\MockObject\Invocation(
                'Foo', 'bar', $__phpunit_arguments, '', $this, false
            )
        );

        return $__phpunit_result;
    }
