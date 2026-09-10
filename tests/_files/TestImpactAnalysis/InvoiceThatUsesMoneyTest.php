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
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Invoice::class)]
#[UsesClass(Money::class)]
final class InvoiceThatUsesMoneyTest extends TestCase
{
    public function testTotalIsTheSumOfItsItems(): void
    {
        $invoice = new Invoice;
        $invoice->add(new Money(1));

        $this->assertSame(1, $invoice->total()->amount());
    }
}
