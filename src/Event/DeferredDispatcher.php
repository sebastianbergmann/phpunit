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
final class DeferredDispatcher implements Dispatcher
{
    private Dispatcher $dispatcher;

    /**
     * @psalm-var list<Event>
     */
    private array $events = [];

    private bool $recording = true;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function registerTracer(Tracer\Tracer $tracer): void
    {
        $this->dispatcher->registerTracer($tracer);
    }

    public function registerSubscriber(Subscriber $subscriber): void
    {
        $this->dispatcher->registerSubscriber($subscriber);
    }

    public function dispatch(Event $event): void
    {
        if ($this->recording) {
            $this->events[] = $event;

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

        $this->events = [];
    }
}
