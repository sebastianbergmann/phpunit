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

use function array_fill;
use function array_map;
use function array_slice;
use function array_sum;
use function count;
use function decbin;
use function implode;
use function sprintf;
use function str_split;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\TestFixture\BooleanConstraint;
use PHPUnit\TestFixture\CountConstraint;
use PHPUnit\TestFixture\NamedConstraint;
use ReflectionClass;
use ReflectionMethod;

abstract class BinaryOperatorTestCase extends OperatorTestCase
{
    /**
     * Shall return the name of the operator under test.
     */
    abstract public static function getOperatorName(): string;

    /**
     * Shall return the precedence of the operator under test.
     */
    abstract public static function getOperatorPrecedence(): int;

    /**
     * Takes an array or boolean values and returns the expected evaluation
     * result for the logical operator under test.
     */
    abstract public function evaluateExpectedResult(array $input): bool;

    final public function testIsSubclassOfOperator(): void
    {
        $className = $this->className();

        $reflection = new ReflectionClass($className);

        $this->assertTrue($reflection->isSubclassOf(Operator::class), sprintf(
            'Failed to assert that "%s" is subclass of "%s".',
            $className,
            Operator::class
        ));
    }

    final public function testOperatorName(): void
    {
        $className  = $this->className();
        $constraint = new $className;
        $this->assertSame($this->getOperatorName(), $constraint->operator());
    }

    final public function testOperatorPrecedence(): void
    {
        $className  = $this->className();
        $constraint = new $className;
        $this->assertSame($this->getOperatorPrecedence(), $constraint->precedence());
    }

    final public function testOperatorCount(): void
    {
        $counts = [
            3,
            5,
            8,
        ];

        $constraints = array_map(static function ($count)
        {
            return CountConstraint::fromCount($count);
        }, $counts);

        $className = $this->className();

        $constraint = new $className;

        $constraint->setConstraints($constraints);

        $expected = array_sum($counts);

        $this->assertSame($expected, $constraint->count());
    }

    final public function testOperatorArity(): void
    {
        $constraints = [
            CountConstraint::fromCount(3),
            CountConstraint::fromCount(5),
            CountConstraint::fromCount(8),
        ];

        $className = $this->className();

        $constraint = new $className;

        $constraint->setConstraints($constraints);

        $expected = count($constraints);

        $this->assertSame($expected, $constraint->arity());
    }

    public function testFromConstraints(): void
    {
        $operand   = $this->getMockBuilder(Constraint::class)->getMock();
        $className = $this->className();

        for ($arity = 0; $arity <= 3; $arity++) {
            $constraints = array_fill(0, $arity, $operand);
            $constraint  = $className::fromConstraints(...$constraints);

            $this->assertInstanceOf($className, $constraint);

            $this->assertSame($arity, $constraint->arity());
        }
    }

    public function testSetConstraintsHandlesNonConstraintArguments(): void
    {
        $className = $this->className();

        $constraint = new $className;

        $constraint->setConstraints(['whatever']);

        $this->assertTrue($constraint->evaluate('whatever', '', true));

        $this->assertSame("is equal to 'whatever'", $constraint->toString());
    }

    final public function providerConnectiveTruthTable()
    {
        $inputs = self::getBooleanTuples(0, 5);

        return array_map(function (array $input)
        {
            return [$input, $this->evaluateExpectedResult($input)];
        }, $inputs);
    }

    /**
     * @dataProvider providerConnectiveTruthTable
     */
    final public function testEvaluateReturnsCorrectBooleanResult(array $inputs, bool $expected): void
    {
        $constraints = array_map(static function (bool $input)
        {
            return BooleanConstraint::fromBool($input);
        }, $inputs);

        $className = $this->className();

        $constraint = $className::fromConstraints(...$constraints);

        $message = 'Failed asserting that ' . $constraint->toString() . ' is ' . ($expected ? 'true' : 'false');
        $this->assertSame($expected, $constraint->evaluate(null, '', true), $message);
    }

