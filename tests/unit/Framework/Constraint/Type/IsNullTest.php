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
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(IsNull::class)]
#[CoversClass(Constraint::class)]
#[Small]
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

    public function testIsCountable(): void
    {
        $this->assertCount(1, new IsNull);
    }
}
