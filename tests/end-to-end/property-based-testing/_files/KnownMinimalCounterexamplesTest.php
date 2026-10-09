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

use function array_map;
use function array_sum;
use function array_unique;
use function count;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Currency.php';

require_once __DIR__ . '/Money.php';

final class KnownMinimalCounterexamplesTest extends TestCase
{
    /**
     * @param int<1, 10> $parts
     */
    #[Property]
    public function testAllocationPreservesTheAmount(Money $money, int $parts): void
    {
        $this->assertSame($money->amount, array_sum(array_map(
            static fn (Money $share): int => $share->amount,
            $money->allocate($parts),
        )));
    }

    /**
     * @param list<int> $values
     */
    #[Property]
    public function testListHasNoDuplicates(array $values): void
    {
        $this->assertCount(count($values), array_unique($values));
    }

    /**
     * @param int<0, 1000> $a
     * @param int<0, 1000> $b
     */
    #[Property]
    public function testSumIsAtMostTen(int $a, int $b): void
    {
        $this->assertLessThanOrEqual(10, $a + $b);
    }
}
