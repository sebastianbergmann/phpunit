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

use function array_key_exists;
use function assert;
use function dirname;
use function sprintf;
use function str_starts_with;
use PHPUnit\Event\Test\AssertionFailedSubscriber;
use PHPUnit\Event\Test\AssertionSucceededSubscriber;
use Throwable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class DirectDispatcher implements SubscribableDispatcher
{
    private readonly TypeMap $typeMap;

    /**
     * @psalm-var array<class-string, list<Subscriber>>
     */
    private array $subscribers = [];

    /**
     * @psalm-var list<Tracer\Tracer>
     */
    private array $tracers = [];

    public function __construct(TypeMap $map)
    {
        $this->typeMap = $map;
    }

    /**
     * @todo Remove this method once we found a better way to avoid creating event objects
     *       that are expensive to create when there are no subscribers registered for them
     *
     * @see https://github.com/sebastianbergmann/phpunit/issues/5261
     */
    public function seal(): void
    {
        $emitter = Facade::emitter();

        assert($emitter instanceof DispatchingEmitter);

        if (!empty($this->tracers)) {
            $emitter->emitAssertionSucceededEvents();
            $emitter->emitAssertionFailedEvents();

            return;
        }

        if (isset($this->subscribers[AssertionSucceededSubscriber::class])) {
            $emitter->emitAssertionSucceededEvents();
        }

        if (isset($this->subscribers[AssertionFailedSubscriber::class])) {
            $emitter->emitAssertionFailedEvents();
        }
    }

    public function registerTracer(Tracer\Tracer $tracer): void
    {
        $this->tracers[] = $tracer;
    }

    /**
     * @throws MapError
     * @throws UnknownSubscriberTypeException
     */
    public function registerSubscriber(Subscriber $subscriber): void
    {
        if (!$this->typeMap->isKnownSubscriberType($subscriber)) {
            throw new UnknownSubscriberTypeException(
                sprintf(
                    'Subscriber "%s" does not implement any known interface - did you forget to register it?',
                    $subscriber::class
                )
            );
        }

        $eventClassName = $this->typeMap->map($subscriber);

        if (!array_key_exists($eventClassName, $this->subscribers)) {
            $this->subscribers[$eventClassName] = [];
        }

        $this->subscribers[$eventClassName][] = $subscriber;
    }

    /**
     * @throws UnknownEventTypeException
     */
    public function dispatch(Event $event): void
    {
        $eventClassName = $event::class;

        if (!$this->typeMap->isKnownEventType($event)) {
            throw new UnknownEventTypeException(
                sprintf(
                    'Unknown event type "%s"',
                    $eventClassName
                )
            );
        }

        foreach ($this->tracers as $tracer) {
            try {
                $tracer->trace($event);
            } catch (Throwable $t) {
                $this->ignoreThrowablesFromThirdPartySubscribers($t);
            }
        }

        if (!array_key_exists($eventClassName, $this->subscribers)) {
            return;
        }

        foreach ($this->subscribers[$eventClassName] as $subscriber) {
            try {
                $subscriber->notify($event);
            } catch (Throwable $t) {
                $this->ignoreThrowablesFromThirdPartySubscribers($t);
            }
        }
    }

    /**
     * @throws Throwable
     */
    private function ignoreThrowablesFromThirdPartySubscribers(Throwable $t): void
    {
        if (str_starts_with($t->getFile(), dirname(__DIR__, 2))) {
            throw $t;
        }
    }
}
