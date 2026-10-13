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

use PHPUnit\Runner\DeprecationCollector\Facade as DeprecationCollector;
use PHPUnit\Runner\DeprecationCollector\TestTriggeredDeprecationSubscriber;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class CollectingDispatcher implements Dispatcher
{
    use CollectionWindow;
    private EventCollection $events;
    private DirectDispatcher $isolatedDirectDispatcher;
    private DirectDispatcher $collectedEventsDispatcher;

    public function __construct(DirectDispatcher $directDispatcher, DirectDispatcher $collectedEventsDispatcher)
    {
        $this->isolatedDirectDispatcher  = $directDispatcher;
        $this->collectedEventsDispatcher = $collectedEventsDispatcher;
        $this->events                    = new EventCollection;

        $this->isolatedDirectDispatcher->registerSubscriber(new TestTriggeredDeprecationSubscriber(DeprecationCollector::collector()));
    }

    public function dispatch(Event $event): void
    {
        if ($this->collectDispatchedEvent($event)) {
            try {
                $this->collectedEventsDispatcher->dispatch($event);
            } catch (UnknownEventTypeException) {
                // Do nothing.
            }

            return;
        }

        $this->events->add($event);

        try {
            $this->isolatedDirectDispatcher->dispatch($event);
        } catch (UnknownEventTypeException) {
            // Do nothing.
        }
    }

    /**
     * Registers a subscriber with the direct dispatcher that events are
     * dispatched to as they are collected, so that it receives the events of
     * the test that a child process runs while the test is running. The
     * parallel test runner's worker uses this to observe, while a unit is
     * running, the events that have become part of the unit's recorded stream
     * — an event diverted by a collection window is not dispatched and thus
     * not observed until the window's owner forwards it.
     *
     * @throws UnknownSubscriberTypeException
     */
    public function registerSubscriber(Subscriber $subscriber): void
    {
        $this->isolatedDirectDispatcher->registerSubscriber($subscriber);
    }

    /**
     * Registers a subscriber that is notified of events as they are diverted
     * by a collection window, in addition to any subscriber that is notified
     * when the window's owner forwards them.
     *
     * This is for state that is consulted while a test is running, for
     * instance the deprecations that the expectations of a test are verified
     * against, which cannot wait until the events of an attempt of a retried
     * test that runs in a worker of a parallel test run have been collected
     * and forwarded.
     *
     * @throws UnknownSubscriberTypeException
     */
    public function registerSubscriberForCollectedEvents(Subscriber $subscriber): void
    {
        $this->collectedEventsDispatcher->registerSubscriber($subscriber);
    }

    public function flush(): EventCollection
    {
        $events = $this->events;

        $this->events = new EventCollection;

        return $events;
    }
}
