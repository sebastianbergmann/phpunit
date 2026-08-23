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

final class Invoice
{
    /**
     * @var list<Money>
     */
    private array $items = [];

    public function add(Money $item): void
    {
        $this->items[] = $item;
    }

    public function total(): Money
    {
        $total = new Money(0);

        foreach ($this->items as $item) {
            $total = $total->add($item);
        }

        return $total;
    }
}
