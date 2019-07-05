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

class StringStartsWithTest extends ConstraintTestCase
{
    public function testConstraintStringStartsWithCorrectValueAndReturnResult(): void
    {
        $constraint = new StringStartsWith('prefix');

        $this->assertTrue($constraint->evaluate('prefixfoo', '', true));
    }

    public function testConstraintStringStartsWithNotCorrectValueAndReturnResult(): void
    {
        $constraint = new StringStartsWith('prefix');

        $this->assertFalse($constraint->evaluate('error', '', true));
    }

    public function testConstraintStringStartsWithCorrectNumericValueAndReturnResult(): void
    {
        $constraint = new StringStartsWith('0E1');

        $this->assertTrue($constraint->evaluate('0E1zzz', '', true));
    }

    public function testConstraintStringStartsWithCorrectSingleZeroAndReturnResult(): void
    {
        $constraint = new StringStartsWith('0');

        $this->assertTrue($constraint->evaluate('0ABC', '', true));
    }

    public function testConstraintStringStartsWithNotCorrectNumericValueAndReturnResult(): void
    {
        $constraint = new StringStartsWith('0E1');

        $this->assertFalse($constraint->evaluate('0E2zzz', '', true));
    }

    public function testConstraintStringStartsWithToStringMethod(): void
    {
        $constraint = new StringStartsWith('prefix');

        $this->assertEquals('starts with "prefix"', $constraint->toString());
    }

    public function testConstraintStringStartsWitCountMethod(): void
    {
        $constraint = new StringStartsWith('prefix');

        $this->assertCount(1, $constraint);
    }

    public function testConstraintStringStartsWithNotCorrectValueAndExpectation(): void
    {
        $constraint = new StringStartsWith('prefix');

        try {
            $constraint->evaluate('error');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
Failed asserting that 'error' starts with "prefix".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintStringStartsWithNotCorrectValueExceptionAndCustomMessage(): void
    {
        $constraint = new StringStartsWith('prefix');

        try {
            $constraint->evaluate('error', 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<EOF
custom message
Failed asserting that 'error' starts with "prefix".

EOF
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }
}
