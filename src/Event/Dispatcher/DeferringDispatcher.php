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
final class DeferringDispatcher implements SubscribableDispatcher
{
    private readonly SubscribableDispatcher $dispatcher;
    private EventCollection $events;
    private bool $recording = true;

    public function __construct(SubscribableDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        $this->events     = new EventCollection;
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
     * @todo Remove this method once we found a better way to avoid creating event objects
     *       that are expensive to create when there are no subscribers registered for them
     *
     * @see https://github.com/sebastianbergmann/phpunit/issues/5261
     */
    public function seal(): void
    {
        $this->dispatcher->seal();
    }

    public function dispatch(Event $event): void
    {
        if ($this->recording) {
            $this->events->add($event);

            return;
        }

        $this->dispatcher->dispatch($event);
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
