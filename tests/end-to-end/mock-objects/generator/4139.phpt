--TEST--
https://github.com/sebastianbergmann/phpunit/issues/4139
--FILE--
<?php declare(strict_types=1);
interface InterfaceWithConstructor
{
    public function __construct();
}
require_once __DIR__ . '/../../../bootstrap.php';

$generator = new \PHPUnit\Framework\MockObject\Generator;

$mock = $generator->generate(InterfaceWithConstructor::class);

print $mock->getClassCode();
--EXPECTF--
declare(strict_types=1);

class %s implements PHPUnit\Framework\MockObject\MockObject, InterfaceWithConstructor
{
    use \PHPUnit\Framework\MockObject\Api;
    use \PHPUnit\Framework\MockObject\Method;
    use \PHPUnit\Framework\MockObject\MockedCloneMethod;

    public function __construct()
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
                'InterfaceWithConstructor', '__construct', $__phpunit_arguments, '', $this, true
            )
        );

        return $__phpunit_result;
    }
}
