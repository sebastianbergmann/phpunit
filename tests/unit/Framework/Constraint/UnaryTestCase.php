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

abstract class UnaryTestCase extends OperatorTestCase
{
    /**
     * Shall return the name of the operator under test
     */
    abstract public static function getOperatorName(): string;

    /**
     * Shall return the precedence of the operator under test
     */
    abstract public static function getOperatorPrecedence(): int;

    /**
     * Shall return series of two-element arrays [$input, $expected].
     */
    abstract public function providerToStringWithNativeTransformations();

    /**
     * Takes a boolean values and returns the expected evaluation result for
     * the logical operator under test.
     */
    abstract public function evaluateExpectedResult(bool $input): bool;

    final public function testIsSubclassOfOperator(): void
    {
        $className = $this->className();

        $reflection = new \ReflectionClass($className);

        $this->assertTrue($reflection->isSubclassOf(Operator::class), \sprintf(
            'Failed to assert that "%s" is subclass of "%s".',
            $className,
            Operator::class
        ));
    }

    public function testOperatorName(): void
    {
        $className  = $this->className();
        $constraint = new $className($this->getMockBuilder(Constraint::class)->getMock());
        $this->assertSame($this->getOperatorName(), $constraint->operator());
    }

    public function testOperatorPrecedence(): void
    {
        $className  = $this->className();
        $constraint = new $className($this->getMockBuilder(Constraint::class)->getMock());
        $this->assertSame($this->getOperatorPrecedence(), $constraint->precedence());
    }

    public function testOperatorCount(): void
    {
        $className = $this->className();

        $constraint = new $className(\CountConstraint::fromCount(3));

        $this->assertSame(3, $constraint->count());
    }

    public function testOperatorArity(): void
    {
        $className = $this->className();

        $constraint = new $className(\CountConstraint::fromCount(3));

        $this->assertSame(1, $constraint->arity());
    }

    public function testConstructorAcceptsConstraintArgument(): void
    {
        $className = $this->className();

        $nice = $this->getMockBuilder(Constraint::class)
                     ->setMethods(['toStringInContext'])
                     ->getMockForAbstractClass();

        $constraint = new $className($nice);

        $string = 'is ' . $this->getOperatorName() . ' nice';

        $nice->expects($this->once())
             ->method('toStringInContext')
             ->with($this->identicalTo($constraint), 1)
             ->willReturn($string);

        $this->assertSame($string, $constraint->toString());
    }

    public function testNonRestrictedConstructParameterIsTreatedAsIsEqual(): void
    {
        $className = $this->className();

        $constraint = new $className('test');

        $withIsEqual = new $className(new IsEqual('test'));

        $this->assertSame($withIsEqual->toString(), $constraint->toString());
    }

    public function providerUnaryTruthTable()
    {
        return \array_map(function (bool $input): array {
            return [$input, $this->evaluateExpectedResult($input)];
        }, [false, true]);
    }

    /**
     * @dataProvider providerUnaryTruthTable
     */
    public function testEvaluateReturnsCorrectBooleanResult(bool $input, bool $expected): void
    {
        $operand = \BooleanConstraint::fromBool($input);

        $className = $this->className();

        $constraint = new $className($operand);

        $message = 'Failed asserting that ' . $constraint->toString() . ' is ' . ($expected ? 'true' : 'false');
        $this->assertSame($expected, $constraint->evaluate(null, '', true), $message);
    }

    /**
     * @dataProvider providerUnaryTruthTable
     */
    public function testEvaluateReturnsNullOnSuccessAndThrowsExceptionOnFailure(bool $input, bool $expected): void
    {
        $operand = \BooleanConstraint::fromBool($input);

        $className = $this->className();

        $constraint = new $className($operand);

        if ($expected) {
            $this->assertNull($constraint->evaluate(null));

            return;
        }

        $expectedString = $operand->toString();
        $message        = "Failed asserting that 'the following expression is not true' " . $expectedString;
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($message);
        $constraint->evaluate('the following expression is true');
    }

