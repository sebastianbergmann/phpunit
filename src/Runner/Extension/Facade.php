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
     */
    public function registerTracer(Tracer $tracer): void;

    public function replaceOutput(): void;

    public function replacesOutput(): bool;

    public function replaceProgressOutput(): void;

    public function replacesProgressOutput(): bool;

    public function replaceResultOutput(): void;

    public function replacesResultOutput(): bool;

    public function requireCodeCoverageCollection(): void;

    public function requiresCodeCoverageCollection(): bool;
}
