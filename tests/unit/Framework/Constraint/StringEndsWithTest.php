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

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Util\ThrowableToStringMapper;

#[CoversClass(StringEndsWith::class)]
#[Small]
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
                <<<'EOF'
Failed asserting that 'error' ends with "suffix".

EOF
                ,
                ThrowableToStringMapper::map($e)
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
                <<<'EOF'
custom message
Failed asserting that 'error' ends with "suffix".

EOF
                ,
                ThrowableToStringMapper::map($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintStringEndsNotWith(): void
    {
        $constraint = Assert::logicalNot(
            Assert::stringEndsWith('suffix')
        );

        $this->assertTrue($constraint->evaluate('foo', '', true));
        $this->assertFalse($constraint->evaluate('foosuffix', '', true));
        $this->assertEquals('ends not with "suffix"', $constraint->toString());
        $this->assertCount(1, $constraint);

        try {
            $constraint->evaluate('foosuffix');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<'EOF'
Failed asserting that 'foosuffix' ends not with "suffix".

EOF
                ,
                ThrowableToStringMapper::map($e)
            );

            return;
        }

        $this->fail();
    }

    public function testConstraintStringEndsNotWith2(): void
    {
        $constraint = Assert::logicalNot(
            Assert::stringEndsWith('suffix')
        );

        try {
            $constraint->evaluate('foosuffix', 'custom message');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<'EOF'
custom message
Failed asserting that 'foosuffix' ends not with "suffix".

EOF
                ,
                ThrowableToStringMapper::map($e)
            );

            return;
        }

        $this->fail();
    }
}
