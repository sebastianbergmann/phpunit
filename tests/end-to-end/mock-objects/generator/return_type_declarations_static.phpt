--TEST--
\PHPUnit\Framework\MockObject\Generator\Generator::generate('Foo', [], 'MockFoo', true, true)
--FILE--
<?php declare(strict_types=1);
class ClassWithStaticReturnTypes
{
    public function returnsStatic(): static
    {
    }

    public function returnsStaticOrNull(): ?static
    {
    }

    public function returnsUnionWithStatic(): static|\stdClass
    {
    }
}

require_once __DIR__ . '/../../../bootstrap.php';

$generator = new \PHPUnit\Framework\MockObject\Generator\Generator;

$mock = $generator->generate(
    'ClassWithStaticReturnTypes',
    [],
    'MockClassWithStaticReturnTypes',
    true,
    true
);

print $mock->classCode();
--EXPECTF--
declare(strict_types=1);

class MockClassWithStaticReturnTypes extends ClassWithStaticReturnTypes implements PHPUnit\Framework\MockObject\MockObjectInternal
{
    use \PHPUnit\Framework\MockObject\StubApi;
    use \PHPUnit\Framework\MockObject\MockObjectApi;
    use \PHPUnit\Framework\MockObject\Method;
    use \PHPUnit\Framework\MockObject\MockedCloneMethod;

    public function returnsStatic(): static
    {
        $__phpunit_arguments = [];
        $__phpunit_count     = func_num_args();

        if ($__phpunit_count > 0) {
            $__phpunit_arguments_tmp = func_get_args();

            for ($__phpunit_i = 0; $__phpunit_i < $__phpunit_count; $__phpunit_i++) {
                $__phpunit_arguments[] = $__phpunit_arguments_tmp[$__phpunit_i];
            }
        }

        $__phpunit_result = $this->__phpunit_getInvocationHandler()->invoke(
            new \PHPUnit\Framework\MockObject\Invocation(
                'ClassWithStaticReturnTypes', 'returnsStatic', $__phpunit_arguments, 'static', $this, true
            )
        );

        return $__phpunit_result;
    }

    public function returnsStaticOrNull(): ?static
    {
        $__phpunit_arguments = [];
        $__phpunit_count     = func_num_args();

        if ($__phpunit_count > 0) {
            $__phpunit_arguments_tmp = func_get_args();

            for ($__phpunit_i = 0; $__phpunit_i < $__phpunit_count; $__phpunit_i++) {
                $__phpunit_arguments[] = $__phpunit_arguments_tmp[$__phpunit_i];
            }
        }

        $__phpunit_result = $this->__phpunit_getInvocationHandler()->invoke(
            new \PHPUnit\Framework\MockObject\Invocation(
                'ClassWithStaticReturnTypes', 'returnsStaticOrNull', $__phpunit_arguments, '?static', $this, true
            )
        );

        return $__phpunit_result;
    }

    public function returnsUnionWithStatic(): static|stdClass
    {
        $__phpunit_arguments = [];
        $__phpunit_count     = func_num_args();

        if ($__phpunit_count > 0) {
            $__phpunit_arguments_tmp = func_get_args();

            for ($__phpunit_i = 0; $__phpunit_i < $__phpunit_count; $__phpunit_i++) {
                $__phpunit_arguments[] = $__phpunit_arguments_tmp[$__phpunit_i];
            }
        }

        $__phpunit_result = $this->__phpunit_getInvocationHandler()->invoke(
            new \PHPUnit\Framework\MockObject\Invocation(
                'ClassWithStaticReturnTypes', 'returnsUnionWithStatic', $__phpunit_arguments, 'static|stdClass', $this, true
            )
        );

        return $__phpunit_result;
    }
}
