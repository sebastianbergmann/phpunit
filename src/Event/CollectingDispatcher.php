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
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class CollectingDispatcher implements Dispatcher
{
    private EventCollection $events;

    public function __construct()
    {
        $this->events = new EventCollection;
    }

    public function dispatch(Event $event): void
    {
        $this->events->add($event);
    }

    /**
     * @throws TracerRegistrationNotSupportedException
     *
     * @todo Narrow Dispatcher interface
     */
    public function registerTracer(Tracer\Tracer $tracer): void
    {
        throw new TracerRegistrationNotSupportedException;
    }

    /**
     * @throws SubscriberRegistrationNotSupportedException
     *
     * @todo Narrow Dispatcher interface
     */
    public function registerSubscriber(Subscriber $subscriber): void
    {
        throw new SubscriberRegistrationNotSupportedException;
    }

    public function flush(): EventCollection
    {
        $events = $this->events;

        $this->events = new EventCollection;

        return $events;
    }
}
