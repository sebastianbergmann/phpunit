--TEST--
Mock static method
--FILE--
<?php declare(strict_types=1);
class Foo
{
    private function bar($arg1, $arg2){}
}

require __DIR__ . '/../../../../vendor/autoload.php';

$class = new ReflectionClass('Foo');
$mockMethod = \PHPUnit\Framework\MockObject\MockMethod::fromReflection(
    $class->getMethod('bar'),
    false,
    false
);

$code = $mockMethod->generateCode();

print $code;
--EXPECT--

private function bar($arg1, $arg2)
    {
        $__phpunit_arguments = [$arg1, $arg2];
        $__phpunit_count     = func_num_args();

        if ($__phpunit_count > 2) {
            $__phpunit_arguments_tmp = func_get_args();

            for ($__phpunit_i = 2; $__phpunit_i < $__phpunit_count; $__phpunit_i++) {
                $__phpunit_arguments[] = $__phpunit_arguments_tmp[$__phpunit_i];
            }
        }

        $__phpunit_result = $this->__phpunit_getInvocationMocker()->invoke(
            new \PHPUnit\Framework\MockObject\Invocation(
                'Foo', 'bar', $__phpunit_arguments, '', $this, false
            )
        );

        return $__phpunit_result;
    }
