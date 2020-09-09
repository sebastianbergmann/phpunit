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
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
final class LogicalExpressionsTest extends TestCase
{
    public function testLogicalNotOfDegenerateLogicalAnd(): void
    {
        $constraint = new LogicalNot(
            LogicalAnd::fromConstraints(
                new IsNull(),
            )
        );

        $this->assertTrue($constraint->evaluate('string', '', true));
        $this->assertFalse($constraint->evaluate(null, '', true));

        $string = 'is not null';
        $this->assertSame($string, $constraint->toString());

        $message = 'Failed asserting that null is not null';
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($message);

        $this->assertThat(null, $constraint);
    }

    public function testLogicalNotOfLogicalAndWithThreeArguments(): void
    {
        $constraint = new LogicalNot(
            LogicalAnd::fromConstraints(
                new IsType('int'),
                new GreaterThan(5),
                new LessThan(10)
            )
        );

        $this->assertTrue($constraint->evaluate('string', '', true));
        $this->assertTrue($constraint->evaluate(7.7, '', true));
        $this->assertTrue($constraint->evaluate(2, '', true));
        $this->assertTrue($constraint->evaluate(13, '', true));
        $this->assertFalse($constraint->evaluate(7, '', true));

        $string = 'not( is of type "int" and is greater than 5 and is less than 10 )';
        $this->assertSame($string, $constraint->toString());

        $message = 'Failed asserting that ' .
                   'not( 7 is of type "int" and is greater than 5 and is less than 10 )' .
                   '.';
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($message);

        $this->assertThat(7, $constraint);
    }

    public function testLogicalNotOfDegenerateLogicalOr(): void
    {
        $constraint = new LogicalNot(
            LogicalOr::fromConstraints(
                new IsNull(),
            )
        );

        $this->assertTrue($constraint->evaluate('string', '', true));
        $this->assertFalse($constraint->evaluate(null, '', true));

        $string = 'is not null';
        $this->assertSame($string, $constraint->toString());

        $message = 'Failed asserting that null is not null';
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($message);

        $this->assertThat(null, $constraint);
    }

    public function testLogicalNotOfLogicalOrWithThreeArguments(): void
    {
        $constraint = new LogicalNot(
            LogicalOr::fromConstraints(
                new IsNull(),
                new IsType('int'),
                new IsType('array')
            )
        );

        $this->assertTrue($constraint->evaluate('string', '', true));
        $this->assertFalse($constraint->evaluate([], '', true));
        $this->assertFalse($constraint->evaluate(2, '', true));
        $this->assertFalse($constraint->evaluate(null, '', true));

        $string = 'not( is null or is of type "int" or is of type "array" )';
        $this->assertSame($string, $constraint->toString());

        $message = 'Failed asserting that ' .
                   'not( 2 is null or is of type "int" or is of type "array" )' .
                   '.';
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($message);

        $this->assertThat(2, $constraint);
    }

    public function testLogicalNotOfDegenerateLogicalXor(): void
    {
        $constraint = new LogicalNot(
            LogicalXor::fromConstraints(
                new IsNull(),
            )
        );

        $this->assertTrue($constraint->evaluate('string', '', true));
        $this->assertFalse($constraint->evaluate(null, '', true));

        $string = 'is not null';
        $this->assertSame($string, $constraint->toString());

        $message = 'Failed asserting that null is not null';
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($message);

        $this->assertThat(null, $constraint);
    }

    public function testLogicalNotOfLogicalXorWithTwoArguments(): void
    {
        $constraint = new LogicalNot(
            LogicalXor::fromConstraints(
                new IsType('int'),
                new IsEqual(false),
            )
        );

        $this->assertTrue($constraint->evaluate(0, '', true));
        $this->assertFalse($constraint->evaluate('', '', true));
        $this->assertFalse($constraint->evaluate(1, '', true));

        $string = 'not( is of type "int" xor is equal to false )';
        $this->assertSame($string, $constraint->toString());

        $message = 'Failed asserting that ' .
                   'not( 1 is of type "int" xor is equal to false )' .
                   '.';
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($message);

        $this->assertThat(1, $constraint);
    }

