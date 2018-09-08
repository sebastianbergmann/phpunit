--TEST--
Mock method and call original method with argument
--FILE--
<?php
class Foo
{
    private function bar($arg){}
}

require __DIR__ . '/../../../../vendor/autoload.php';

$class = new ReflectionClass('Foo');
$mockMethod = \PHPUnit\Framework\MockObject\MockMethod::fromReflection(
    $class->getMethod('bar'),
    true,
    false
);

$code = $mockMethod->generateCode();

print $code;
?>
--EXPECT--

private function bar($arg)
    {
        $__phpunit_arguments = [$arg];
        $__phpunit_count     = func_num_args();

        if ($__phpunit_count > 1) {
            $__phpunit_arguments_tmp = func_get_args();

            for ($__phpunit_i = 1; $__phpunit_i < $__phpunit_count; $__phpunit_i++) {
                $__phpunit_arguments[] = $__phpunit_arguments_tmp[$__phpunit_i];
            }
        }

        $this->__phpunit_getInvocationMocker()->invoke(
            new \PHPUnit\Framework\MockObject\Invocation\ObjectInvocation(
                'Foo', 'bar', $__phpunit_arguments, '', $this, false
            )
        );

        return call_user_func_array(array($this->__phpunit_originalObject, "bar"), $__phpunit_arguments);
    }
