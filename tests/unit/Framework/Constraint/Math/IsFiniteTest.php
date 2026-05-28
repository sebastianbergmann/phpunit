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

use function acos;
use function log;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

#[CoversClass(IsFinite::class)]
#[CoversClass(Constraint::class)]
#[Small]
#[Group('framework')]
#[Group('framework/constraints')]
final class IsFiniteTest extends TestCase
{
    public function testCanBeEvaluated(): void
    {
        $constraint = new IsFinite;

        $this->assertTrue($constraint->evaluate(1, returnResult: true));
        $this->assertFalse($constraint->evaluate(log(0), returnResult: true));
        $this->assertFalse($constraint->evaluate(acos(2), returnResult: true));
    }

    public function testCanBeRepresentedAsString(): void
    {
        $this->assertSame('is finite', (new IsFinite)->toString());
    }

    public function testCanBeNegated(): void
    {
        $constraint = new LogicalNot(new IsFinite);

        $this->assertSame('is not finite', $constraint->toString());

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs('Failed asserting that 1.0 is not finite.');

        $constraint->evaluate(1.0);
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new IsFinite));
    }

    public function testFailureDescriptionForScalar(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs('Failed asserting that -INF is finite.');

        (new IsFinite)->evaluate(log(0));
    }
}
