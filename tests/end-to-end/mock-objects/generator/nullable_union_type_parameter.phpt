--TEST--
\PHPUnit\Framework\MockObject\Generator::generate('Foo', [], 'MockFoo', true, true)
--SKIPIF--
<?php declare(strict_types=1);
if (PHP_MAJOR_VERSION < 8) {
    print 'skip: PHP 8 is required.';
}
--FILE--
<?php declare(strict_types=1);
class Foo
{
    public function bar(bool|int $baz, self|stdClass|null $other)
    {
    }
}

require_once __DIR__ . '/../../../bootstrap.php';

$generator = new \PHPUnit\Framework\MockObject\Generator;

$mock = $generator->generate(
    Foo::class,
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
    use \PHPUnit\Framework\MockObject\MockedCloneMethod;

    public function bar(int|bool $baz, Foo|stdClass|null $other)
    {
        $__phpunit_arguments = [$baz, $other];
        $__phpunit_count     = func_num_args();

        if ($__phpunit_count > 2) {
            $__phpunit_arguments_tmp = func_get_args();

            for ($__phpunit_i = 2; $__phpunit_i < $__phpunit_count; $__phpunit_i++) {
                $__phpunit_arguments[] = $__phpunit_arguments_tmp[$__phpunit_i];
            }
        }

        $__phpunit_result = $this->__phpunit_getInvocationHandler()->invoke(
            new \PHPUnit\Framework\MockObject\Invocation(
                'Foo', 'bar', $__phpunit_arguments, '', $this, true
            )
        );

        return $__phpunit_result;
    }
}
