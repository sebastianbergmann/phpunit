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

use PHPUnit\Event\EventFacadeIsSealedException;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Event\Subscriber;
use PHPUnit\Event\Tracer\Tracer;
use PHPUnit\Event\UnknownSubscriberTypeException;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class Facade
{
    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public function registerSubscribers(Subscriber ...$subscribers): void
    {
        EventFacade::registerSubscribers(...$subscribers);
    }

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public function registerSubscriber(Subscriber $subscriber): void
    {
        EventFacade::registerSubscriber($subscriber);
    }

    /**
     * @throws EventFacadeIsSealedException
     */
    public function registerTracer(Tracer $tracer): void
    {
        EventFacade::registerTracer($tracer);
    }
}
