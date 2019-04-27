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

use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestFailure;

/**
 * @small
 */
final class LogicalAndTest extends ConstraintTestCase
{
    public function testSetConstraintsRejectsInvalidConstraint(): void
    {
        $constraints = [
            new \TruthyConstraint,
            new \FalsyConstraint,
            new \stdClass,
        ];

        $constraint = new LogicalAnd;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(\sprintf(
            'All parameters to %s must be a constraint object.',
            LogicalAnd::class
        ));

        $constraint->setConstraints($constraints);
    }

    public function testCountReturnsCountOfComposedConstraints(): void
    {
        $counts = [
            3,
            5,
            8,
        ];

        $constraints = \array_map(function (int $count) {
            return \CountConstraint::fromCount($count);
        }, $counts);

        $constraint = new LogicalAnd;

        $constraint->setConstraints($constraints);

        $expected = \array_sum($counts);

        $this->assertSame($expected, $constraint->count());
    }

    public function testToStringReturnsImplodedStringRepresentationOfComposedConstraintsGluedWithAnd(): void
    {
        $names = [
            'is healthy',
            'is rich in amino acids',
            'is rich in unsaturated fats',
        ];

        $constraints = \array_map(function (string $name) {
            return \NamedConstraint::fromName($name);
        }, $names);

        $constraint = LogicalAnd::fromConstraints(...$constraints);

        $expected = \implode(' and ', $names);

        $this->assertSame($expected, $constraint->toString());
    }

    /**
     * @dataProvider providerFailingConstraints
     *
     * @param Constraint[] $constraints
     */
    public function testEvaluateReturnsFalseIfAnyOfTheComposedConstraintsEvaluateToFalse(array $constraints): void
    {
        $constraint = new LogicalAnd;

        $constraint->setConstraints($constraints);

        $this->assertFalse($constraint->evaluate('whatever', '', true));
    }

    /**
     * @dataProvider providerSucceedingConstraints
     *
     * @param Constraint[] $constraints
     */
    public function testEvaluateReturnsTrueIfAllOfTheComposedConstraintsEvaluateToTrue(array $constraints): void
    {
        $constraint = new LogicalAnd;

        $constraint->setConstraints($constraints);

        $this->assertTrue($constraint->evaluate('whatever', '', true));
    }

    /**
     * @dataProvider providerFailingConstraints
     *
     * @param Constraint[] $constraints
     */
    public function testEvaluateThrowsExceptionIfAnyOfTheComposedConstraintsEvaluateToFalse(array $constraints): void
    {
        $other = 'whatever';

        $constraint = new LogicalAnd;

        $constraint->setConstraints($constraints);

        try {
            $constraint->evaluate($other);
        } catch (ExpectationFailedException $exception) {
            $toString = $this->stringify($constraints);

            $expectedDescription = <<<EOF
Failed asserting that '$other' $toString.

EOF;

            $this->assertEquals($expectedDescription, TestFailure::exceptionToString($exception));

            return;
        }

        $this->fail();
    }

    /**
     * @dataProvider providerFailingConstraints
     *
     * @param Constraint[] $constraints
     */
    public function testEvaluateThrowsExceptionWithCustomMessageIfAnyOfTheComposedConstraintsEvaluateToFalse(array $constraints): void
    {
        $other             = 'whatever';
        $customDescription = 'Not very happy about the results at this point in time, I have to admit!';

        $constraint = new LogicalAnd;

        $constraint->setConstraints($constraints);

        try {
            $constraint->evaluate(
                $other,
                $customDescription
            );
        } catch (ExpectationFailedException $exception) {
            $toString = $this->stringify($constraints);

            $expectedDescription = <<<EOF
$customDescription
Failed asserting that '$other' $toString.

EOF;

            $this->assertEquals($expectedDescription, TestFailure::exceptionToString($exception));

            return;
        }

        $this->fail();
    }

    /**
     * @dataProvider providerSucceedingConstraints
     *
     * @param Constraint[] $constraints
     */
    public function testEvaluateReturnsNothingIfAllOfTheComposedConstraintsEvaluateToTrue(array $constraints): void
    {
        $constraint = new LogicalAnd;

        $constraint->setConstraints($constraints);

        $this->assertNull($constraint->evaluate('whatever'));
    }

    public function providerFailingConstraints(): \Generator
    {
        $values = [
            'single' => [
                new \FalsyConstraint,
            ],
            'multiple' => [
                new \TruthyConstraint,
                new \FalsyConstraint,
                new \TruthyConstraint,
            ],
        ];

        foreach ($values as $key => $constraints) {
            yield $key => [
                $constraints,
            ];
        }
    }

    public function providerSucceedingConstraints(): \Generator
    {
        $values = [
            'single' => [
                new \TruthyConstraint,
            ],
            'multiple' => [
                new \TruthyConstraint,
                new \TruthyConstraint,
                new \TruthyConstraint,
            ],
        ];

        foreach ($values as $key => $constraints) {
            yield $key => [
                $constraints,
            ];
        }
    }

    private function stringify(array $constraints): string
    {
        return \implode(
            ' and ',
            \array_map(function (Constraint $constraint) {
                return $constraint->toString();
            }, $constraints)
        );
    }
}
