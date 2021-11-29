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

use PHPUnit\Event\Telemetry\HRTime;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class Facade
{
    private static ?TypeMap $typeMap                       = null;
    private static ?Emitter $emitter                       = null;
    private static ?Emitter $suspended                     = null;
    private static ?DeferredDispatcher $deferredDispatcher = null;
    private static bool $sealed                            = false;

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public static function registerSubscriber(Subscriber $subscriber): void
    {
        if (self::$sealed) {
            throw new EventFacadeIsSealedException;
        }

        self::deferredDispatcher()->registerSubscriber($subscriber);
    }

    /**
     * @throws EventFacadeIsSealedException
     */
    public static function registerTracer(Tracer\Tracer $tracer): void
    {
        if (self::$sealed) {
            throw new EventFacadeIsSealedException;
        }

        self::deferredDispatcher()->registerTracer($tracer);
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public static function emitter(): Emitter
    {
        if (self::$emitter === null) {
            self::$emitter = self::createDispatchingEmitter();
        }

        return self::$emitter;
    }

    /**
     * @throws NoEmitterToSuspendException
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public static function suspend(): void
    {
        if (self::$emitter === null) {
            throw new NoEmitterToSuspendException;
        }

        self::$suspended = self::$emitter;

        self::$emitter = new DispatchingEmitter(
            new CollectingDispatcher,
            self::createTelemetrySystem()
        );
    }

    /**
     * @throws NoEmitterToResumeException
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public static function resume(): void
    {
        if (self::$suspended === null) {
            throw new NoEmitterToResumeException;
        }

        self::$emitter   = self::$suspended;
        self::$suspended = null;
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public static function initForIsolation(HRTime $offset): CollectingDispatcher
    {
        $dispatcher = new CollectingDispatcher;

        self::$emitter = new DispatchingEmitter(
            $dispatcher,
            new Telemetry\System(
                new Telemetry\SystemStopWatchWithOffset($offset),
                new Telemetry\SystemMemoryMeter
            )
        );

        self::$sealed = true;

        return $dispatcher;
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public static function forward(EventCollection $events): void
    {
        if (self::$suspended !== null) {
            return;
        }

        $dispatcher = self::deferredDispatcher();

        foreach ($events as $event) {
            $dispatcher->dispatch($event);
        }
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public static function seal(): void
    {
        self::$deferredDispatcher->flush();

        self::$sealed = true;

        self::emitter()->eventFacadeSealed();
    }

    private static function createDispatchingEmitter(): DispatchingEmitter
    {
        return new DispatchingEmitter(
            self::deferredDispatcher(),
            self::createTelemetrySystem()
        );
    }

    private static function createTelemetrySystem(): Telemetry\System
    {
        return new Telemetry\System(
            new Telemetry\SystemStopWatch,
            new Telemetry\SystemMemoryMeter
        );
    }

    private static function deferredDispatcher(): DeferredDispatcher
    {
        if (self::$deferredDispatcher === null) {
            self::$deferredDispatcher = new DeferredDispatcher(
                new DirectDispatcher(self::typeMap())
            );
        }

        return self::$deferredDispatcher;
    }

    private static function typeMap(): TypeMap
    {
        if (self::$typeMap === null) {
            $typeMap = new TypeMap;

            self::registerDefaultTypes($typeMap);

            self::$typeMap = $typeMap;
        }

        return self::$typeMap;
    }

    private static function registerDefaultTypes(TypeMap $typeMap): void
    {
        $defaultEvents = [
            Test\AssertionMade::class,
            TestRunner\BootstrapFinished::class,
            Test\ComparatorRegistered::class,
            TestRunner\ExtensionLoaded::class,
            GlobalState\Captured::class,
            GlobalState\Modified::class,
            GlobalState\Restored::class,
            Test\Aborted::class,
            Test\AfterLastTestMethodCalled::class,
            Test\AfterLastTestMethodFinished::class,
            Test\AfterTestMethodCalled::class,
            Test\AfterTestMethodFinished::class,
            Test\BeforeFirstTestMethodCalled::class,
            Test\BeforeFirstTestMethodFinished::class,
            Test\BeforeTestMethodCalled::class,
            Test\BeforeTestMethodFinished::class,
            Test\Errored::class,
            Test\Failed::class,
            Test\Finished::class,
            Test\OutputPrinted::class,
            Test\Passed::class,
            Test\ConsideredRisky::class,
            Test\PassedWithWarning::class,
            Test\PostConditionCalled::class,
            Test\PostConditionFinished::class,
            Test\PreConditionCalled::class,
            Test\PreConditionFinished::class,
            Test\Prepared::class,
            Test\Skipped::class,
            Test\DeprecatedPhpunitFeatureUsed::class,
            Test\DeprecatedPhpFeatureUsed::class,
            Test\DeprecatedFeatureUsed::class,
            TestDouble\MockObjectCreated::class,
            TestDouble\MockObjectCreatedForAbstractClass::class,
            TestDouble\MockObjectCreatedForTrait::class,
            TestDouble\MockObjectCreatedFromWsdl::class,
            TestDouble\PartialMockObjectCreated::class,
            TestDouble\TestProxyCreated::class,
            TestDouble\TestStubCreated::class,
            TestRunner\Started::class,
            TestRunner\Configured::class,
            TestRunner\EventFacadeSealed::class,
            TestRunner\ExecutionStarted::class,
            TestRunner\Finished::class,
            TestSuite\Finished::class,
            TestSuite\Loaded::class,
            TestSuite\Filtered::class,
            TestSuite\Sorted::class,
            TestSuite\Started::class,
        ];

        foreach ($defaultEvents as $eventClass) {
            $typeMap->addMapping(
                $eventClass . 'Subscriber',
                $eventClass
            );
        }
    }
}
