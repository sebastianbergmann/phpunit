--TEST--
https://github.com/sebastianbergmann/phpunit-mock-objects/issues/397
--SKIPIF--
<?php declare(strict_types=1);
if (PHP_MAJOR_VERSION >= 8) {
    print 'skip: PHP 7 is required.';
}
--FILE--
<?php declare(strict_types=1);
class C
{
    public function m(?self $other): self
    {
    }
}

require_once __DIR__ . '/../../../bootstrap.php';

$generator = new \PHPUnit\Framework\MockObject\Generator;

$mock = $generator->generate(
    C::class,
    [],
    'MockC',
    true,
    true
);

print $mock->getClassCode();
--EXPECTF--
declare(strict_types=1);

class MockC extends C implements PHPUnit\Framework\MockObject\MockObject
{
    use \PHPUnit\Framework\MockObject\Api;
    use \PHPUnit\Framework\MockObject\Method;
    use \PHPUnit\Framework\MockObject\MockedCloneMethodWithoutReturnType;

    public function m(?C $other): C
    {
        $__phpunit_arguments = [$other];
        $__phpunit_count     = func_num_args();

        if ($__phpunit_count > 1) {
            $__phpunit_arguments_tmp = func_get_args();

            for ($__phpunit_i = 1; $__phpunit_i < $__phpunit_count; $__phpunit_i++) {
                $__phpunit_arguments[] = $__phpunit_arguments_tmp[$__phpunit_i];
            }
        }

        $__phpunit_result = $this->__phpunit_getInvocationHandler()->invoke(
            new \PHPUnit\Framework\MockObject\Invocation(
                'C', 'm', $__phpunit_arguments, 'C', $this, true
            )
        );

        return $__phpunit_result;
    }
}
