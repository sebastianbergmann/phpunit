<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting\StateMachine;

use function sprintf;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\FromPool;
use PHPUnit\Framework\Attributes\Invariant;
use PHPUnit\Framework\Attributes\Rule;

/**
 * Compares the bank and its projection with a model of the balances of the
 * accounts. Catching up the projection is a rule of its own, so that the
 * steps of the state machine explore when it catches up.
 */
final class BankMachine
{
    private readonly Bank $bank;
    private readonly EventStore $store;
    private readonly BalanceProjection $projection;

    /**
     * @var array<string, int>
     */
    private array $balances = [];

    /**
     * The model when the projection last caught up with the event store.
     *
     * @var array<string, int>
     */
    private array $projectedBalances = [];

    public function __construct(Bank $bank, EventStore $store, BalanceProjection $projection)
    {
        $this->bank       = $bank;
        $this->store      = $store;
        $this->projection = $projection;
    }

    #[Rule(produces: 'accounts')]
    public function open(): AccountId
    {
        $account = $this->bank->openAccount();

        $this->balances[$account->value] = 0;

        return $account;
    }

    /**
     * @param int<1, 100> $amount
     *
     * @return list<object>
     */
    #[Rule]
    public function deposit(#[FromPool('accounts')] AccountId $account, int $amount): array
    {
        $events = $this->bank->deposit($account, $amount);

        $this->balances[$account->value] += $amount;

        return $events;
    }

    /**
     * @param int<1, 100> $amount
     *
     * @return list<object>
     */
    #[Rule]
    public function withdraw(#[FromPool('accounts')] AccountId $account, int $amount): array
    {
        if ($amount > $this->balances[$account->value]) {
            // the command is rejected, and no events are recorded
            try {
                $this->bank->withdraw($account, $amount);
            } catch (InsufficientFundsException) {
                return [];
            }

            Assert::fail(sprintf('Withdrawing %d from %s was not rejected', $amount, $account->value));
        }

        $events = $this->bank->withdraw($account, $amount);

        $this->balances[$account->value] -= $amount;

        return $events;
    }

    #[Rule]
    public function catchUpProjection(): void
    {
        $this->projection->catchUp($this->store);

        $this->projectedBalances = $this->balances;
    }

    #[Invariant]
    public function projectionMatchesTheModel(): void
    {
        Assert::assertSame($this->projectedBalances, $this->projection->balances());
    }

    #[Invariant(atEnd: true)]
    public function accountsRebuiltFromTheirEventsMatchTheModel(): void
    {
        foreach ($this->balances as $account => $balance) {
            Assert::assertSame($balance, $this->bank->balanceOf(new AccountId($account)));
        }
    }
}