    /**
     * @dataProvider providerConnectiveTruthTable
     */
    final public function testEvaluateReturnsNullOnSuccessAndThrowsExceptionOnFailure(array $inputs, bool $expected): void
    {
        $constraints = array_map(static function (bool $input)
        {
            return BooleanConstraint::fromBool($input);
        }, $inputs);

        $className = $this->className();

        $constraint = $className::fromConstraints(...$constraints);

        if ($expected) {
            $this->assertNull($constraint->evaluate(null));
        } else {
            $expectedString = self::operatorJoinStrings(
                array_map(
                    static function (Constraint $operand)
                    {
                        return $operand->toString();
                    },
                    $constraints
                )
            );
            $message = "Failed asserting that 'the following expression is true' " . $expectedString;
            $this->expectException(ExpectationFailedException::class);
            $this->expectExceptionMessage($message);
            $constraint->evaluate('the following expression is true');
        }
    }

    public function providerToStringWithNamedConstraints(): array
    {
        return [
            [
            ],
            [
                'is healthy',
            ],
            [
                'is healthy',
                'is rich in amino acids',
            ],
            [
                'is healthy',
                'is rich in amino acids',
                'is rich in unsaturated fats',
            ],
        ];
    }

    /**
     * @dataProvider providerToStringWithNamedConstraints
     */
    public function testToStringWithNamedConstraints(string ...$names): void
    {
        $constraints = array_map(static function (string $name)
        {
            return NamedConstraint::fromName($name);
        }, $names);

        $className = $this->className();

        $constraint = $className::fromConstraints(...$constraints);

        $expected = static::operatorJoinStrings($names);

        $this->assertSame($expected, $constraint->toString());
    }

    public function testToStringWithoutOperands(): void
    {
        $className = $this->className();

        $operator = $className::fromConstraints();

        $this->assertSame('', $operator->toString());
    }

    public function testToStringWithSingleOperand(): void
    {
        $methods = [
            'arity',
            'precedence',
            'toStringInContext',
            'toString',
        ];

        $operand = $this->getMockBuilder(Operator::class)
                        ->onlyMethods($methods)
                        ->getMockForAbstractClass();

        $className = $this->className();

        $operator = $className::fromConstraints($operand);

        // A non-contextual operator
        $operand->expects($this->never())
                ->method('arity');
        $operand->expects($this->never())
                ->method('precedence');
        $operand->expects($this->never())
                ->method('toStringInContext');
        $operand->expects($this->once())
                ->method('toString')
                ->with()
                ->willReturn('is the only');

        $this->assertSame('is the only', $operator->toString());
    }

