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

class StringEndsWithTest extends TestCase
{
    public function testConstraintStringEndsWith()
    {
        $constraint = new StringEndsWith('suffix');

        $this->assertFalse($constraint->evaluate('foo', '', true));
        $this->assertTrue($constraint->evaluate('foosuffix', '', true));
        $this->assertEquals('ends with "suffix"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate('foo');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 'foo' ends with "suffix".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintStringEndsWith2()
    {
        $constraint = new StringEndsWith('suffix');

        try {
            $constraint->evaluate('foo', 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that 'foo' ends with "suffix".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }
}
