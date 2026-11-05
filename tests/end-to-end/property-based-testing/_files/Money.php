<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting;

use function intdiv;

final readonly class Money
{
    /**
     * @var non-negative-int
     */
    public int $amount;
    public Currency $currency;

    /**
     * @param non-negative-int $amount
     */
    public function __construct(int $amount, Currency $currency)
    {
        $this->amount   = $amount;
        $this->currency = $currency;
    }

    /**
     * Has a bug: the remainder of the division is lost.
     *
     * @param positive-int $parts
     *
     * @return list<self>
     */
    public function allocate(int $parts): array
    {
        $shares = [];

        for ($i = 0; $i < $parts; $i++) {
            $shares[] = new self(intdiv($this->amount, $parts), $this->currency);
        }

        return $shares;
    }
}
