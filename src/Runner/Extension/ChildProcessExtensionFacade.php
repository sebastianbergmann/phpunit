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

use PHPUnit\Event\CollectingDispatcher;
use PHPUnit\Event\Subscriber;
use PHPUnit\Event\UnknownSubscriberTypeException;

/**
 * Registers the subscribers of the extensions that are bootstrapped in a
 * child process with the dispatcher that collects the events of the test
 * that the child process runs.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ChildProcessExtensionFacade implements ChildProcessFacade
{
    private CollectingDispatcher $dispatcher;

    public function __construct(CollectingDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
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
        $this->dispatcher->registerSubscriber($subscriber);
    }
}