    public function testTwoLevelNestedLogicalNotOfTerminalConstraint(): void
    {
        $terminal   = new GreaterThan(5);
        $constraint = new LogicalNot(new LogicalNot($terminal));

        $this->assertTrue($constraint->evaluate(6, '', true));
        $this->assertFalse($constraint->evaluate(5, '', true));
        $this->assertSame('is greater than 5', $constraint->toString());

        $message = 'Failed asserting that 5 is greater than 5';
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($message);

        $this->assertThat(5, $constraint);
    }

    public function testThreeLevelNestedLogicalNotOfTerminalConstraint(): void
    {
        $terminal   = new GreaterThan(5);
        $constraint = new LogicalNot(new LogicalNot(new LogicalNot($terminal)));

        $this->assertFalse($constraint->evaluate(6, '', true));
        $this->assertTrue($constraint->evaluate(5, '', true));
        $this->assertSame('is not greater than 5', $constraint->toString());

        $message = 'Failed asserting that 6 is not greater than 5';
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($message);

        $this->assertThat(6, $constraint);
    }

    public function testFourLevelNestedLogicalNotOfTerminalConstraint(): void
    {
        $terminal   = new GreaterThan(5);
        $constraint = new LogicalNot(new LogicalNot(new LogicalNot(new LogicalNot($terminal))));

        $this->assertTrue($constraint->evaluate(6, '', true));
        $this->assertFalse($constraint->evaluate(5, '', true));
        $this->assertSame('is greater than 5', $constraint->toString());

        $message = 'Failed asserting that 5 is greater than 5';
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($message);

        $this->assertThat(5, $constraint);
    }

    public function testTwoLevelNestedLogicalNotOfLogicalAndWithSingleOperand(): void
    {
        $subexpr    = LogicalAnd::fromConstraints(new IsEqual(5));
        $constraint = new LogicalNot(new LogicalNot($subexpr));

        $this->assertTrue($constraint->evaluate(5, '', true));
        $this->assertFalse($constraint->evaluate(3, '', true));

        $string = 'is equal to 5';
        $this->assertSame($string, $constraint->toString());

        $message = 'Failed asserting that 3 is equal to 5.';
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($message);

        $this->assertThat(3, $constraint);
    }

    public function testTwoLevelNestedLogicalNotOfLogicalOrWithSingleOperand(): void
    {
        $subexpr    = LogicalOr::fromConstraints(new IsEqual(5));
        $constraint = new LogicalNot(new LogicalNot($subexpr));

        $this->assertTrue($constraint->evaluate(5, '', true));
        $this->assertFalse($constraint->evaluate(3, '', true));

        $string = 'is equal to 5';
        $this->assertSame($string, $constraint->toString());

        $message = 'Failed asserting that 3 is equal to 5.';
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($message);

        $this->assertThat(3, $constraint);
    }

    public function testTwoLevelNestedLogicalNotOfLogicalXorWithSingleOperand(): void
    {
        $subexpr    = LogicalXor::fromConstraints(new IsEqual(5));
        $constraint = new LogicalNot(new LogicalNot($subexpr));

        $this->assertTrue($constraint->evaluate(5, '', true));
        $this->assertFalse($constraint->evaluate(3, '', true));

        $string = 'is equal to 5';
        $this->assertSame($string, $constraint->toString());

        $message = 'Failed asserting that 3 is equal to 5.';
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($message);

        $this->assertThat(3, $constraint);
    }

    public function testTwoLevelNestedLogicalNotOfLogicalOrWithThreeOperands(): void
    {
        $subexpr    = LogicalOr::fromConstraints(new LessThan(5), new IsEqual(10), new GreaterThan(15));
        $constraint = new LogicalNot(new LogicalNot($subexpr));

        $this->assertTrue($constraint->evaluate(4, '', true));
        $this->assertTrue($constraint->evaluate(10, '', true));
        $this->assertTrue($constraint->evaluate(16, '', true));
        $this->assertFalse($constraint->evaluate(5, '', true));
        $this->assertFalse($constraint->evaluate(15, '', true));

        $string = 'is less than 5 or is equal to 10 or is greater than 15';
        $this->assertSame($string, $constraint->toString());

        $message = 'Failed asserting that ' .
                   '7 is less than 5 or is equal to 10 or is greater than 15' .
                   '.';

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($message);

        $this->assertThat(7, $constraint);
    }

