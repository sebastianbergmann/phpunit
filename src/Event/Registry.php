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

use function date_default_timezone_get;
use DateTimeZone;
use PHPUnit\Event\Telemetry\System;
use PHPUnit\Event\Telemetry\SystemClock;
use PHPUnit\Event\Telemetry\SystemMemoryMeter;

final class Registry
{
    private static ?TypeMap $typeMap = null;

    private static ?Emitter $emitter = null;

    private static ?Dispatcher $dispatcher = null;

    public static function emitter(): Emitter
    {
        if (self::$emitter === null) {
            self::$emitter = new DispatchingEmitter(
                self::dispatcher(),
                new System(
                    new SystemClock(new DateTimeZone(date_default_timezone_get())),
                    new SystemMemoryMeter()
                )
            );
        }

        return self::$emitter;
    }

    private static function dispatcher(): Dispatcher
    {
        if (self::$dispatcher === null) {
            self::$dispatcher = new Dispatcher(self::typeMap());
        }

        return self::$dispatcher;
    }

    private static function typeMap(): TypeMap
    {
        if (self::$typeMap === null) {
            self::$typeMap = new TypeMap();
            self::registerDefaultTypes();
        }

        return self::$typeMap;
    }

    private static function registerDefaultTypes(): void
    {
        $defaultEvents = [
            Application\Configured::class,
            Application\Started::class,
            Assertion\Made::class,
            Bootstrap\Finished::class,
            Comparator\Registered::class,
            Extension\Loaded::class,
            GlobalState\Captured::class,
            GlobalState\Modified::class,
            GlobalState\Restored::class,
            Test\RunConfigured::class,
            Test\RunErrored::class,
            Test\RunFailed::class,
            Test\RunFinished::class,
            Test\RunPassed::class,
            Test\RunRisky::class,
            Test\RunSkippedByDataProvider::class,
            Test\RunSkippedIncomplete::class,
            Test\RunSkippedWithFailedRequirements::class,
            Test\RunSkippedWithWarning::class,
            Test\RunStarted::class,
            Test\SetUpFinished::class,
            Test\TearDownFinished::class,
            TestCase\AfterClassFinished::class,
            TestCase\BeforeClassFinished::class,
            TestCase\SetUpBeforeClassFinished::class,
            TestCase\SetUpFinished::class,
            TestCase\TearDownAfterClassFinished::class,
            TestDouble\MockCreated::class,
            TestDouble\MockForTraitCreated::class,
            TestDouble\PartialMockCreated::class,
            TestDouble\ProphecyCreated::class,
            TestDouble\TestProxyCreated::class,
            TestSuite\AfterClassFinished::class,
            TestSuite\BeforeClassFinished::class,
            TestSuite\Configured::class,
            TestSuite\Loaded::class,
            TestSuite\RunFinished::class,
            TestSuite\RunStarted::class,
            TestSuite\Sorted::class,
        ];

        foreach ($defaultEvents as $eventClass) {
            self::typeMap()->addMapping(
                $eventClass . 'Subscriber',
                $eventClass
            );
        }
    }
}
