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

use function count;

/**
 * A read model of the balances of all accounts that is updated when it
 * catches up with the event store. The bug that can be planted skips the
 * first event that was appended after the previous catch-up.
 */
final class BalanceProjection
{
    private readonly bool $catchingUpWithBug;

    /**
     * @var array<string, int>
     */
    private array $balances = [];
    private int $position   = 0;

    public function __construct(bool $catchingUpWithBug)
    {
        $this->catchingUpWithBug = $catchingUpWithBug;
    }

    public function catchUp(EventStore $store): void
    {
        $events = $store->events();
        $start  = $this->position;

        if ($this->catchingUpWithBug && $start > 0) {
            $start++;
        }

        for ($position = $start; $position < count($events); $position++) {
            $this->apply($events[$position]);
        }

        $this->position = count($events);
    }

    /**
     * @return array<string, int>
     */
    public function balances(): array
    {
        return $this->balances;
    }

    private function apply(object $event): void
    {
        if ($event instanceof AccountOpened) {
            $this->balances[$event->account->value] = 0;
        }

        if ($event instanceof MoneyDeposited) {
            $this->balances[$event->account->value] += $event->amount;
        }

        if ($event instanceof MoneyWithdrawn) {
            $this->balances[$event->account->value] -= $event->amount;
        }
    }
}
