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

    public function testFailureDescriptionInContextIsUsedWhenConstraintProvidesContextString(): void
    {
        $constraint = new class extends Constraint
        {
            public function toString(): string
            {
                return 'fallback string';
            }

            public function toStringInContext(Operator $operator, mixed $role): string
            {
                return 'context-aware string';
            }

            protected function matches(mixed $other): bool
            {
                return true;
            }
        };

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs("Failed asserting that 'value' context-aware string.");

        new LogicalNot($constraint)->evaluate('value');
    }

    public function testToStringInContextIsUsedWhenConstraintProvidesContextString(): void
    {
        $constraint = new class extends Constraint
        {
            public function toString(): string
            {
                return 'fallback string';
            }

            public function toStringInContext(Operator $operator, mixed $role): string
            {
                return 'context-aware string';
            }
        };

        $this->assertSame('context-aware string', new LogicalNot($constraint)->toString());
    }
}
