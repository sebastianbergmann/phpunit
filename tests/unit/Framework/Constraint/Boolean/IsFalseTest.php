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

#[CoversClass(IsFalse::class)]
#[CoversClass(Constraint::class)]
#[Small]
#[Group('framework')]
#[Group('framework/constraints')]
final class IsFalseTest extends TestCase
{
    public function testCanBeEvaluated(): void
    {
        $this->assertTrue((new IsFalse)->evaluate(false, returnResult: true));
        $this->assertFalse((new IsFalse)->evaluate(true, returnResult: true));
    }

    public function testCanBeRepresentedAsString(): void
    {
        $this->assertSame('is false', (new IsFalse)->toString());
    }

    public function testCanBeNegated(): void
    {
        $constraint = new LogicalNot(new IsFalse);

        $this->assertSame('is not false', $constraint->toString());

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs('Failed asserting that false is not false.');

        $constraint->evaluate(false);
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new IsFalse));
    }

    public function testFailureDescriptionForScalar(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs('Failed asserting that true is false.');

        (new IsFalse)->evaluate(true);
    }

    public function testFailureDescriptionForArray(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs('Failed asserting that an array is false.');

        (new IsFalse)->evaluate([1, 2, 3]);
    }

    public function testFailureDescriptionForObject(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs('Failed asserting that an instance of class stdClass is false.');

        (new IsFalse)->evaluate(new stdClass);
    }
}
