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

final class LogicalNotTest extends UnaryTestCase
{
    public static function getOperatorName(): string
    {
        return 'not';
    }

    public static function getOperatorPrecedence(): int
    {
        return 5;
    }

    public function providerToStringWithNativeTransformations()
    {
        return $this->providerNegate();
    }

    public function evaluateExpectedResult(bool $input): bool
    {
        return !$input;
    }

    public function providerNegate()
    {
        return [
            ['ocean contains water', 'ocean does not contain water'],
            [
                '\'this is water\' contains "water" and contains "is"',
                '\'this is water\' does not contain "water" and does not contain "is"',
            ],
            ['what it contains', 'what it contains'],
            ['life exists in outer space', 'life does not exist in outer space'],
            ['alien exists', 'alien does not exist'],
            ['it coexists', 'it coexists'],
            ['the dog has a bone', 'the dog does not have a bone'],
            ['whatever it has', 'whatever it has'],
            ['apple is red', 'apple is not red'],
            ['yes, it is', 'yes, it is'],
            ['this is clock', 'this is not clock'],
            ['how are you?', 'how are not you?'],
            ['how dare you!', 'how dare you!'],
            ['what they are', 'what they are'],
            ['that matches my preferences', 'that does not match my preferences'],
            ['dinner starts with desert', 'dinner starts not with desert'],
            ['it starts with', 'it starts with'],
            ['dinner ends with desert', 'dinner ends not with desert'],
            ['it ends with', 'it ends with'],
            ['you reference me', 'you don\'t reference me'],
            ['it\'s not not false', 'it\'s not false'],
        ];
    }

    /**
     * @dataProvider providerNegate
     */
    public function testNegate(string $input, string $expected): void
    {
        $this->assertSame($expected, LogicalNot::negate($input));
    }

    public function testLogicalNotOfLogicalOrToString(): void
    {
        $names = [
            'is healthy',
            'is rich in amino acids',
            'is rich in unsaturated fats',
        ];

        $constraints = \array_map(static function (string $name): \NamedConstraint {
            return \NamedConstraint::fromName($name);
        }, $names);

        $constraint = new LogicalNot(LogicalOr::fromConstraints(...$constraints));

        $expected = 'not( ' . \implode(' or ', $names) . ' )';

        $this->assertSame($expected, $constraint->toString());
    }

    public function testLogicalNotOfLogicalOrToString2(): void
    {
        $constraint = new LogicalNot(
            LogicalOr::fromConstraints(
                \NamedConstraint::fromName('is healthy')
            )
        );

        $expected = 'is not healthy';

        $this->assertSame($expected, $constraint->toString());
    }

    public function testLogicalNotOfLogicalAndToString(): void
    {
        $names = [
            'is healthy',
            'is rich in amino acids',
            'is rich in unsaturated fats',
        ];

        $constraints = \array_map(static function (string $name): \NamedConstraint {
            return \NamedConstraint::fromName($name);
        }, $names);

        $constraint = new LogicalNot(LogicalAnd::fromConstraints(...$constraints));

        $expected = 'not( ' . \implode(' and ', $names) . ' )';

        $this->assertSame($expected, $constraint->toString());
    }

    public function testLogicalNotOfLogicalAndToString2(): void
    {
        $constraint = new LogicalNot(
            LogicalAnd::fromConstraints(
                \NamedConstraint::fromName('is healthy')
            )
        );

        $expected = 'is not healthy';

        $this->assertSame($expected, $constraint->toString());
    }

    public function testLogicalNotOfLogicalXorToString(): void
    {
        $names = [
            'is healthy',
            'is rich in amino acids',
            'is rich in unsaturated fats',
        ];

        $constraints = \array_map(static function (string $name): \NamedConstraint {
            return \NamedConstraint::fromName($name);
        }, $names);

        $constraint = new LogicalNot(LogicalXor::fromConstraints(...$constraints));

        $expected = 'not( ' . \implode(' xor ', $names) . ' )';

        $this->assertSame($expected, $constraint->toString());
    }

    public function testLogicalNotOfLogicalXorToString2(): void
    {
        $constraint = new LogicalNot(
            LogicalXor::fromConstraints(
                \NamedConstraint::fromName('is healthy')
            )
        );

        $expected = 'is not healthy';

        $this->assertSame($expected, $constraint->toString());
    }

