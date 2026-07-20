<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event;

use function assert;

/**
 * The collection window a dispatcher opens to divert events: between
 * startCollectingEvents() and stopCollectingEvents(), dispatched events are
 * collected instead of being processed, so that the window's owner — a
 * RetryTestSuite suppressing the events of a failed attempt, for example —
 * decides what becomes of them.
 *
 * The window behaves identically in every dispatcher that offers it; this
 * trait is that one behavior, so that the dispatchers cannot drift apart.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This trait is not covered by the backward compatibility promise for PHPUnit
 */
trait CollectionWindow
{
    private ?EventCollection $collectedEvents = null;

    public function startCollectingEvents(): void
    {
        assert($this->collectedEvents === null);

        $this->collectedEvents = new EventCollection;
    }

    public function stopCollectingEvents(): EventCollection
    {
        assert($this->collectedEvents !== null);

        $events = $this->collectedEvents;

        $this->collectedEvents = null;

        return $events;
    }

    /**
     * Divert the event into the open collection window; false when no window
     * is open and the dispatcher processes the event as usual.
     */
    private function collectDispatchedEvent(Event $event): bool
    {
        if ($this->collectedEvents === null) {
            return false;
        }

        $this->collectedEvents->add($event);

        return true;
    }
}
