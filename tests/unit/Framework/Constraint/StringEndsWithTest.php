<?php declare(strict_types=1);
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

/**
 * @small
 */
final class StringEndsWithTest extends ConstraintTestCase
{
    public function testConstraintStringEndsWithCorrectValueAndReturnResult(): void
    {
        $constraint = new StringEndsWith('suffix');

        $this->assertTrue($constraint->evaluate('foosuffix', '', true));
    }

    public function testConstraintStringEndsWithNotCorrectValueAndReturnResult(): void
    {
        $constraint = new StringEndsWith('suffix');

        $this->assertFalse($constraint->evaluate('suffixerror', '', true));
    }

    public function testConstraintStringEndsWithCorrectNumericValueAndReturnResult(): void
    {
        $constraint = new StringEndsWith('0E1');

        $this->assertTrue($constraint->evaluate('zzz0E1', '', true));
    }

    public function testConstraintStringEndsWithNotCorrectNumericValueAndReturnResult(): void
    {
        $constraint = new StringEndsWith('0E1');

        $this->assertFalse($constraint->evaluate('zzz0E2', '', true));
    }

    public function testConstraintStringEndsWithToStringMethod(): void
    {
        $constraint = new StringEndsWith('suffix');

        $this->assertEquals('ends with "suffix"', $constraint->toString());
    }

    public function testConstraintStringEndsWithCountMethod(): void
    {
        $constraint = new StringEndsWith('suffix');

        $this->assertCount(1, $constraint);
    }

    public function testConstraintStringEndsWithNotCorrectValueAndExpectation(): void
    {
        $constraint = new StringEndsWith('suffix');

        try {
            $constraint->evaluate('error');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 'error' ends with "suffix".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintStringEndsWithNotCorrectValueExceptionAndCustomMessage(): void
    {
        $constraint = new StringEndsWith('suffix');

        try {
            $constraint->evaluate('error', 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that 'error' ends with "suffix".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }
}