    /**
     * @dataProvider providerToStringWithNativeTransformations
     */
    public function testToStringWithNativeTransformations(string $input, string $expected): void
    {
        $operand = \NamedConstraint::fromName($input);

        $className = $this->className();

        $constraint = new $className($operand);

        $this->assertSame($expected, $constraint->toString());
    }

    public function testToStringWithNonContextualNonOperatorConstraint(): void
    {
        $methods = [
            'toStringInContext',
            'toString',
        ];

        $operand = $this->getMockBuilder(Constraint::class)
                        ->setMethods($methods)
                        ->getMockForAbstractClass();

        $className = $this->className();

        $operator = new $className($operand);

        $string = 'John Smith';

        $operand->expects($this->once())
                ->method('toStringInContext')
                ->with($this->identicalTo($operator), 1)
                ->willReturn(null);
        $operand->expects($this->once())
                ->method('toString')
                ->with()
                ->willReturn($string);

        $this->assertSame($string, $operator->toString());
    }

    public function testToStringWithContextualNonOperatorConstraint(): void
    {
        $methods = [
            'toStringInContext',
            'toString',
        ];

        $operand = $this->getMockBuilder(Constraint::class)
                        ->setMethods($methods)
                        ->getMockForAbstractClass();

        $className = $this->className();

        $operator = new $className($operand);

        $string = 'John ' . $this->getOperatorName() . ' Smith';

        $operand->expects($this->once())
                ->method('toStringInContext')
                ->with($this->identicalTo($operator), 1)
                ->willReturn($string);
        $operand->expects($this->never())
                ->method('toString');

        $this->assertSame($string, $operator->toString());
    }

    public function testToStringWithContextualUnaryOperator(): void
    {
        $methods = [
            'toStringInContext',
            'toString',
            'arity',
            'precedence',
        ];

        $operand = $this->getMockBuilder(Operator::class)
                        ->setMethods($methods)
                        ->getMockForAbstractClass();

        $className = $this->className();

        $operator = new $className($operand);

        $string = 'John ' . $this->getOperatorName() . ' Smith';

        $operand->expects($this->once())
                ->method('arity')
                ->with()
                ->willReturn(1);
        $operand->expects($this->never())
                ->method('precedence');
        $operand->expects($this->once())
                ->method('toStringInContext')
                ->with($this->identicalTo($operator), 1)
                ->willReturn($string);
        $operand->expects($this->never())
                ->method('toString');

        $this->assertSame($string, $operator->toString());
    }

    public function testToStringWithNonContextualBinaryOperatorOfHigherPrecedence(): void
    {
        $methods = [
            'toStringInContext',
            'toString',
            'arity',
            'precedence',
        ];

        $operand = $this->getMockBuilder(Operator::class)
                        ->setMethods($methods)
                        ->getMockForAbstractClass();

        $className = $this->className();

        $operator = new $className($operand);

        $string = 'tree of apples';

        $operand->expects($this->once())
                ->method('arity')
                ->with()
                ->willReturn(2);
        $operand->expects($this->once())
                ->method('precedence')
                ->with()
                ->willReturn(-1);
        $operand->expects($this->once())
                ->method('toStringInContext')
                ->with($this->identicalTo($operator), 1)
                ->willReturn(null);
        $operand->expects($this->once())
                ->method('toString')
                ->with()
                ->willReturn($string);

        $this->assertSame($string, $operator->toString());
    }

