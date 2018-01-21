<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestFailure;

class ArrayHasKeyValuePairTest extends ConstraintTestCase
{
    /**
     * @dataProvider keyValueMessageProvider
     *
     * @param mixed $key
     * @param mixed $value
     * @param mixed $message
     */
    public function testConstraintArrayHasKeyValuePair($key, $value, $message): void
    {
        $constraint = new ArrayHasKeyValuePair($key, $value);

        $this->assertFalse($constraint->evaluate([], '', true));
        $this->assertEquals($message, $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate([]);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that an array {$message}.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function keyValueMessageProvider()
    {
        return [
            [0, 123, 'has the 0 => 123 key value pair'],
            ['foo', 'bar', "has the 'foo' => 'bar' key value pair"],
            ['arr', [1, 2, 3], "has the 'arr' => Array &0 (\n    0 => 1\n    1 => 2\n    2 => 3\n) key value pair"]
        ];
    }
}
