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
 * @internal This interface is not covered by the backward compatibility promise for PHPUnit
 */
interface Dispatcher
{
    public function registerTracer(Tracer\Tracer $tracer): void;

    /**
     * @throws UnknownSubscriberTypeException
     */
    public function registerSubscriber(Subscriber $subscriber): void;

    /**
     * @throws UnknownEventTypeException
     */
    public function dispatch(Event $event): void;
}