    public function testThreeLevelNestedLogicalNotOfLogicalOrWithThreeOperands(): void
    {
        $subexpr    = LogicalOr::fromConstraints(new LessThan(5), new IsEqual(10), new GreaterThan(15));
        $constraint = new LogicalNot(new LogicalNot(new LogicalNot($subexpr)));

        $this->assertFalse($constraint->evaluate(4, '', true));
        $this->assertFalse($constraint->evaluate(10, '', true));
        $this->assertFalse($constraint->evaluate(16, '', true));
        $this->assertTrue($constraint->evaluate(5, '', true));
        $this->assertTrue($constraint->evaluate(15, '', true));

        $string = 'not( is less than 5 or is equal to 10 or is greater than 15 )';
        $this->assertSame($string, $constraint->toString());

        $message = 'Failed asserting that ' .
                   'not( 10 is less than 5 or is equal to 10 or is greater than 15 )' .
                   '.';

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($message);

        $this->assertThat(10, $constraint);
    }

    public function testFourLevelNestedLogicalNotOfLogicalOrWithThreeOperands(): void
    {
        $subexpr    = LogicalOr::fromConstraints(new LessThan(5), new IsEqual(10), new GreaterThan(15));
        $constraint = new LogicalNot(new LogicalNot(new LogicalNot(new LogicalNot($subexpr))));

        $this->assertTrue($constraint->evaluate(4, '', true));
        $this->assertTrue($constraint->evaluate(10, '', true));
        $this->assertTrue($constraint->evaluate(16, '', true));
        $this->assertFalse($constraint->evaluate(5, '', true));
        $this->assertFalse($constraint->evaluate(15, '', true));

        $string = 'is less than 5 or is equal to 10 or is greater than 15';
        $this->assertSame($string, $constraint->toString());

        $message = 'Failed asserting that ' .
                   '7 is less than 5 or is equal to 10 or is greater than 15' .
                   '.';

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($message);

        $this->assertThat(7, $constraint);
    }

    public function testOneLevelDegenerateLogicaOrOfTerminalConstraint(): void
    {
        $terminal   = new GreaterThan(5);
        $constraint = LogicalOr::fromConstraints($terminal);

        $this->assertTrue($constraint->evaluate(6, '', true));
        $this->assertFalse($constraint->evaluate(5, '', true));
        $this->assertSame('is greater than 5', $constraint->toString());

        $message = 'Failed asserting that 5 is greater than 5';
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($message);

        $this->assertThat(5, $constraint);
    }

    public function testTwoLevelDegenerateLogicaOrOfTerminalConstraint(): void
    {
        $terminal   = new GreaterThan(5);
        $constraint = LogicalOr::fromConstraints(LogicalOr::fromConstraints($terminal));

        $this->assertTrue($constraint->evaluate(6, '', true));
        $this->assertFalse($constraint->evaluate(5, '', true));
        $this->assertSame('is greater than 5', $constraint->toString());

        $message = 'Failed asserting that 5 is greater than 5';
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($message);

        $this->assertThat(5, $constraint);
    }

    public function testThreeLevelDegenerateLogicaOrOfTerminalConstraint(): void
    {
        $terminal   = new GreaterThan(5);
        $constraint = LogicalOr::fromConstraints(LogicalOr::fromConstraints(LogicalOr::fromConstraints($terminal)));

        $this->assertTrue($constraint->evaluate(6, '', true));
        $this->assertFalse($constraint->evaluate(5, '', true));
        $this->assertSame('is greater than 5', $constraint->toString());

        $message = 'Failed asserting that 5 is greater than 5';
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($message);

        $this->assertThat(5, $constraint);
    }

