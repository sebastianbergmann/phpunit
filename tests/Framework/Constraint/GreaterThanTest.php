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

class GreaterThanTest extends ConstraintTestCase
{
    public function testConstraintGreaterThan(): void
    {
        $constraint = new GreaterThan(1);

        $this->assertFalse($constraint->evaluate(0, '', true));
        $this->assertTrue($constraint->evaluate(2, '', true));
        $this->assertEquals('is greater than 1', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate(0);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 0 is greater than 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintGreaterThan2(): void
    {
        $constraint = new GreaterThan(1);

        try {
            $constraint->evaluate(0, 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that 0 is greater than 1.

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }
}
