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

use const NAN;
use function acos;
use function log;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

#[CoversClass(IsNan::class)]
#[CoversClass(Constraint::class)]
#[Small]
#[Group('framework')]
#[Group('framework/constraints')]
final class IsNanTest extends TestCase
{
    public function testCanBeEvaluated(): void
    {
        $constraint = new IsNan;

        $this->assertTrue($constraint->evaluate(acos(2), returnResult: true));
        $this->assertFalse($constraint->evaluate(log(0), returnResult: true));
        $this->assertFalse($constraint->evaluate(1, returnResult: true));
    }

    public function testCanBeRepresentedAsString(): void
    {
        $this->assertSame('is nan', (new IsNan)->toString());
    }

    public function testCanBeNegated(): void
    {
        $constraint = new LogicalNot(new IsNan);

        $this->assertSame('is not nan', $constraint->toString());

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs('Failed asserting that NAN is not nan.');

        $constraint->evaluate(NAN);
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new IsNan));
    }

    public function testFailureDescriptionForScalar(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs('Failed asserting that 1 is nan.');

        (new IsNan)->evaluate(1);
    }
}
