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
use PHPUnit\Runner\DeprecationCollector\Facade as DeprecationCollector;
use PHPUnit\Runner\DeprecationCollector\TestTriggeredDeprecationSubscriber;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class CollectingDispatcher implements Dispatcher
{
    private EventCollection $events;
    private DirectDispatcher $isolatedDirectDispatcher;
    private ?EventCollection $collectedEvents = null;

    public function __construct(DirectDispatcher $directDispatcher)
    {
        $this->isolatedDirectDispatcher = $directDispatcher;
        $this->events                   = new EventCollection;

        $this->isolatedDirectDispatcher->registerSubscriber(new TestTriggeredDeprecationSubscriber(DeprecationCollector::collector()));
    }

    public function dispatch(Event $event): void
    {
        if ($this->collectedEvents !== null) {
            $this->collectedEvents->add($event);

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
     * Open a collection window: until stopCollectingEvents() is called, events
     * are diverted into a separate collection instead of being recorded and
     * dispatched. This mirrors the collection window of the DeferringDispatcher
     * so that a RetryTestSuite can suppress the events of a failed attempt when
     * it runs in a process whose event facade was initialized for isolation.
     */
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

    public function flush(): EventCollection
    {
        $events = $this->events;

        $this->events = new EventCollection;

        return $events;
    }
}
