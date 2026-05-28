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

#[CoversClass(SameSize::class)]
#[CoversClass(Count::class)]
#[CoversClass(Constraint::class)]
#[Small]
#[Group('framework')]
#[Group('framework/constraints')]
final class SameSizeTest extends TestCase
{
    public function testCanBeRepresentedAsString(): void
    {
        $this->assertSame('count matches 0', new SameSize([])->toString());
    }

    public function testCanBeNegated(): void
    {
        $constraint = new LogicalNot(new SameSize([1]));

        $this->assertSame('count does not match 1', $constraint->toString());

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs('Failed asserting that actual size 1 does not match expected size 1.');

        $constraint->evaluate([2]);
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new SameSize([])));
    }
}
