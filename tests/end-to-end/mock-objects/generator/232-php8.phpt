--TEST--
\PHPUnit\Framework\MockObject\Generator::generate('Foo', [], 'MockFoo', true, true)
--SKIPIF--
<?php declare(strict_types=1);
if (PHP_MAJOR_VERSION < 8) {
    print 'skip: PHP 8 is required.';
}
--FILE--
<?php declare(strict_types=1);
trait BaseTrait
{
    protected function hello()
    {
        return 'hello';
    }
}

trait ChildTrait
{
    use BaseTrait
    {
        hello as private hi;
    }

    protected function hello()
    {
        return 'hi';
    }

    protected function world()
    {
        return $this->hi();
    }
}

class Foo
{
    use ChildTrait;

    public function speak()
    {
        return $this->world();
    }
}

require_once __DIR__ . '/../../../bootstrap.php';

$generator = new \PHPUnit\Framework\MockObject\Generator;

$mock = $generator->generate(
    'Foo',
    [],
    'MockFoo',
    true,
    true
);

print $mock->getClassCode();
--EXPECTF--
declare(strict_types=1);

class MockFoo extends Foo implements PHPUnit\Framework\MockObject\MockObject
{
    use \PHPUnit\Framework\MockObject\Api;
    use \PHPUnit\Framework\MockObject\Method;
    use \PHPUnit\Framework\MockObject\MockedCloneMethodWithVoidReturnType;

    public function speak()
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

        $__phpunit_result = $this->__phpunit_getInvocationHandler()->invoke(
            new \PHPUnit\Framework\MockObject\Invocation(
                'Foo', 'speak', $__phpunit_arguments, '', $this, true
            )
        );

        return $__phpunit_result;
    }
}
