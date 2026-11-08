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
use PHPUnit\Framework\Attributes\InitializeStateMachine;
use PHPUnit\Framework\Attributes\StateMachineInvariant;
use PHPUnit\Framework\Attributes\StateMachineRule;
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

    #[InitializeStateMachine(produces: 'accounts')]
    public function openInitialAccount(): string
    {
        $this->balances['initial'] = 0;

        return 'initial';
    }

    #[InitializeStateMachine]
    public function seed(int $value): void
    {
    }

    #[StateMachineRule(produces: 'accounts')]
    public function open(#[ForAll('names')] string $name): string
    {
        $this->balances[$name] = 0;

        return $name;
    }

    #[StateMachineRule]
    public function deposit(#[FromPool('accounts')] string $account, #[ForAll('amounts')] int $amount): void
    {
        $this->balances[$account] += $amount;
    }

    #[StateMachineRule(precondition: 'hasAccounts')]
    public function close(#[FromPool('accounts', consume: true)] string $account): void
    {
        unset($this->balances[$account]);
    }

    public function hasAccounts(): bool
    {
        return $this->balances !== [];
    }

    #[StateMachineInvariant]
    public function balancesAreNotNegative(): void
    {
    }

    #[StateMachineInvariant(atEnd: true)]
    public function balancesAddUp(): void
    {
    }

    public function isNotAStep(): void
    {
    }
}
