<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestImpactAnalysis;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

#[CoversClass(Money::class)]
final class DependencyChainSelectionTest extends TestCase
{
    public function testProducesMoney(): Money
    {
        $money = new Money(1);

        $this->assertSame(1, $money->amount());

        return $money;
    }

    #[Depends('testProducesMoney')]
    public function testDoublesMoney(Money $money): Money
    {
        $doubled = $money->add($money);

        $this->assertSame(2, $doubled->amount());

        return $doubled;
    }

    #[Depends('testDoublesMoney')]
    public function testFormatsDoubledMoney(Money $money): void
    {
        $this->assertSame('2', (new Formatter)->format($money));
    }

    #[Depends('testProducesMoney')]
    public function testConsumesMoney(Money $money): void
    {
        $this->assertSame(1, $money->amount());
    }
}
