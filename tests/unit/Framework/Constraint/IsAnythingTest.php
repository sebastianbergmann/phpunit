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

#[CoversClass(IsAnything::class)]
#[CoversClass(Constraint::class)]
#[Small]
final class IsAnythingTest extends TestCase
{
    public function testCanBeEvaluated(): void
    {
        $this->assertTrue((new IsAnything)->evaluate(true, returnResult: true));
    }

    public function testCanBeRepresentedAsString(): void
    {
        $this->assertSame('is anything', (new IsAnything)->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(0, (new IsAnything));
    }
}
