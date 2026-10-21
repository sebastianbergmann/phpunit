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

/**
 * Handles the commands for accounts: it rebuilds an account from its events,
 * lets it handle the command, and appends the events that it recorded to the
 * event store.
 */
final class Bank
{
    private readonly EventStore $store;
    private readonly bool $rebuildingWithBug;
    private int $numberOfAccounts = 0;

    public function __construct(EventStore $store, bool $rebuildingWithBug)
    {
        $this->store             = $store;
        $this->rebuildingWithBug = $rebuildingWithBug;
    }

    public function openAccount(): AccountId
    {
        $this->numberOfAccounts++;

        $id = new AccountId('account-' . $this->numberOfAccounts);

        $this->store->append(Account::open($id)->releaseEvents());

        return $id;
    }

    /**
     * @return list<object>
     */
    public function deposit(AccountId $id, int $amount): array
    {
        $account = $this->account($id);

        $account->deposit($id, $amount);

        return $this->save($account);
    }

    /**
     * @throws InsufficientFundsException
     *
     * @return list<object>
     */
    public function withdraw(AccountId $id, int $amount): array
    {
        $account = $this->account($id);

        $account->withdraw($id, $amount);

        return $this->save($account);
    }

    public function balanceOf(AccountId $id): int
    {
        return $this->account($id)->balance();
    }

    private function account(AccountId $id): Account
    {
        return Account::fromEvents($this->store->eventsOf($id), $this->rebuildingWithBug);
    }

    /**
     * @return list<object>
     */
    private function save(Account $account): array
    {
        $events = $account->releaseEvents();

        $this->store->append($events);

        return $events;
    }
}
