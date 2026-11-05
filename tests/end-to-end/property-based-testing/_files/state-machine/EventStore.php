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

final class EventStore
{
    /**
     * @var list<object>
     */
    private array $events = [];

    /**
     * @param list<object> $events
     */
    public function append(array $events): void
    {
        foreach ($events as $event) {
            $this->events[] = $event;
        }
    }

    /**
     * @return list<object>
     */
    public function events(): array
    {
        return $this->events;
    }

    /**
     * @return list<object>
     */
    public function eventsOf(AccountId $account): array
    {
        $events = [];

        foreach ($this->events as $event) {
            if ($event->account == $account) {
                $events[] = $event;
            }
        }

        return $events;
    }
}
