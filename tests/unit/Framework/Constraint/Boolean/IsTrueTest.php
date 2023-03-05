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

#[CoversClass(IsTrue::class)]
#[CoversClass(Constraint::class)]
#[Small]
final class IsTrueTest extends TestCase
{
    public function testCanBeEvaluated(): void
    {
        $this->assertTrue((new IsTrue)->evaluate(true, returnResult: true));
        $this->assertFalse((new IsTrue)->evaluate(false, returnResult: true));
    }

    public function testCanBeRepresentedAsString(): void
    {
        $this->assertSame('is true', (new IsTrue)->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new IsTrue));
    }
}
