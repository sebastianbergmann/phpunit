<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBased\StateMachine;

use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\FromPool;
use PHPUnit\Framework\Attributes\Initialize;
use PHPUnit\Framework\Attributes\Invariant;
use PHPUnit\Framework\Attributes\Rule;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;

final class ValidStateMachine
{
    /**
     * @var array<string, int>
     */
    private array $balances = [];

    /**
     * @return ValueGenerator<int>
     */
    public static function amounts(): ValueGenerator
    {
        return Generators::integers(1, 10);
    }

    /**
     * @return ValueGenerator<string>
     */
    public static function names(): ValueGenerator
    {
        return Generators::elements(['a', 'b']);
    }

    #[Initialize(produces: 'accounts')]
    public function openInitialAccount(): string
    {
        $this->balances['initial'] = 0;

        return 'initial';
    }

    #[Initialize]
    public function seed(int $value): void
    {
    }

    #[Rule(produces: 'accounts')]
    public function open(#[ForAll('names')] string $name): string
    {
        $this->balances[$name] = 0;

        return $name;
    }

    #[Rule]
    public function deposit(#[FromPool('accounts')] string $account, #[ForAll('amounts')] int $amount): void
    {
        $this->balances[$account] += $amount;
    }

    #[Rule(precondition: 'hasAccounts')]
    public function close(#[FromPool('accounts', consume: true)] string $account): void
    {
        unset($this->balances[$account]);
    }

    public function hasAccounts(): bool
    {
        return $this->balances !== [];
    }

    #[Invariant]
    public function balancesAreNotNegative(): void
    {
    }

    #[Invariant(atEnd: true)]
    public function balancesAddUp(): void
    {
    }

    public function isNotAStep(): void
    {
    }
}
