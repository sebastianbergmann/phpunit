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
interface SubscribableDispatcher extends Dispatcher
{
    /**
     * @throws UnknownSubscriberTypeException
     */
    public function registerSubscriber(Subscriber $subscriber): void;

    public function registerTracer(Tracer\Tracer $tracer): void;

    /**
     * @psalm-param class-string $className
     */
    public function hasSubscriberFor(string $className): bool;
}
