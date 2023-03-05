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
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(IsNan::class)]
#[CoversClass(Constraint::class)]
#[Small]
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

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new IsNan));
    }
}