    public function testToStringWithMultipleOperands(): void
    {
        $constraintMethods = [
            'toStringInContext',
            'toString',
        ];

        $operatorMethods = [
            'arity',
            'precedence',
            'toStringInContext',
            'toString',
        ];

        $constraints = [
            $this->getMockBuilder(Constraint::class)
                 ->onlyMethods($constraintMethods)
                 ->getMockForAbstractClass(),
            $this->getMockBuilder(Constraint::class)
                 ->onlyMethods($constraintMethods)
                 ->getMockForAbstractClass(),
            $this->getMockBuilder(Operator::class)
                 ->onlyMethods($operatorMethods)
                 ->getMockForAbstractClass(),
            $this->getMockBuilder(Operator::class)
                 ->onlyMethods($operatorMethods)
                 ->getMockForAbstractClass(),
            $this->getMockBuilder(Operator::class)
                 ->onlyMethods($operatorMethods)
                 ->getMockForAbstractClass(),
        ];

        $className = $this->className();

        $constraint = $className::fromConstraints(...$constraints);

        // A non-contextual non-operator constraint
        $constraints[0]->expects($this->once())
                       ->method('toStringInContext')
                       ->with($this->identicalTo($constraint), 0)
                       ->willReturn('');
        $constraints[0]->expects($this->once())
                       ->method('toString')
                       ->with()
                       ->willReturn('is first');

        // A contextual non-operator constraint
        $constraints[1]->expects($this->once())
                       ->method('toStringInContext')
                       ->with($this->identicalTo($constraint), 1)
                       ->willReturn('is second');
        $constraints[1]->expects($this->never())
                       ->method('toString');

        // An non-contextual operator constraint with arity = 2 and high precedence (no braces needed)
        $constraints[2]->expects($this->once())
                       ->method('arity')
                       ->with()
                       ->willReturn(2);
        $constraints[2]->expects($this->once())
                       ->method('precedence')
                       ->with()
                       ->willReturn(-1);
        $constraints[2]->expects($this->once())
                       ->method('toStringInContext')
                       ->with($this->identicalTo($constraint), 2)
                       ->willReturn('');
        $constraints[2]->expects($this->once())
                       ->method('toString')
                       ->with()
                       ->willReturn('is third');

        // A contextual operator constraint with arity = 1 (no braces needed)
        $constraints[3]->expects($this->once())
                       ->method('arity')
                       ->with()
                       ->willReturn(1);
        $constraints[3]->expects($this->never())
                       ->method('precedence');
        $constraints[3]->expects($this->once())
                       ->method('toStringInContext')
                       ->with($this->identicalTo($constraint), 3)
                       ->willReturn('is fourth');
        $constraints[3]->expects($this->never())
                       ->method('toString');

        // An operator constraint with arity = 2 and low precedence (braces needed)
        $constraints[4]->expects($this->once())
                       ->method('arity')
                       ->with()
                       ->willReturn(2);
        $constraints[4]->expects($this->once())
                       ->method('precedence')
                       ->with()
                       ->willReturn(10000);
        $constraints[4]->expects($this->never())
                       ->method('toStringInContext');
        $constraints[4]->expects($this->once())
                       ->method('toString')
                       ->with()
                       ->willReturn('is fifth or later');

        $expected = self::operatorJoinStrings([
            'is first',
            'is second',
            'is third',
            'is fourth',
            '( is fifth or later )',
        ]);

        $this->assertSame($expected, $constraint->toString());
    }

    public function testFailureDescriptionWithSingleOperand(): void
    {
        $methods = [
            'arity',
            'precedence',
            'toStringInContext',
            'toString',
        ];

        $operand = $this->getMockBuilder(Operator::class)
                        ->onlyMethods($methods)
                        ->getMockForAbstractClass();

        $className = $this->className();

        $operator = $className::fromConstraints($operand);

        // A non-contextual operator with toString()
        $operand->expects($this->never())
                ->method('arity');
        $operand->expects($this->never())
                ->method('precedence');
        $operand->expects($this->never())
                ->method('toStringInContext');
        $operand->expects($this->once())
                ->method('toString')
                ->with()
                ->willReturn('is the only');

        $method = new ReflectionMethod($className, 'failureDescription');
        $method->setAccessible(true);

        $this->assertSame("'whatever' is the only", $method->invoke($operator, 'whatever'));
    }

