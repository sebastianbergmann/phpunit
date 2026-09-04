<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\ExecutionOrder;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Order::class)]
#[Small]
final class OrderTest extends TestCase
{
    /**
     * @return non-empty-list<array{non-empty-string, Order, bool, bool}>
     */
    public static function provider(): array
    {
        return [
            ['defects', Order::Defects, false, true],
            ['duration-ascending', Order::DurationAscending, true, true],
            ['duration-descending', Order::DurationDescending, true, true],
            ['modified-ascending', Order::ModifiedAscending, true, false],
            ['modified-descending', Order::ModifiedDescending, true, false],
            ['random', Order::Random, true, false],
            ['reverse', Order::Reverse, true, false],
            ['size-ascending', Order::SizeAscending, true, false],
            ['size-descending', Order::SizeDescending, true, false],
        ];
    }

    /**
     * @param non-empty-string $token
     */
    #[DataProvider('provider')]
    public function testKnowsItsConfigurationToken(string $token, Order $order, bool $isSortingStrategy, bool $requiresTestRunHistory): void
    {
        $this->assertSame($token, $order->token());
        $this->assertSame($order, Order::fromToken($token));
    }

    /**
     * @param non-empty-string $token
     */
    #[DataProvider('provider')]
    public function testKnowsWhetherItIsASortingStrategy(string $token, Order $order, bool $isSortingStrategy, bool $requiresTestRunHistory): void
    {
        $this->assertSame($isSortingStrategy, $order->isSortingStrategy());
    }

    /**
     * @param non-empty-string $token
     */
    #[DataProvider('provider')]
    public function testKnowsWhetherItNeedsTheTestRunHistory(string $token, Order $order, bool $isSortingStrategy, bool $requiresTestRunHistory): void
    {
        $this->assertSame($requiresTestRunHistory, $order->requiresTestRunHistory());
    }

    #[TestDox('Has no order for a token that does not exist')]
    public function testHasNoOrderForATokenThatDoesNotExist(): void
    {
        $this->assertNull(Order::fromToken('does-not-exist'));
    }
}
