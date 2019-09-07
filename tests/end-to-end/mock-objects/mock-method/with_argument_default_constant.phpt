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

private function bar($a = 1, $b = 2, $c = 3, $d = 4)
    {
        $__phpunit_arguments = [$a, $b, $c, $d];
        $__phpunit_count     = func_num_args();

        if ($__phpunit_count > 4) {
            $__phpunit_arguments_tmp = func_get_args();

            for ($__phpunit_i = 4; $__phpunit_i < $__phpunit_count; $__phpunit_i++) {
                $__phpunit_arguments[] = $__phpunit_arguments_tmp[$__phpunit_i];
            }
        }

        $__phpunit_result = $this->__phpunit_getInvocationHandler()->invoke(
            new \PHPUnit\Framework\MockObject\Invocation(
                'Foo', 'bar', $__phpunit_arguments, '', $this, false
            )
        );

        return $__phpunit_result;
    }
