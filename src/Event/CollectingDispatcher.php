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
    /**
     * @psalm-var list<Event>
     */
    private array $events = [];

    public function dispatch(Event $event): void
    {
        $this->events[] = $event;
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

    /**
     * @psalm-return list<Event>
     */
    public function flush(): array
    {
        $events = $this->events;

        $this->events = [];

        return $events;
    }
}
