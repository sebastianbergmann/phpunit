--TEST--
Mock static method
--FILE--
<?php declare(strict_types=1);
class Foo
{
    private function bar($arg = false){}
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

private function bar($arg = false)
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
        $__phpunit_arguments = [$arg];
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
                'Foo', 'bar', $__phpunit_arguments, '', $this, false
            )
        );

        return $__phpunit_result;
    }