    public function testLogicalNotOfLogicalAndFailureDescription(): void
    {
        $names = [
            'is healthy',
            'is rich in amino acids',
            'is rich in unsaturated fats',
        ];

        $constraints = \array_map(function (string $name) {
            return \NamedConstraint::fromName($name);
        }, $names);

        $constraint = new LogicalNot(LogicalAnd::fromConstraints(...$constraints));

        $expected = 'not( \'apple\' ' . \implode(' and ', $names) . ' )';

        $method = new \ReflectionMethod(LogicalNot::class, 'failureDescription');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invokeArgs($constraint, ['apple']));
    }

    public function testLogicalNotOfLogicalAndFailureDescription2(): void
    {
        $constraint = new LogicalNot(
            LogicalAnd::fromConstraints(
                \NamedConstraint::fromName('is healthy')
            )
        );

        $expected = '\'apple\' is not healthy';

        $method = new \ReflectionMethod(LogicalNot::class, 'failureDescription');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invokeArgs($constraint, ['apple']));
    }

    public function testLogicalNotOfLogicalOrFailureDescription(): void
    {
        $names = [
            'is healthy',
            'is rich in amino acids',
            'is rich in unsaturated fats',
        ];

        $constraints = \array_map(function (string $name) {
            return \NamedConstraint::fromName($name);
        }, $names);

        $constraint = new LogicalNot(LogicalOr::fromConstraints(...$constraints));

        $expected = 'not( \'apple\' ' . \implode(' or ', $names) . ' )';

        $method = new \ReflectionMethod(LogicalNot::class, 'failureDescription');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invokeArgs($constraint, ['apple']));
    }

    public function testLogicalNotOfLogicalOrFailureDescription2(): void
    {
        $constraint = new LogicalNot(
            LogicalOr::fromConstraints(
                \NamedConstraint::fromName('is healthy')
            )
        );

        $expected = '\'apple\' is not healthy';

        $method = new \ReflectionMethod(LogicalNot::class, 'failureDescription');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invokeArgs($constraint, ['apple']));
    }

    public function testLogicalNotOfLogicalXorFailureDescription(): void
    {
        $names = [
            'is healthy',
            'is rich in amino acids',
            'is rich in unsaturated fats',
        ];

        $constraints = \array_map(function (string $name) {
            return \NamedConstraint::fromName($name);
        }, $names);

        $constraint = new LogicalNot(LogicalXor::fromConstraints(...$constraints));

        $expected = 'not( \'apple\' ' . \implode(' xor ', $names) . ' )';

        $method = new \ReflectionMethod(LogicalNot::class, 'failureDescription');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invokeArgs($constraint, ['apple']));
    }

    public function testLogicalNotOfLogicalXorFailureDescription2(): void
    {
        $constraint = new LogicalNot(
            LogicalXor::fromConstraints(
                \NamedConstraint::fromName('is healthy')
            )
        );

        $expected = '\'apple\' is not healthy';

        $method = new \ReflectionMethod(LogicalNot::class, 'failureDescription');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invokeArgs($constraint, ['apple']));
    }

    public function testNestedLogicalNotOfIsEqualToString(): void
    {
        $constraint = new LogicalNot(
            new LogicalNot(
                new IsEqual(5)
            )
        );

        $expected = 'not( is not equal to 5 )';

        $this->assertSame($expected, $constraint->toString());
    }

    public function testNestedLogicalNotOfIsEqualFailureDescription(): void
    {
        $constraint = new LogicalNot(
            new LogicalNot(
                new IsEqual(5)
            )
        );

        $method = new \ReflectionMethod(LogicalNot::class, 'failureDescription');
        $method->setAccessible(true);

        $expected = 'not( 3 is not equal to 5 )';

        $this->assertSame($expected, $method->invokeArgs($constraint, [3]));
    }

    public function testNestedLogicalNotOfLogicalOrWithSingleOperandToString(): void
    {
        $constraint = new LogicalNot(
            new LogicalNot(
                LogicalOr::fromConstraints(
                    new IsEqual(5)
                )
            )
        );

        $expected = 'not( is not equal to 5 )';

        $this->assertSame($expected, $constraint->toString());
    }

    public function testNestedLogicalNotOfLogicalOrWithSingleOperandFailureDescription(): void
    {
        $constraint = new LogicalNot(
            new LogicalNot(
                LogicalOr::fromConstraints(
                    new IsEqual(5)
                )
            )
        );

        $method = new \ReflectionMethod(LogicalNot::class, 'failureDescription');
        $method->setAccessible(true);

        $expected = 'not( 3 is not equal to 5 )';

        $this->assertSame($expected, $method->invokeArgs($constraint, [3]));
    }

    public function testNestedLogicalNotOfNestedLogicalOrWithSingleOperandToString(): void
    {
        $constraint = new LogicalNot(
            new LogicalNot(
                LogicalOr::fromConstraints(
                    LogicalOr::fromConstraints(
                        new IsEqual(5)
                    )
                )
            )
        );

        $expected = 'not( is not equal to 5 )';

        $this->assertSame($expected, $constraint->toString());
    }

    public function testNestedLogicalNotOfNestedLogicalOrWithSingleOperandFailureDescription(): void
    {
        $constraint = new LogicalNot(
            new LogicalNot(
                LogicalOr::fromConstraints(
                    LogicalOr::fromConstraints(
                        new IsEqual(5)
                    )
                )
            )
        );

        $method = new \ReflectionMethod(LogicalNot::class, 'failureDescription');
        $method->setAccessible(true);

        $expected = 'not( 3 is not equal to 5 )';

        $this->assertSame($expected, $method->invokeArgs($constraint, [3]));
    }

    public function testNestedLogicalNotOfLogicalOrWithMultipleOperandsToString(): void
    {
        $constraint = new LogicalNot(
            new LogicalNot(
                LogicalOr::fromConstraints(
                    new LessThan(5),
                    new IsEqual(10),
                    new GreaterThan(15)
                )
            )
        );

        $expected = 'not( not( is less than 5 or is equal to 10 or is greater than 15 ) )';

        $this->assertSame($expected, $constraint->toString());
    }

    public function testNestedLogicalNotOfLogicalOrWithMultipleOperandsFailureDescription(): void
    {
        $constraint = new LogicalNot(
            new LogicalNot(
                LogicalOr::fromConstraints(
                    new LessThan(5),
                    new IsEqual(10),
                    new GreaterThan(15)
                )
            )
        );

        $method = new \ReflectionMethod(LogicalNot::class, 'failureDescription');
        $method->setAccessible(true);

        $expected = 'not( not( 7 is less than 5 or is equal to 10 or is greater than 15 ) )';

        $this->assertSame($expected, $method->invokeArgs($constraint, [7]));
    }
}
