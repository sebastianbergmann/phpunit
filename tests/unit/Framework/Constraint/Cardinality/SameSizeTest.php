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

#[CoversClass(SameSize::class)]
#[CoversClass(Count::class)]
#[CoversClass(Constraint::class)]
#[Small]
final class SameSizeTest extends TestCase
{
    public function testCanBeRepresentedAsString(): void
    {
        $this->assertSame('count matches 0', (new SameSize([]))->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new SameSize([])));
    }
}
