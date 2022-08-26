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
    private TypeMap $typeMap;
    private Emitter $emitter;
    private ?Emitter $suspended = null;
    private DeferredDispatcher $deferredDispatcher;
    private bool $sealed = false;

    public function __construct()
    {
        $this->typeMap = new TypeMap;
        $this->registerDefaultTypes($this->typeMap);

        $this->deferredDispatcher = new DeferredDispatcher(
            new DirectDispatcher($this->typeMap)
        );

        $this->emitter = new DispatchingEmitter(
            $this->deferredDispatcher,
            $this->createTelemetrySystem()
        );
    }

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public function registerSubscriber(Subscriber $subscriber): void
    {
        if ($this->sealed) {
            throw new EventFacadeIsSealedException;
        }

        $this->deferredDispatcher->registerSubscriber($subscriber);
    }

    /**
     * @throws EventFacadeIsSealedException
     */
    public function registerTracer(Tracer\Tracer $tracer): void
    {
        if ($this->sealed) {
            throw new EventFacadeIsSealedException;
        }

        $this->deferredDispatcher->registerTracer($tracer);
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function emitter(): Emitter
    {
        return $this->emitter;
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public static function initForIsolation(HRTime $offset): array
    {
        $eventFacade = new self;

        $dispatcher = new CollectingDispatcher;

        $eventFacade->emitter = new DispatchingEmitter(
            $dispatcher,
            new Telemetry\System(
                new Telemetry\SystemStopWatchWithOffset($offset),
                new Telemetry\SystemMemoryMeter
            )
        );

        return [$eventFacade, $dispatcher];
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function forward(EventCollection $events): void
    {
        if ($this->suspended !== null) {
            return;
        }

        $dispatcher = $this->deferredDispatcher;

        foreach ($events as $event) {
            $dispatcher->dispatch($event);
        }
    }

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function seal(): void
    {
        $this->deferredDispatcher->flush();

        $this->sealed = true;

        $this->emitter()->eventFacadeSealed();
    }

    private function createTelemetrySystem(): Telemetry\System
    {
        return new Telemetry\System(
            new Telemetry\SystemStopWatch,
            new Telemetry\SystemMemoryMeter
        );
    }

    private function registerDefaultTypes(TypeMap $typeMap): void
    {
        $defaultEvents = [
            Test\MarkedIncomplete::class,
            Test\AfterLastTestMethodCalled::class,
            Test\AfterLastTestMethodFinished::class,
            Test\AfterTestMethodCalled::class,
            Test\AfterTestMethodFinished::class,
            Test\AssertionMade::class,
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

            TestDouble\MockObjectCreatedForAbstractClass::class,
            TestDouble\MockObjectCreatedForTrait::class,
            TestDouble\MockObjectCreatedFromWsdl::class,
            TestDouble\MockObjectCreated::class,
            TestDouble\PartialMockObjectCreated::class,
            TestDouble\TestProxyCreated::class,
            TestDouble\TestStubCreated::class,

            TestRunner\BootstrapFinished::class,
            TestRunner\Configured::class,
            TestRunner\EventFacadeSealed::class,
            TestRunner\ExecutionFinished::class,
            TestRunner\ExecutionStarted::class,
            TestRunner\ExtensionLoaded::class,
            TestRunner\Finished::class,
            TestRunner\Started::class,
            TestRunner\DeprecationTriggered::class,
            TestRunner\WarningTriggered::class,

            TestSuite\Filtered::class,
            TestSuite\Finished::class,
            TestSuite\Loaded::class,
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
