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
 * A destination for events that can divert them: between
 * startCollectingEvents() and stopCollectingEvents(), the events emitted into
 * it are handed back to the caller instead of being processed, and the caller
 * decides with forward() which of them become part of the test run's event
 * stream after all.
 *
 * This is what lets the orchestration of a repeated or retried PHPT test — for
 * which the outcome of each run has to be examined before the next one is
 * decided — be written once and used both by a sequential run, where the
 * destination is the global event facade, and by a parallel run, where it is
 * the collecting emitter of the unit the test runs as.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This interface is not covered by the backward compatibility promise for PHPUnit
 */
interface EventCollector
{
    /**
     * @throws EventsAreAlreadyBeingCollectedException
     */
    public function startCollectingEvents(): void;

    /**
     * @throws EventsAreNotBeingCollectedException
     */
    public function stopCollectingEvents(): EventCollection;

    public function forward(EventCollection $events): void;
}
