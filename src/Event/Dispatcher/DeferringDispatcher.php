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

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class DeferringDispatcher implements SubscribableDispatcher
{
    private readonly SubscribableDispatcher $dispatcher;
    private readonly SubscribableDispatcher $collectedEventsDispatcher;
    private EventCollection $events;
    private bool $recording                   = true;
    private ?EventCollection $collectedEvents = null;

    public function __construct(SubscribableDispatcher $dispatcher, SubscribableDispatcher $collectedEventsDispatcher)
    {
        $this->dispatcher                = $dispatcher;
        $this->collectedEventsDispatcher = $collectedEventsDispatcher;
        $this->events                    = new EventCollection;
    }

    public function registerTracer(Tracer\Tracer $tracer): void
    {
        $this->dispatcher->registerTracer($tracer);
    }

    public function registerSubscriber(Subscriber $subscriber): void
    {
        $this->dispatcher->registerSubscriber($subscriber);
    }

    /**
     * Registers a subscriber that is notified of events as they are collected,
     * in addition to any subscriber that is notified when they are forwarded.
     *
     * This is for state that is consulted while a test is running, for
     * instance the deprecations that the expectations of a test are verified
     * against, which cannot wait until the events of an attempt of a retried
     * test have been collected and forwarded.
     */
    public function registerSubscriberForCollectedEvents(Subscriber $subscriber): void
    {
        $this->collectedEventsDispatcher->registerSubscriber($subscriber);
    }

    public function dispatch(Event $event): void
    {
        if ($this->collectedEvents !== null) {
            $this->collectedEvents->add($event);

            $this->collectedEventsDispatcher->dispatch($event);

            return;
        }

        if ($this->recording) {
            $this->events->add($event);

            return;
        }

        $this->dispatcher->dispatch($event);
    }

    /**
     * @throws EventsAreAlreadyBeingCollectedException
     */
    public function startCollectingEvents(): void
    {
        if ($this->collectedEvents !== null) {
            throw new EventsAreAlreadyBeingCollectedException;
        }

        $this->collectedEvents = new EventCollection;
    }

    /**
     * @throws EventsAreNotBeingCollectedException
     */
    public function stopCollectingEvents(): EventCollection
    {
        if ($this->collectedEvents === null) {
            throw new EventsAreNotBeingCollectedException;
        }

        $events = $this->collectedEvents;

        $this->collectedEvents = null;

        return $events;
    }

    public function flush(): void
    {
        $this->recording = false;

        foreach ($this->events as $event) {
            $this->dispatcher->dispatch($event);
        }

        $this->events = new EventCollection;
    }
}
