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
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\ConstraintThatDescribesItselfInContext;
use PHPUnit\TestFixture\PassThroughUnaryOperator;

#[CoversClass(UnaryOperator::class)]
#[Small]
#[Group('framework')]
#[Group('framework/constraints')]
final class UnaryOperatorTest extends TestCase
{
    #[TestDox('Uses the failure description of an operand that does not describe itself in the context of the operator')]
    public function testUsesFailureDescriptionOfOperandThatDoesNotDescribeItselfInContextOfOperator(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs('Failed asserting that false is true.');

        new PassThroughUnaryOperator(new IsTrue)->evaluate(false);
    }

    #[TestDox('Uses the failure description an operand provides for the context of the operator')]
    public function testUsesFailureDescriptionOperandProvidesForContextOfOperator(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs('Failed asserting that false is described in the context of pass-through.');

        new PassThroughUnaryOperator(new ConstraintThatDescribesItselfInContext)->evaluate(false);
    }
}