    public function testLogicalAndOfTwoLogicalOr(): void
    {
        $constraint = LogicalAnd::fromConstraints(
            LogicalOr::fromConstraints(
                new IsEqual(false),
                new GreaterThan(5)
            ),
            LogicalOr::fromConstraints(
                new IsType('int'),
                new IsType('bool')
            ),
        );

        $this->assertTrue($constraint->evaluate(0, '', true));
        $this->assertTrue($constraint->evaluate(false, '', true));
        $this->assertTrue($constraint->evaluate(6, '', true));
        $this->assertFalse($constraint->evaluate(5, '', true));
        $this->assertFalse($constraint->evaluate('', '', true));
        $this->assertFalse($constraint->evaluate(true, '', true));

        $string = '( is equal to false or is greater than 5 ) and ( is of type "int" or is of type "bool" )';
        $this->assertSame($string, $constraint->toString());

        $message = 'Failed asserting that ' .
                   '5 ( is equal to false or is greater than 5 ) and ( is of type "int" or is of type "bool" )' .
                   '.';
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($message);

        $this->assertThat(5, $constraint);
    }

    public function testLogicalOrOfTwoLogicalAnd(): void
    {
        $constraint = LogicalOr::fromConstraints(
            LogicalAnd::fromConstraints(
                new IsType('bool'),
                new IsEqual(false)
            ),
            LogicalAnd::fromConstraints(
                new IsType('int'),
                new GreaterThan(5)
            )
        );

        $this->assertTrue($constraint->evaluate(false, '', true));
        $this->assertTrue($constraint->evaluate(6, '', true));
        $this->assertFalse($constraint->evaluate(5, '', true));
        $this->assertFalse($constraint->evaluate('', '', true));
        $this->assertFalse($constraint->evaluate(true, '', true));

        $string = 'is of type "bool" and is equal to false or is of type "int" and is greater than 5';
        $this->assertSame($string, $constraint->toString());

        $message = 'Failed asserting that ' .
                   '5 is of type "bool" and is equal to false or is of type "int" and is greater than 5' .
                   '.';
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($message);

        $this->assertThat(5, $constraint);
    }

    public function testLogicalOrOfLogicalAndAndLogicalNotOfLogicalAnd(): void
    {
        $constraint = LogicalOr::fromConstraints(
            LogicalAnd::fromConstraints(
                new IsType('bool'),
                new IsEqual(false)
            ),
            new LogicalNot(
                LogicalAnd::fromConstraints(
                    new IsType('int'),
                    new GreaterThan(5)
                )
            )
        );

        $this->assertTrue($constraint->evaluate(false, '', true));
        $this->assertTrue($constraint->evaluate(true, '', true));
        $this->assertTrue($constraint->evaluate(5, '', true));
        $this->assertTrue($constraint->evaluate('', '', true));
        $this->assertFalse($constraint->evaluate(6, '', true));

        $string = 'is of type "bool" and is equal to false or not( is of type "int" and is greater than 5 )';
        $this->assertSame($string, $constraint->toString());

        $message = 'Failed asserting that ' .
                   '6 is of type "bool" and is equal to false or not( is of type "int" and is greater than 5 )' .
                   '.';
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($message);

        $this->assertThat(6, $constraint);
    }

    public function testLogicalOrOfLogicalAndAndTwoLevelNestedLogicalNotOfLogicalAnd(): void
    {
        $constraint = LogicalOr::fromConstraints(
            LogicalAnd::fromConstraints(
                new IsType('bool'),
                new IsEqual(false)
            ),
            new LogicalNot(new LogicalNot(
                LogicalAnd::fromConstraints(
                    new IsType('int'),
                    new GreaterThan(5)
                )
            ))
        );

        $this->assertTrue($constraint->evaluate(false, '', true));
        $this->assertTrue($constraint->evaluate(6, '', true));
        $this->assertFalse($constraint->evaluate(5, '', true));
        $this->assertFalse($constraint->evaluate('', '', true));
        $this->assertFalse($constraint->evaluate(true, '', true));

        $string = 'is of type "bool" and is equal to false or is of type "int" and is greater than 5';
        $this->assertSame($string, $constraint->toString());

        $message = 'Failed asserting that ' .
                   '5 is of type "bool" and is equal to false or is of type "int" and is greater than 5' .
                   '.';
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($message);

        $this->assertThat(5, $constraint);
    }
}
