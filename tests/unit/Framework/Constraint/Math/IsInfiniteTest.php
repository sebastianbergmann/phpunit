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

use const INF;
use function acos;
use function log;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

#[CoversClass(IsInfinite::class)]
#[CoversClass(Constraint::class)]
#[Small]
#[Group('framework')]
#[Group('framework/constraints')]
final class IsInfiniteTest extends TestCase
{
    public function testCanBeEvaluated(): void
    {
        $constraint = new IsInfinite;

        $this->assertTrue($constraint->evaluate(log(0), returnResult: true));
        $this->assertFalse($constraint->evaluate(1, returnResult: true));
        $this->assertFalse($constraint->evaluate(acos(2), returnResult: true));
    }

    public function testCanBeRepresentedAsString(): void
    {
        $this->assertSame('is infinite', (new IsInfinite)->toString());
    }

    public function testCanBeNegated(): void
    {
        $constraint = new LogicalNot(new IsInfinite);

        $this->assertSame('is not infinite', $constraint->toString());

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs('Failed asserting that INF is not infinite.');

        $constraint->evaluate(INF);
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new IsInfinite));
    }

    public function testFailureDescriptionForScalar(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs('Failed asserting that 1 is infinite.');

        (new IsInfinite)->evaluate(1);
    }

    public function testMatchesReturnsFalseForNonNumeric(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs("Failed asserting that 'foo' is infinite.");

        (new IsInfinite)->evaluate('foo');
    }

    public function testReturnsAffirmativeStringInNonLogicalNotContext(): void
    {
        $this->assertSame(
            'is infinite',
            LogicalAnd::fromConstraints(new IsInfinite)->toString(),
        );
    }
}
