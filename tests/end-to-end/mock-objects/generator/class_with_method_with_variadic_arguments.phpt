--TEST--
\PHPUnit\Framework\MockObject\Generator::generate('ClassWithMethodWithVariadicArguments', [], 'MockFoo', true, true)
--FILE--
<?php declare(strict_types=1);
class ClassWithMethodWithVariadicArguments
{
    public function methodWithVariadicArguments($a, ...$parameters)
    {
    }
}

require __DIR__ . '/../../../../vendor/autoload.php';

$generator = new \PHPUnit\Framework\MockObject\Generator;

$mock = $generator->generate(
    'ClassWithMethodWithVariadicArguments',
    [],
    'MockFoo',
    true,
    true
);

print $mock->getClassCode();
--EXPECTF--
declare(strict_types=1);

class MockFoo extends ClassWithMethodWithVariadicArguments implements PHPUnit\Framework\MockObject\MockObject
{
    use \PHPUnit\Framework\MockObject\Api;
    use \PHPUnit\Framework\MockObject\Method;
    use \PHPUnit\Framework\MockObject\MockedCloneMethod;

    public function methodWithVariadicArguments($a, ...$parameters)
    {
        $__phpunit_arguments = [$a];
        $__phpunit_count     = func_num_args();

        if ($__phpunit_count > 1) {
            $__phpunit_arguments_tmp = func_get_args();

            for ($__phpunit_i = 1; $__phpunit_i < $__phpunit_count; $__phpunit_i++) {
                $__phpunit_arguments[] = $__phpunit_arguments_tmp[$__phpunit_i];
            }
        }

        $__phpunit_result = $this->__phpunit_getInvocationMocker()->invoke(
            new \PHPUnit\Framework\MockObject\Invocation(
                'ClassWithMethodWithVariadicArguments', 'methodWithVariadicArguments', $__phpunit_arguments, '', $this, true
            )
        );

        return $__phpunit_result;
    }
}
