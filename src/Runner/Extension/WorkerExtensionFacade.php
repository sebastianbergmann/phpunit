<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Extension;

use function sprintf;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Event\Subscriber;
use PHPUnit\Event\UnknownSubscriberTypeException;

/**
 * Collects the subscribers that the extensions bootstrapped in a parallel
 * worker process register.
 *
 * The subscribers are not registered with the worker's event facade directly:
 * the worker initializes its event dispatching anew for every unit it runs,
 * and the collected subscribers are registered with each unit's dispatcher in
 * turn (see the worker template). The subscriber objects themselves persist
 * for the lifetime of the worker process, so an extension's state carries
 * over from one unit to the next, the way it does in the main process.
 *
 * A subscriber of a type that events cannot be dispatched to is rejected when
 * it is registered, as it is in the child process of a test that runs in a
 * separate process: the extension's bootstrap fails, and the worker reports
 * that failure (see WorkerExtensionBootstrapper).
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class WorkerExtensionFacade implements ChildProcessFacade
{
    private readonly EventFacade $eventFacade;

    /**
     * @var list<Subscriber>
     */
    private array $subscribers = [];

    public function __construct(EventFacade $eventFacade)
    {
        $this->eventFacade = $eventFacade;
    }

    /**
     * @throws UnknownSubscriberTypeException
     */
    public function registerSubscribers(Subscriber ...$subscribers): void
    {
        foreach ($subscribers as $subscriber) {
            $this->registerSubscriber($subscriber);
        }
    }

    /**
     * @throws UnknownSubscriberTypeException
     */
    public function registerSubscriber(Subscriber $subscriber): void
    {
        if (!$this->eventFacade->isKnownSubscriberType($subscriber)) {
            throw new UnknownSubscriberTypeException(
                sprintf(
                    'Subscriber "%s" does not implement any known interface - did you forget to register it?',
                    $subscriber::class,
                ),
            );
        }

        $this->subscribers[] = $subscriber;
    }

    /**
     * @return list<Subscriber>
     */
    public function subscribers(): array
    {
        return $this->subscribers;
    }
}
