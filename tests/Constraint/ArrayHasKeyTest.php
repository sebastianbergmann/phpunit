<?php
/*
 * This file is part of sebastian/phpunit-framework-constraint.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Framework\Constraint;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestFailure;

class ArrayHasKeyTest extends TestCase
{
    public function testConstraintArrayHasKey()
    {
        $constraint = new ArrayHasKey(0);

        $this->assertFalse($constraint->evaluate([], '', true));
        $this->assertEquals('has the key 0', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate([]);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that an array has the key 0.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintArrayHasKey2()
    {
        $constraint = new ArrayHasKey(0);

        try {
            $constraint->evaluate([], 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message\nFailed asserting that an array has the key 0.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintArrayNotHasKey()
    {
        $constraint = new LogicalNot(
            new ArrayHasKey(0)
        );

        $this->assertFalse($constraint->evaluate([0 => 1], '', true));
        $this->assertEquals('does not have the key 0', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate([0 => 1]);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that an array does not have the key 0.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }


    public function testConstraintArrayNotHasKey2()
    {
        $constraint = new LogicalNot(
            new ArrayHasKey(0)
        );

        try {
            $constraint->evaluate([0], 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that an array does not have the key 0.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }
}