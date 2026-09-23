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
use PHPUnit\Event\Subscriber;
use PHPUnit\Event\Tracer\Tracer;
use PHPUnit\Event\UnknownSubscriberTypeException;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
interface Facade
{
    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public function registerSubscribers(Subscriber ...$subscribers): void;

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public function registerSubscriber(Subscriber $subscriber): void;

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public function registerSubscribersForEventsOfThisProcess(Subscriber ...$subscribers): void;

    /**
     * Register a subscriber that is notified only of the events that were
     * emitted in this process.
     *
     * The events of a test that ran in a parallel worker process or in a
     * separate process are emitted there and replayed in this process: a
     * subscriber registered with registerSubscriber() receives them, a
     * subscriber registered with this method does not. This is what a
     * subscriber needs that acts on a test in the process that runs it, such
     * as one that wraps every test in a database transaction, when the
     * extension also registers it in the worker processes of a parallel test
     * run (see ParallelWorkerExtension).
     *
     * An event is considered to be emitted in this process when the process
     * ID in its telemetry information (see Telemetry\Info::processId()) is
     * that of this process. This includes the events that the test runner
     * emits in this process on behalf of tests that did not run here, for
     * instance the "test errored" and "test finished" events of the tests of a
     * parallel worker process that crashed.
     *
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public function registerSubscriberForEventsOfThisProcess(Subscriber $subscriber): void;

    /**
     * @throws EventFacadeIsSealedException
     */
    public function registerTracer(Tracer $tracer): void;

    public function replaceOutput(): void;

    public function replaceProgressOutput(): void;

    public function replaceResultOutput(): void;

    public function requireCodeCoverageCollection(): void;
}
