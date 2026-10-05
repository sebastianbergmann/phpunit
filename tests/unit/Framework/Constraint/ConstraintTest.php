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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

#[CoversClass(Constraint::class)]
#[CoversClass(UnaryOperator::class)]
#[CoversClass(LogicalNot::class)]
#[Small]
#[Group('framework')]
#[Group('framework/constraints')]
final class ConstraintTest extends TestCase
{
    public function testInvokeReturnsActualValueWhenConstraintIsMet(): void
    {
        $constraint = new IsIdentical(42);

        $this->assertSame(42, $constraint(42));
    }

    public function testInvokeThrowsWhenConstraintIsNotMet(): void
    {
        $constraint = new IsIdentical(42);

        $this->expectException(ExpectationFailedException::class);

        $constraint(0);
    }

    public function testDefaultMatchesReturnsFalse(): void
    {
        $constraint = new class extends Constraint
        {
            public function toString(): string
            {
                return 'is anything via default matches';
            }
        };

        $this->assertFalse($constraint->evaluate('anything', returnResult: true));
    }

    public function testFailureMessageIsPrefixedWithUserDescription(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs("custom user description\nFailed asserting that false is true.");

        (new IsTrue)->evaluate(false, 'custom user description');
    }

    public function testToStringInContextIsIgnoredByLogicalNot(): void
    {
        $constraint = new class extends Constraint
        {
            public function toString(): string
            {
                return 'is fresh';
            }

            public function toStringInContext(Operator $operator, mixed $role): string
            {
                if (!$operator instanceof LogicalNot) {
                    return '';
                }

                return 'is stale';
            }
        };

        $this->assertSame('not (is fresh)', new LogicalNot($constraint)->toString());
    }

    public function testNegatedToStringIsUsedWhenConstraintAuthorsItsNegation(): void
    {
        $constraint = new class extends Constraint
        {
            public function toString(): string
            {
                return 'is fresh';
            }

            public function negatedToString(): string
            {
                return 'is stale';
            }

            protected function matches(mixed $other): bool
            {
                return true;
            }
        };

        $this->assertSame('is stale', new LogicalNot($constraint)->toString());

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs("Failed asserting that 'value' is stale.");

        new LogicalNot($constraint)->evaluate('value');
    }

    public function testNegatedFailureDescriptionIsUsedWhenConstraintAuthorsIt(): void
    {
        $constraint = new class extends Constraint
        {
            public function toString(): string
            {
                return 'is fresh';
            }

            public function negatedFailureDescription(mixed $other): string
            {
                return 'the cache entry is stale';
            }

            protected function matches(mixed $other): bool
            {
                return true;
            }
        };

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs('Failed asserting that the cache entry is stale.');

        new LogicalNot($constraint)->evaluate('value');
    }

    public function testAffirmativeDescriptionIsWrappedWhenConstraintDoesNotAuthorItsNegation(): void
    {
        $constraint = new class extends Constraint
        {
            public function toString(): string
            {
                return 'is fresh';
            }

            protected function matches(mixed $other): bool
            {
                return true;
            }
        };

        $this->assertSame('not (is fresh)', new LogicalNot($constraint)->toString());

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs("Failed asserting that 'value' not (is fresh).");

        new LogicalNot($constraint)->evaluate('value');
    }

    public function testNegatedToStringIsNotUsedByBinaryOperators(): void
    {
        $constraint = new class extends Constraint
        {
            public function toString(): string
            {
                return 'is fresh';
            }

            public function negatedToString(): string
            {
                return 'is stale';
            }

            protected function matches(mixed $other): bool
            {
                return true;
            }
        };

        $this->assertSame(
            'is fresh and is fresh',
            LogicalAnd::fromConstraints($constraint, $constraint)->toString(),
        );
    }
}
