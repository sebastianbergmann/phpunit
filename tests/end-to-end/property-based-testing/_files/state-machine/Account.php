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
 * An aggregate whose state is rebuilt from the events that it recorded. The
 * bug that can be planted applies a withdrawal like a deposit when the state
 * is rebuilt.
 */
final class Account
{
    private int $balance = 0;

    /**
     * @var list<object>
     */
    private array $recordedEvents = [];

    public static function open(AccountId $id): self
    {
        $account = new self;

        $account->recordedEvents[] = new AccountOpened($id);

        return $account;
    }

    /**
     * @param list<object> $events
     */
    public static function fromEvents(array $events, bool $withBug): self
    {
        $account = new self;

        foreach ($events as $event) {
            if ($event instanceof MoneyDeposited) {
                $account->balance += $event->amount;
            }

            if ($event instanceof MoneyWithdrawn) {
                if ($withBug) {
                    $account->balance += $event->amount;
                } else {
                    $account->balance -= $event->amount;
                }
            }
        }

        return $account;
    }

    public function deposit(AccountId $id, int $amount): void
    {
        $this->balance += $amount;

        $this->recordedEvents[] = new MoneyDeposited($id, $amount);
    }

    /**
     * @throws InsufficientFundsException
     */
    public function withdraw(AccountId $id, int $amount): void
    {
        if ($amount > $this->balance) {
            throw new InsufficientFundsException;
        }

        $this->balance -= $amount;

        $this->recordedEvents[] = new MoneyWithdrawn($id, $amount);
    }

    public function balance(): int
    {
        return $this->balance;
    }

    /**
     * @return list<object>
     */
    public function releaseEvents(): array
    {
        $events = $this->recordedEvents;

        $this->recordedEvents = [];

        return $events;
    }
}
