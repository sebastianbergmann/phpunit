<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestRunner\TestResult;

use PHPUnit\Event\EventFacadeIsSealedException;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Event\UnknownSubscriberTypeException;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Facade
{
    private static ?Collector $collector = null;

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public static function init(): void
    {
        self::collector();
    }

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public static function result(): TestResult
    {
        return self::collector()->result();
    }

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public static function hasTestErroredEvents(): bool
    {
        return self::collector()->hasTestErroredEvents();
    }

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public static function hasTestFailedEvents(): bool
    {
        return self::collector()->hasTestFailedEvents();
    }

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public static function hasWarningEvents(): bool
    {
        return self::collector()->hasWarningEvents();
    }

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public static function hasTestConsideredRiskyEvents(): bool
    {
        return self::collector()->hasTestConsideredRiskyEvents();
    }

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public static function hasTestSkippedEvents(): bool
    {
        return self::collector()->hasTestSkippedEvents();
    }

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public static function hasTestMarkedIncompleteEvents(): bool
    {
        return self::collector()->hasTestMarkedIncompleteEvents();
    }

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    private static function collector(): Collector
    {
        if (self::$collector === null) {
            self::$collector = new Collector(EventFacade::instance());
        }

        return self::$collector;
    }
}
