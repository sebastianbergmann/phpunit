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
use stdClass;

#[CoversClass(IsNull::class)]
#[CoversClass(Constraint::class)]
#[Small]
#[Group('framework')]
#[Group('framework/constraints')]
final class IsNullTest extends TestCase
{
    public function testCanBeEvaluated(): void
    {
        $constraint = new IsNull;

        $this->assertTrue($constraint->evaluate(null, returnResult: true));
        $this->assertFalse($constraint->evaluate(false, returnResult: true));
    }

    public function testCanBeRepresentedAsString(): void
    {
        $this->assertSame('is null', (new IsNull)->toString());
    }

    public function testCanBeNegated(): void
    {
        $constraint = new LogicalNot(new IsNull);

        $this->assertSame('is not null', $constraint->toString());

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs('Failed asserting that null is not null.');

        $constraint->evaluate(null);
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, new IsNull);
    }

    public function testFailureDescriptionForScalar(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs('Failed asserting that false is null.');

        (new IsNull)->evaluate(false);
    }

    public function testFailureDescriptionForArray(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs('Failed asserting that an array is null.');

        (new IsNull)->evaluate([1, 2, 3]);
    }

    public function testFailureDescriptionForObject(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs('Failed asserting that an instance of class stdClass is null.');

        (new IsNull)->evaluate(new stdClass);
    }
}
