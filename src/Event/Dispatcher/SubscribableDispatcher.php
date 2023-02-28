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
     * @todo Remove this method once we found a better way to avoid creating event objects
     *       that are expensive to create when there are no subscribers registered for them
     *
     * @see https://github.com/sebastianbergmann/phpunit/issues/5261
     */
    public function seal(): void;
}