    public function testFailureDescriptionWithMultipleOperands(): void
    {
        $constraintMethods = [
            'toStringInContext',
            'toString',
        ];

        $operatorMethods = [
            'arity',
            'precedence',
            'toStringInContext',
            'toString',
        ];

        $constraints = [
            $this->getMockBuilder(Constraint::class)
                 ->onlyMethods($constraintMethods)
                 ->getMockForAbstractClass(),
            $this->getMockBuilder(Constraint::class)
                 ->onlyMethods($constraintMethods)
                 ->getMockForAbstractClass(),
            $this->getMockBuilder(Operator::class)
                 ->onlyMethods($operatorMethods)
                 ->getMockForAbstractClass(),
            $this->getMockBuilder(Operator::class)
                 ->onlyMethods($operatorMethods)
                 ->getMockForAbstractClass(),
            $this->getMockBuilder(Operator::class)
                 ->onlyMethods($operatorMethods)
                 ->getMockForAbstractClass(),
        ];

        $className = $this->className();

        $constraint = $className::fromConstraints(...$constraints);

        // A non-contextual non-operator constraint
        $constraints[0]->expects($this->once())
                       ->method('toStringInContext')
                       ->with($this->identicalTo($constraint), 0)
                       ->willReturn('');
        $constraints[0]->expects($this->once())
                       ->method('toString')
                       ->with()
                       ->willReturn('is first');

        // A contextual non-operator constraint
        $constraints[1]->expects($this->once())
                       ->method('toStringInContext')
                       ->with($this->identicalTo($constraint), 1)
                       ->willReturn('is second');
        $constraints[1]->expects($this->never())
                       ->method('toString');

        // An non-contextual operator constraint with arity = 2 and high precedence (no braces needed)
        $constraints[2]->expects($this->once())
                       ->method('arity')
                       ->with()
                       ->willReturn(2);
        $constraints[2]->expects($this->once())
                       ->method('precedence')
                       ->with()
                       ->willReturn(-1);
        $constraints[2]->expects($this->once())
                       ->method('toStringInContext')
                       ->with($this->identicalTo($constraint), 2)
                       ->willReturn('');
        $constraints[2]->expects($this->once())
                       ->method('toString')
                       ->with()
                       ->willReturn('is third');

        // A contextual operator constraint with arity = 1 (no braces needed)
        $constraints[3]->expects($this->once())
                       ->method('arity')
                       ->with()
                       ->willReturn(1);
        $constraints[3]->expects($this->never())
                       ->method('precedence');
        $constraints[3]->expects($this->once())
                       ->method('toStringInContext')
                       ->with($this->identicalTo($constraint), 3)
                       ->willReturn('is fourth');
        $constraints[3]->expects($this->never())
                       ->method('toString');

        // An operator constraint with arity = 2 and low precedence (braces needed)
        $constraints[4]->expects($this->once())
                       ->method('arity')
                       ->with()
                       ->willReturn(2);
        $constraints[4]->expects($this->once())
                       ->method('precedence')
                       ->with()
                       ->willReturn(10000);
        $constraints[4]->expects($this->never())
                       ->method('toStringInContext');
        $constraints[4]->expects($this->once())
                       ->method('toString')
                       ->with()
                       ->willReturn('is fifth or later');

        $method = new ReflectionMethod($className, 'failureDescription');
        $method->setAccessible(true);

        $expectedToString = self::operatorJoinStrings([
            'is first',
            'is second',
            'is third',
            'is fourth',
            '( is fifth or later )',
        ]);
        $expected = "'whatever' " . $expectedToString;
        $this->assertSame($expected, $method->invokeArgs($constraint, ['whatever']));
    }

    /**
     * Generates an array of "binary tuples" of size $minSize up to (and
     * including) $maxSize.
     *
     * A "binary tuple" is an array of 0s an 1s. The method generates all
     * possible combinations of 0s and 1s of size $minSize up to $maxSize.
     */
    final protected static function getBinaryTuples(int $minSize, int $maxSize): array
    {
        $tuples = [];

        for ($size = $minSize; $size <= $maxSize; $size++) {
            for ($num = 0; $num < 2 ** $size; $num++) {
                $str      = decbin($num | 2 ** $size);                      // "1xyz" (extra "1" on the left)
                $bits     = array_map('intval', str_split($str));          // ["1", "x", "y", "z"]
                $tuple    = array_slice($bits, 1);                          // ["x", "y", "z"]
                $tuples[] = $tuple;
            }
        }

        return $tuples;
    }

    /**
     * Same as getBinaryTuples(), but returns tuples of boolean values
     * instead of integers.
     */
    final protected static function getBooleanTuples(int $minSize, int $maxSize): array
    {
        $tuples = self::getBinaryTuples($minSize, $maxSize);

        return array_map(static function ($tuple)
        {
            return array_map('boolval', $tuple);
        }, $tuples);
    }

    protected static function operatorJoinStrings(array $strings): string
    {
        return implode(' ' . static::getOperatorName() . ' ', $strings);
    }
}
