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

class LogicalNotTest extends UnaryTestCase
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
            ['what it contains', 'what it contains'],
            ['life exists in outer space', 'life does not exist in outer space'],
            ['alien exists', 'alien does not exist'],
            ['the dog has a bone', 'the dog does not have a bone'],
            ['whatever it has', 'whatever it has'],
            ['apple is red', 'apple is not red'],
            ['yes, it is', 'yes, it is'],
            ['how are you?', 'how are not you?'],
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

        $constraints = \array_map(function (string $name) {
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

        $constraints = \array_map(function (string $name) {
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

        $constraints = \array_map(function (string $name) {
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

        $reflectionClass  = new \ReflectionClass(LogicalNot::class);
        $reflectionMethod = $reflectionClass->getMethod('failureDescription');
        $reflectionMethod->setAccessible(true);

        $this->assertSame($expected, $reflectionMethod->invokeArgs($constraint, ['apple']));
    }

    public function testLogicalNotOfLogicalAndFailureDescription2(): void
    {
        $constraint = new LogicalNot(
            LogicalAnd::fromConstraints(
                \NamedConstraint::fromName('is healthy')
            )
        );

        $expected = '\'apple\' is not healthy';

        $reflectionClass  = new \ReflectionClass(LogicalNot::class);
        $reflectionMethod = $reflectionClass->getMethod('failureDescription');
        $reflectionMethod->setAccessible(true);

        $this->assertSame($expected, $reflectionMethod->invokeArgs($constraint, ['apple']));
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

        $reflectionClass  = new \ReflectionClass(LogicalNot::class);
        $reflectionMethod = $reflectionClass->getMethod('failureDescription');
        $reflectionMethod->setAccessible(true);

        $this->assertSame($expected, $reflectionMethod->invokeArgs($constraint, ['apple']));
    }

    public function testLogicalNotOfLogicalOrFailureDescription2(): void
    {
        $constraint = new LogicalNot(
            LogicalOr::fromConstraints(
                \NamedConstraint::fromName('is healthy')
            )
        );

        $expected = '\'apple\' is not healthy';

        $reflectionClass  = new \ReflectionClass(LogicalNot::class);
        $reflectionMethod = $reflectionClass->getMethod('failureDescription');
        $reflectionMethod->setAccessible(true);

        $this->assertSame($expected, $reflectionMethod->invokeArgs($constraint, ['apple']));
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

        $reflectionClass  = new \ReflectionClass(LogicalNot::class);
        $reflectionMethod = $reflectionClass->getMethod('failureDescription');
        $reflectionMethod->setAccessible(true);

        $this->assertSame($expected, $reflectionMethod->invokeArgs($constraint, ['apple']));
    }

    public function testLogicalNotOfLogicalXorFailureDescription2(): void
    {
        $constraint = new LogicalNot(
            LogicalXor::fromConstraints(
                \NamedConstraint::fromName('is healthy')
            )
        );

        $expected = '\'apple\' is not healthy';

        $reflectionClass  = new \ReflectionClass(LogicalNot::class);
        $reflectionMethod = $reflectionClass->getMethod('failureDescription');
        $reflectionMethod->setAccessible(true);

        $this->assertSame($expected, $reflectionMethod->invokeArgs($constraint, ['apple']));
    }
}
