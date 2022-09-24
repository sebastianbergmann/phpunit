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

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Facade
{
    private static ?Collector $collector = null;

    public static function init(): void
    {
        self::collector();
    }

    public static function result(): TestResult
    {
        return self::collector()->result();
    }

    public static function hasTestErroredEvents(): bool
    {
        return self::collector()->hasTestErroredEvents();
    }

    public static function hasTestFailedEvents(): bool
    {
        return self::collector()->hasTestFailedEvents();
    }

    public static function hasWarningEvents(): bool
    {
        return self::collector()->hasWarningEvents();
    }

    public static function hasTestConsideredRiskyEvents(): bool
    {
        return self::collector()->hasTestConsideredRiskyEvents();
    }

    public static function hasTestSkippedEvents(): bool
    {
        return self::collector()->hasTestSkippedEvents();
    }

    public static function hasTestMarkedIncompleteEvents(): bool
    {
        return self::collector()->hasTestMarkedIncompleteEvents();
    }

    private static function collector(): Collector
    {
        if (self::$collector === null) {
            self::$collector = new Collector;
        }

        return self::$collector;
    }
}