    public function testToStringWithContextualBinaryOperatorOfHigherPrecedence(): void
    {
        $methods = [
            'toStringInContext',
            'toString',
            'arity',
            'precedence',
        ];

        $operand = $this->getMockBuilder(Operator::class)
                        ->setMethods($methods)
                        ->getMockForAbstractClass();

        $className = $this->className();

        $operator = new $className($operand);

        $string = 'tree of apples';

        $operand->expects($this->once())
                ->method('arity')
                ->with()
                ->willReturn(2);
        $operand->expects($this->once())
                ->method('precedence')
                ->with()
                ->willReturn(-1);
        $operand->expects($this->once())
                ->method('toStringInContext')
                ->with($this->identicalTo($operator), 1)
                ->willReturn($string);
        $operand->expects($this->never())
                ->method('toString');

        $this->assertSame($string, $operator->toString());
    }

    public function testToStringWithBinaryOperatorOfLowerPrecedence(): void
    {
        $methods = [
            'toStringInContext',
            'toString',
            'arity',
            'precedence',
        ];

        $operand = $this->getMockBuilder(Operator::class)
                        ->setMethods($methods)
                        ->getMockForAbstractClass();

        $className = $this->className();

        $operator = new $className($operand);

        $string = 'apple or banana';

        $operand->expects($this->once())
                ->method('arity')
                ->with()
                ->willReturn(2);
        $operand->expects($this->once())
                ->method('precedence')
                ->with()
                ->willReturn(10000);
        $operand->expects($this->never())
                ->method('toStringInContext');
        $operand->expects($this->once())
                ->method('toString')
                ->with()
                ->willReturn($string);

        $expected = $this->getOperatorName() . '( ' . $string . ' )';
        $this->assertSame($expected, $operator->toString());
    }

    /**
     * @dataProvider providerToStringWithNativeTransformations
     */
    public function testFailureDescriptionWithNativeTransformations(string $input, string $expected): void
    {
        $operand = \NamedConstraint::fromName($input);

        $className = $this->className();

        $constraint = new $className($operand);

        $method = (new \ReflectionMethod($className, 'failureDescription'));
        $method->setAccessible(true);

        $this->assertSame("'whatever' " . $expected, $method->invokeArgs($constraint, ['whatever']));
    }

    public function testFailureDescriptionWithNonContextualNonOperatorConstraint(): void
    {
        $methods = [
            'toStringInContext',
            'toString',
        ];

        $operand = $this->getMockBuilder(Constraint::class)
                        ->setMethods($methods)
                        ->getMockForAbstractClass();

        $className = $this->className();

        $operator = new $className($operand);

        $string = 'John Smith';

        $operand->expects($this->once())
                ->method('toStringInContext')
                ->with($this->identicalTo($operator), 1)
                ->willReturn(null);
        $operand->expects($this->once())
                ->method('toString')
                ->with()
                ->willReturn($string);

        $method = (new \ReflectionMethod($className, 'failureDescription'));
        $method->setAccessible(true);

        $expected = "'whatever' " . $string;

        $this->assertSame($expected, $method->invokeArgs($operator, ['whatever']));
    }

    public function testFailureDescriptionWithContextualNonOperatorConstraint(): void
    {
        $methods = [
            'toStringInContext',
            'toString',
        ];

        $operand = $this->getMockBuilder(Constraint::class)
                        ->setMethods($methods)
                        ->getMockForAbstractClass();

        $className = $this->className();

        $operator = new $className($operand);

        $string = 'John ' . $this->getOperatorName() . ' Smith';

        $operand->expects($this->once())
                ->method('toStringInContext')
                ->with($this->identicalTo($operator), 1)
                ->willReturn($string);
        $operand->expects($this->never())
                ->method('toString');

        $method = (new \ReflectionMethod($className, 'failureDescription'));
        $method->setAccessible(true);

        $expected = "'whatever' " . $string;

        $this->assertSame($expected, $method->invokeArgs($operator, ['whatever']));
    }

