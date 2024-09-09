--TEST--
https://github.com/sebastianbergmann/phpunit-mock-objects/issues/420
https://github.com/sebastianbergmann/phpunit/issues/3154
--FILE--
<?php declare(strict_types=1);
namespace Is\Namespaced;
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

const A_CONSTANT = 17;
const PHP_VERSION = "";

class Issue3154
{
    public function a(int $i = PHP_INT_MAX, int $j = A_CONSTANT, string $v = \PHP_VERSION, string $z = '#'): string
    {
        return $z."sum: ".($i+$j).$v;
    }
}
require_once __DIR__ . '/../../../bootstrap.php';

$generator = new \PHPUnit\Framework\MockObject\Generator\Generator;

$mock = $generator->generate(
    Issue3154::class,
    true,
    true,
    [],
    'Issue3154Mock',
    true,
    true
);

print $mock->classCode();
--EXPECTF--
declare(strict_types=1);

class Issue3154Mock extends Is\Namespaced\Issue3154 implements PHPUnit\Framework\MockObject\MockObjectInternal
{
    use PHPUnit\Framework\MockObject\%SStubApi;
    use PHPUnit\Framework\MockObject\MockObjectApi;
    use PHPUnit\Framework\MockObject\GeneratedAsMockObject;
    use PHPUnit\Framework\MockObject\Method;
    use PHPUnit\Framework\MockObject\DoubledCloneMethod;

    public function a(int $i = %d, int $j = 17, string $v = '%s', string $z = '#'): string
    {
        $__phpunit_definedVariables        = get_defined_vars();
        $__phpunit_namedVariadicParameters = [];

        foreach ($__phpunit_definedVariables as $__phpunit_definedVariableName => $__phpunit_definedVariableValue) {
            if ((new ReflectionParameter([__CLASS__, __FUNCTION__], $__phpunit_definedVariableName))->isVariadic()) {
                foreach ($__phpunit_definedVariableValue as $__phpunit_key => $__phpunit_namedValue) {
                    if (is_string($__phpunit_key)) {
                        $__phpunit_namedVariadicParameters[$__phpunit_key] = $__phpunit_namedValue;
                    }
                }
            }
        }

        $__phpunit_arguments = [$i, $j, $v, $z];
        $__phpunit_count     = func_num_args();

        if (4 !== null && $__phpunit_count > 4) {
            $__phpunit_arguments_tmp = func_get_args();

            for ($__phpunit_i = 4; $__phpunit_i < $__phpunit_count; $__phpunit_i++) {
                $__phpunit_arguments[] = $__phpunit_arguments_tmp[$__phpunit_i];
            }
        }

        $__phpunit_arguments = array_merge($__phpunit_arguments, $__phpunit_namedVariadicParameters);

        $__phpunit_result = $this->__phpunit_getInvocationHandler()->invoke(
            new \PHPUnit\Framework\MockObject\Invocation(
                'Is\Namespaced\Issue3154', 'a', $__phpunit_arguments, 'string', $this, true
            )
        );

        return $__phpunit_result;
    }
}
