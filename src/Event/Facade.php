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
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Facade
{
    private static ?TypeMap $typeMap                         = null;
    private static ?Emitter $emitter                         = null;
    private static ?Emitter $suspended                       = null;
    private static ?DeferringDispatcher $deferringDispatcher = null;
    private static bool $sealed                              = false;

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public static function registerSubscribers(Subscriber ...$subscribers): void
    {
        foreach ($subscribers as $subscriber) {
            self::registerSubscriber($subscriber);
        }
    }

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

    public static function emitter(): Emitter
    {
        if (self::$emitter === null) {
            self::$emitter = self::createDispatchingEmitter();
        }

        return self::$emitter;
    }

    /** @noinspection PhpUnused */
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

    public static function seal(): void
    {
        self::deferredDispatcher()->flush();

        self::$sealed = true;

        self::emitter()->testRunnerEventFacadeSealed();
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

    private static function deferredDispatcher(): DeferringDispatcher
    {
        if (self::$deferringDispatcher === null) {
            self::$deferringDispatcher = new DeferringDispatcher(
                new DirectDispatcher(self::typeMap())
            );
        }

        return self::$deferringDispatcher;
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
            Application\Started::class,
            Application\Finished::class,

            Test\MarkedIncomplete::class,
            Test\AfterLastTestMethodCalled::class,
            Test\AfterLastTestMethodFinished::class,
            Test\AfterTestMethodCalled::class,
            Test\AfterTestMethodFinished::class,
            Test\AssertionSucceeded::class,
            Test\AssertionFailed::class,
            Test\BeforeFirstTestMethodCalled::class,
            Test\BeforeFirstTestMethodErrored::class,
            Test\BeforeFirstTestMethodFinished::class,
            Test\BeforeTestMethodCalled::class,
            Test\BeforeTestMethodFinished::class,
            Test\ComparatorRegistered::class,
            Test\ConsideredRisky::class,
            Test\DeprecationTriggered::class,
            Test\Errored::class,
            Test\ErrorTriggered::class,
            Test\Failed::class,
            Test\Finished::class,
            Test\NoticeTriggered::class,
            Test\Passed::class,
            Test\PhpDeprecationTriggered::class,
            Test\PhpNoticeTriggered::class,
            Test\PhpunitDeprecationTriggered::class,
            Test\PhpunitErrorTriggered::class,
            Test\PhpunitWarningTriggered::class,
            Test\PhpWarningTriggered::class,
            Test\PostConditionCalled::class,
            Test\PostConditionFinished::class,
            Test\PreConditionCalled::class,
            Test\PreConditionFinished::class,
            Test\PreparationStarted::class,
            Test\Prepared::class,
            Test\Skipped::class,
            Test\WarningTriggered::class,

            Test\MockObjectCreated::class,
            Test\MockObjectForAbstractClassCreated::class,
            Test\MockObjectForIntersectionOfInterfacesCreated::class,
            Test\MockObjectForTraitCreated::class,
            Test\MockObjectFromWsdlCreated::class,
            Test\PartialMockObjectCreated::class,
            Test\TestProxyCreated::class,
            Test\TestStubCreated::class,
            Test\TestStubForIntersectionOfInterfacesCreated::class,

            TestRunner\BootstrapFinished::class,
            TestRunner\Configured::class,
            TestRunner\EventFacadeSealed::class,
            TestRunner\ExecutionFinished::class,
            TestRunner\ExecutionStarted::class,
            TestRunner\ExtensionLoadedFromPhar::class,
            TestRunner\ExtensionBootstrapped::class,
            TestRunner\Finished::class,
            TestRunner\Started::class,
            TestRunner\DeprecationTriggered::class,
            TestRunner\WarningTriggered::class,

            TestSuite\Filtered::class,
            TestSuite\Finished::class,
            TestSuite\Loaded::class,
            TestSuite\Skipped::class,
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