    public function testFailureDescriptionWithContextualUnaryOperator(): void
    {
        $methods = [
            'toStringInContext',
            'toString',
            'arity',
            'precedence',
        ];

        $operand = $this->getMockBuilder(Operator::class)
                        ->setMethods($methods)
                        ->getMockForAbstractClass();

        $className = $this->className();

        $operator = new $className($operand);

        $string = 'John ' . $this->getOperatorName() . ' Smith';

        $operand->expects($this->once())
                ->method('arity')
                ->with()
                ->willReturn(1);
        $operand->expects($this->never())
                ->method('precedence');
        $operand->expects($this->once())
                ->method('toStringInContext')
                ->with($this->identicalTo($operator), 1)
                ->willReturn($string);
        $operand->expects($this->never())
                ->method('toString');

        $method = (new \ReflectionMethod($className, 'failureDescription'));
        $method->setAccessible(true);

        $expected = "'whatever' " . $string;

        $this->assertSame($expected, $method->invokeArgs($operator, ['whatever']));
    }

    public function testFailureDescriptionWithNonContextualBinaryOperatorOfHigherPrecedence(): void
    {
        $methods = [
            'toStringInContext',
            'toString',
            'arity',
            'precedence',
        ];

        $operand = $this->getMockBuilder(Operator::class)
                        ->setMethods($methods)
                        ->getMockForAbstractClass();

        $className = $this->className();

        $operator = new $className($operand);

        $string = 'tree of apples';

        $operand->expects($this->once())
                ->method('arity')
                ->with()
                ->willReturn(2);
        $operand->expects($this->once())
                ->method('precedence')
                ->with()
                ->willReturn(-1);
        $operand->expects($this->once())
                ->method('toStringInContext')
                ->with($this->identicalTo($operator), 1)
                ->willReturn(null);
        $operand->expects($this->once())
                ->method('toString')
                ->with()
                ->willReturn($string);

        $method = (new \ReflectionMethod($className, 'failureDescription'));
        $method->setAccessible(true);

        $expected = "'whatever' " . $string;

        $this->assertSame($expected, $method->invokeArgs($operator, ['whatever']));
    }

    public function testFailureDescriptionWithContextualBinaryOperatorOfHigherPrecedence(): void
    {
        $methods = [
            'toStringInContext',
            'toString',
            'arity',
            'precedence',
        ];

        $operand = $this->getMockBuilder(Operator::class)
                        ->setMethods($methods)
                        ->getMockForAbstractClass();

        $className = $this->className();

        $operator = new $className($operand);

        $string = 'tree of apples';

        $operand->expects($this->once())
                ->method('arity')
                ->with()
                ->willReturn(2);
        $operand->expects($this->once())
                ->method('precedence')
                ->with()
                ->willReturn(-1);
        $operand->expects($this->once())
                ->method('toStringInContext')
                ->with($this->identicalTo($operator), 1)
                ->willReturn($string);
        $operand->expects($this->never())
                ->method('toString');

        $method = (new \ReflectionMethod($className, 'failureDescription'));
        $method->setAccessible(true);

        $expected = "'whatever' " . $string;

        $this->assertSame($expected, $method->invokeArgs($operator, ['whatever']));
    }

    public function testFailureDescriptionWithBinaryOperatorOfLowerPrecedence(): void
    {
        $methods = [
            'toStringInContext',
            'toString',
            'arity',
            'precedence',
        ];

        $operand = $this->getMockBuilder(Operator::class)
                        ->setMethods($methods)
                        ->getMockForAbstractClass();

        $className = $this->className();

        $operator = new $className($operand);

        $string = 'apple or banana';

        $operand->expects($this->once())
                ->method('arity')
                ->with()
                ->willReturn(2);
        $operand->expects($this->once())
                ->method('precedence')
                ->with()
                ->willReturn(10000);
        $operand->expects($this->never())
                ->method('toStringInContext');
        $operand->expects($this->once())
                ->method('toString')
                ->with()
                ->willReturn($string);

        $method = (new \ReflectionMethod($className, 'failureDescription'));
        $method->setAccessible(true);

        $expected = $this->getOperatorName() . "( 'whatever' " . $string . ' )';

        $this->assertSame($expected, $method->invokeArgs($operator, ['whatever']));
    }
}
