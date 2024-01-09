--TEST--
https://github.com/sebastianbergmann/phpunit/issues/3967
--SKIPIF--
<?php declare(strict_types=1);
if ((new ReflectionMethod(Exception::class, '__clone'))->isFinal()) {
    print 'skip: PHP >= 8.1 required';
}
--FILE--
<?php declare(strict_types=1);
interface Bar extends \Throwable
{
    public function foo(): string;
}

interface Baz extends Bar
{
}

require_once __DIR__ . '/../../../bootstrap.php';

$generator = new \PHPUnit\Framework\MockObject\Generator;

$mock = $generator->generate(
    'Baz',
    [],
    'MockBaz',
    true,
    true
);

print $mock->getClassCode();
--EXPECT--
declare(strict_types=1);

class MockBaz extends Exception implements Baz, PHPUnit\Framework\MockObject\MockObject
{
    use \PHPUnit\Framework\MockObject\Api;
    use \PHPUnit\Framework\MockObject\Method;
    use \PHPUnit\Framework\MockObject\UnmockedCloneMethodWithVoidReturnType;

    public function foo(): string
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
                'Bar', 'foo', $__phpunit_arguments, 'string', $this, true
            )
        );

        return $__phpunit_result;
    }
}
