--TEST--
Mock method and call original method that has a return type of void
--FILE--
<?php declare(strict_types=1);
class Foo
{
    public function bar():void{}
}

require_once __DIR__ . '/../../../bootstrap.php';

$class = new ReflectionClass('Foo');
$mockMethod = \PHPUnit\Framework\MockObject\Generator\MockMethod::fromReflection(
    $class->getMethod('bar'),
    true,
    false
);

$code = $mockMethod->generateCode();

print $code;
--EXPECTF--

    public function bar(): void
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
        $__phpunit_arguments = [];
        $__phpunit_count     = func_num_args();

        if ($__phpunit_count > 0) {
            $__phpunit_arguments_tmp = func_get_args();

            for ($__phpunit_i = 0; $__phpunit_i < $__phpunit_count; $__phpunit_i++) {
                $__phpunit_arguments[] = $__phpunit_arguments_tmp[$__phpunit_i];
            }
        }
        $__phpunit_arguments = array_merge($__phpunit_arguments, $namedVariadicParameters);

        $this->__phpunit_getInvocationHandler()->invoke(
            new \PHPUnit\Framework\MockObject\Invocation(
                'Foo', 'bar', $__phpunit_arguments, 'void', $this, false, true
            )
        );

        $__phpunit_result = call_user_func_array([$this->__phpunit_originalObject, "bar"], $__phpunit_arguments);
    }
