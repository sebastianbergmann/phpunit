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

use PHPUnit\Framework;
use PHPUnit\TestFixture;
use RecordingSubscriber;
use SebastianBergmann\GlobalState\Snapshot;
use stdClass;

/**
 * @covers \PHPUnit\Event\DispatchingEmitter
 */
final class DispatchingEmitterTest extends Framework\TestCase
{
    public function testApplicationConfiguredDispatchesApplicationConfiguredEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Application\ConfiguredSubscriber {
            public function notify(Application\Configured $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Application\ConfiguredSubscriber::class,
            Application\Configured::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->applicationConfigured();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Application\Configured::class, $subscriber->lastRecordedEvent());
    }

    public function testApplicationStartedDispatchesApplicationStartedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Application\StartedSubscriber {
            public function notify(Application\Started $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Application\StartedSubscriber::class,
            Application\Started::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->applicationStarted();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Application\Started::class, $subscriber->lastRecordedEvent());
    }

    public function testAssertionMadeDispatchesAssertionMadeEvent(): void
    {
        $value      = 'Hmm';
        $constraint = new Framework\Constraint\IsEqual('Ok');
        $message    = 'Well, that did not go as planned!';
        $hasFailed  = true;

        $subscriber = new class extends RecordingSubscriber implements Assertion\MadeSubscriber {
            public function notify(Assertion\Made $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Assertion\MadeSubscriber::class,
            Assertion\Made::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->assertionMade(
            $value,
            $constraint,
            $message,
            $hasFailed
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Assertion\Made::class, $event);

        $this->assertSame($value, $event->value());
        $this->assertSame($constraint, $event->constraint());
        $this->assertSame($message, $event->message());
        $this->assertSame($hasFailed, $event->hasFailed());
    }

    public function testBootstrapFinishedDispatchesBootstrapFinishedEvent(): void
    {
        $filename = __FILE__;

        $subscriber = new class extends RecordingSubscriber implements Bootstrap\FinishedSubscriber {
            public function notify(Bootstrap\Finished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Bootstrap\FinishedSubscriber::class,
            Bootstrap\Finished::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->bootstrapFinished($filename);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Bootstrap\Finished::class, $event);

        $this->assertSame($filename, $event->filename());
    }

    public function testComparatorRegisteredDispatchesComparatorRegisteredEvent(): void
    {
        $className = self::class;

        $subscriber = new class extends RecordingSubscriber implements Comparator\RegisteredSubscriber {
            public function notify(Comparator\Registered $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Comparator\RegisteredSubscriber::class,
            Comparator\Registered::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->comparatorRegistered($className);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Comparator\Registered::class, $event);

        $this->assertSame($className, $event->className());
    }

    public function testExtensionLoadedDispatchesExtensionLoadedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Extension\LoadedSubscriber {
            public function notify(Extension\Loaded $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Extension\LoadedSubscriber::class,
            Extension\Loaded::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->extensionLoaded(
            'example-extension',
            '1.2.3'
        );

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Extension\Loaded::class, $subscriber->lastRecordedEvent());
    }

    public function testGlobalStateCapturedDispatchesGlobalStateCapturedEvent(): void
    {
        $snapshot = new Snapshot();

        $subscriber = new class extends RecordingSubscriber implements GlobalState\CapturedSubscriber {
            public function notify(GlobalState\Captured $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            GlobalState\CapturedSubscriber::class,
            GlobalState\Captured::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->globalStateCaptured($snapshot);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(GlobalState\Captured::class, $event);

        $this->assertSame($snapshot, $event->snapshot());
    }

    public function testGlobalStateModifiedDispatchesGlobalStateModifiedEvent(): void
    {
        $snapshotBefore = new Snapshot();
        $snapshotAfter  = new Snapshot();
        $message        = 'Hmm, who would have thought?';

        $subscriber = new class extends RecordingSubscriber implements GlobalState\ModifiedSubscriber {
            public function notify(GlobalState\Modified $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            GlobalState\ModifiedSubscriber::class,
            GlobalState\Modified::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->globalStateModified(
            $snapshotBefore,
            $snapshotAfter,
            $message
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(GlobalState\Modified::class, $event);

        $this->assertSame($snapshotBefore, $event->snapshotBefore());
        $this->assertSame($snapshotAfter, $event->snapshotAfter());
        $this->assertSame($message, $event->message());
    }

    public function testGlobalStateRestoredDispatchesGlobalStateRestoredEvent(): void
    {
        $snapshot = new Snapshot();

        $subscriber = new class extends RecordingSubscriber implements GlobalState\RestoredSubscriber {
            public function notify(GlobalState\Restored $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            GlobalState\RestoredSubscriber::class,
            GlobalState\Restored::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->globalStateRestored($snapshot);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(GlobalState\Restored::class, $event);

        $this->assertSame($snapshot, $event->snapshot());
    }

    public function testTestRunConfiguredDispatchesTestRunConfiguredEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\RunConfiguredSubscriber {
            public function notify(Test\RunConfigured $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\RunConfiguredSubscriber::class,
            Test\RunConfigured::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testRunConfigured();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Test\RunConfigured::class, $subscriber->lastRecordedEvent());
    }

    public function testTestErroredDispatchesTestErroredEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\ErroredSubscriber {
            public function notify(Test\Errored $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\ErroredSubscriber::class,
            Test\Errored::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testErrored();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Test\Errored::class, $subscriber->lastRecordedEvent());
    }

    public function testTestFailedDispatchesTestFailedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\FailedSubscriber {
            public function notify(Test\Failed $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\FailedSubscriber::class,
            Test\Failed::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testFailed();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Test\Failed::class, $subscriber->lastRecordedEvent());
    }

    public function testTestFinishedDispatchesTestFinishedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\FinishedSubscriber {
            public function notify(Test\Finished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\FinishedSubscriber::class,
            Test\Finished::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testFinished();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Test\Finished::class, $subscriber->lastRecordedEvent());
    }

    public function testTestPassedDispatchesTestPassedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\PassedSubscriber {
            public function notify(Test\Passed $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\PassedSubscriber::class,
            Test\Passed::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testPassed();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Test\Passed::class, $subscriber->lastRecordedEvent());
    }

    public function testTestPassedButRiskyDispatchesTestPassedButRiskyEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\PassedButRiskySubscriber {
            public function notify(Test\PassedButRisky $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\PassedButRiskySubscriber::class,
            Test\PassedButRisky::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testPassedButRisky();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Test\PassedButRisky::class, $subscriber->lastRecordedEvent());
    }

    public function testTestSkippedByDataProviderDispatchesTestSkippedByDataProviderEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\SkippedByDataProviderSubscriber {
            public function notify(Test\SkippedByDataProvider $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\SkippedByDataProviderSubscriber::class,
            Test\SkippedByDataProvider::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testSkippedByDataProvider();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Test\SkippedByDataProvider::class, $subscriber->lastRecordedEvent());
    }

    public function testTestSkippedIncompleteDispatchesTestSkippedIncompleteEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\SkippedIncompleteSubscriber {
            public function notify(Test\SkippedIncomplete $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\SkippedIncompleteSubscriber::class,
            Test\SkippedIncomplete::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testSkippedIncomplete();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Test\SkippedIncomplete::class, $subscriber->lastRecordedEvent());
    }

    public function testTestSkippedDueToUnsatisfiedRequirementsDispatchesSkippedDueToUnsatisfiedRequirementsEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\SkippedDueToUnsatisfiedRequirementsSubscriber {
            public function notify(Test\SkippedDueToUnsatisfiedRequirements $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\SkippedDueToUnsatisfiedRequirementsSubscriber::class,
            Test\SkippedDueToUnsatisfiedRequirements::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testSkippedDueToUnsatisfiedRequirements();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Test\SkippedDueToUnsatisfiedRequirements::class, $subscriber->lastRecordedEvent());
    }

    public function testTestRunSkippedWithWarningDispatchesTestRunSkippedWithWarningEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\SkippedWithMessageSubscriber {
            public function notify(Test\SkippedWithMessage $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\SkippedWithMessageSubscriber::class,
            Test\SkippedWithMessage::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testSkippedWithMessage();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Test\SkippedWithMessage::class, $subscriber->lastRecordedEvent());
    }

    public function testTestPreparedDispatchesTestPreparedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\PreparedSubscriber {
            public function notify(Test\Prepared $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\PreparedSubscriber::class,
            Test\Prepared::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testPrepared();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Test\Prepared::class, $subscriber->lastRecordedEvent());
    }

    public function testTestSetUpFinishedDispatchesTestSetUpFinishedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\SetUpFinishedSubscriber {
            public function notify(Test\SetUpFinished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\SetUpFinishedSubscriber::class,
            Test\SetUpFinished::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testSetUpFinished();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Test\SetUpFinished::class, $subscriber->lastRecordedEvent());
    }

    public function testTestAfterTestMethodFinishedDispatchesTestAfterTestMethodFinishedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\AfterTestMethodFinishedSubscriber {
            public function notify(Test\AfterTestMethodFinished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\AfterTestMethodFinishedSubscriber::class,
            Test\AfterTestMethodFinished::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testAfterTestMethodFinished();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Test\AfterTestMethodFinished::class, $subscriber->lastRecordedEvent());
    }

    public function testTestAfterLastTestMethodFinishedDispatchesTestAfterLastTestMethodEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\AfterLastTestMethodFinishedSubscriber {
            public function notify(Test\AfterLastTestMethodFinished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\AfterLastTestMethodFinishedSubscriber::class,
            Test\AfterLastTestMethodFinished::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testAfterLastTestMethodFinished();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Test\AfterLastTestMethodFinished::class, $subscriber->lastRecordedEvent());
    }

    public function testTestBeforeFirstTestMethodCalledDispatchesTestBeforeFirstTestMethodEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\BeforeFirstTestMethodCalledSubscriber {
            public function notify(Test\BeforeFirstTestMethodCalled $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\BeforeFirstTestMethodCalledSubscriber::class,
            Test\BeforeFirstTestMethodCalled::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testBeforeFirstTestMethodCalled();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Test\BeforeFirstTestMethodCalled::class, $subscriber->lastRecordedEvent());
    }

    public function testTestBeforeFirstTestMethodFinishedDispatchesTestBeforeFirstTestMethodFinishedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\BeforeFirstTestMethodFinishedSubscriber {
            public function notify(Test\BeforeFirstTestMethodFinished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\BeforeFirstTestMethodFinishedSubscriber::class,
            Test\BeforeFirstTestMethodFinished::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testBeforeFirstTestMethodFinished();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Test\BeforeFirstTestMethodFinished::class, $subscriber->lastRecordedEvent());
    }

    public function testAfterLastTestMethodCalledDispatchesAfterLastTestMethodCalledEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\AfterLastTestMethodCalledSubscriber {
            public function notify(Test\AfterLastTestMethodCalled $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\AfterLastTestMethodCalledSubscriber::class,
            Test\AfterLastTestMethodCalled::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testAfterLastTestMethodCalled();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Test\AfterLastTestMethodCalled::class, $subscriber->lastRecordedEvent());
    }

    public function testTestDoubleMockCreatedDispatchesTestDoubleMockCreatedEvent(): void
    {
        $className = self::class;

        $subscriber = new class extends RecordingSubscriber implements TestDouble\MockCreatedSubscriber {
            public function notify(TestDouble\MockCreated $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            TestDouble\MockCreatedSubscriber::class,
            TestDouble\MockCreated::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testDoubleMockCreated($className);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(TestDouble\MockCreated::class, $event);

        $this->assertSame($className, $event->className());
    }

    public function testTestDoubleMockForTraitCreatedDispatchesTestDoubleMockForTraitCreatedEvent(): void
    {
        $traitName = TestFixture\ExampleTrait::class;

        $subscriber = new class extends RecordingSubscriber implements TestDouble\MockForTraitCreatedSubscriber {
            public function notify(TestDouble\MockForTraitCreated $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            TestDouble\MockForTraitCreatedSubscriber::class,
            TestDouble\MockForTraitCreated::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testDoubleMockForTraitCreated($traitName);

        $this->assertSame(1, $subscriber->recordedEventCount());
        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(TestDouble\MockForTraitCreated::class, $event);

        $this->assertSame($traitName, $event->traitName());
    }

    public function testTestDoublePartialMockCreatedDispatchesTestDoublePartialMockCreatedEvent(): void
    {
        $className   = self::class;
        $methodNames = [
            'foo',
            'bar',
            'baz',
        ];

        $subscriber = new class extends RecordingSubscriber implements TestDouble\PartialMockCreatedSubscriber {
            public function notify(TestDouble\PartialMockCreated $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            TestDouble\PartialMockCreatedSubscriber::class,
            TestDouble\PartialMockCreated::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testDoublePartialMockCreated(
            $className,
            ...$methodNames
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(TestDouble\PartialMockCreated::class, $event);

        $this->assertSame($className, $event->className());
        $this->assertSame($methodNames, $event->methodNames());
    }

    public function testTestDoubleTestProxyCreatedDispatchesTestDoubleTestProxyCreatedEvent(): void
    {
        $className            = self::class;
        $constructorArguments = [
            'foo',
            new stdClass(),
            [
                'bar',
                'baz',
            ],
        ];

        $subscriber = new class extends RecordingSubscriber implements TestDouble\TestProxyCreatedSubscriber {
            public function notify(TestDouble\TestProxyCreated $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            TestDouble\TestProxyCreatedSubscriber::class,
            TestDouble\TestProxyCreated::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testDoubleTestProxyCreated(
            $className,
            $constructorArguments
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(TestDouble\TestProxyCreated::class, $event);

        $this->assertSame($className, $event->className());
        $this->assertSame($constructorArguments, $event->constructorArguments());
    }

    public function testTestSuiteAfterClassFinishedDispatchesTestSuiteAfterClassFinishedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements TestSuite\AfterClassFinishedSubscriber {
            public function notify(TestSuite\AfterClassFinished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            TestSuite\AfterClassFinishedSubscriber::class,
            TestSuite\AfterClassFinished::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testSuiteAfterClassFinished();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(TestSuite\AfterClassFinished::class, $subscriber->lastRecordedEvent());
    }

    public function testTestSuiteLoadedDispatchesTestSuiteLoadedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements TestSuite\LoadedSubscriber {
            public function notify(TestSuite\Loaded $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            TestSuite\LoadedSubscriber::class,
            TestSuite\Loaded::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testSuiteLoaded($this->createMock(Framework\TestSuite::class));

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(TestSuite\Loaded::class, $subscriber->lastRecordedEvent());
    }

    public function testTestSuiteRunFinishedDispatchesTestSuiteRunFinishedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements TestSuite\RunFinishedSubscriber {
            public function notify(TestSuite\RunFinished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            TestSuite\RunFinishedSubscriber::class,
            TestSuite\RunFinished::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testSuiteRunFinished();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(TestSuite\RunFinished::class, $subscriber->lastRecordedEvent());
    }

    public function testTestSuiteRunStartedDispatchesTestSuiteRunStartedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements TestSuite\RunStartedSubscriber {
            public function notify(TestSuite\RunStarted $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            TestSuite\RunStartedSubscriber::class,
            TestSuite\RunStarted::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testSuiteRunStarted();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(TestSuite\RunStarted::class, $subscriber->lastRecordedEvent());
    }

    public function testTestSuiteSortedDispatchesTestSuiteSortedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements TestSuite\SortedSubscriber {
            public function notify(TestSuite\Sorted $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            TestSuite\SortedSubscriber::class,
            TestSuite\Sorted::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testSuiteSorted();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(TestSuite\Sorted::class, $subscriber->lastRecordedEvent());
    }

    private static function createDispatcherWithRegisteredSubscriber(string $subscriberInterface, string $eventClass, Subscriber $subscriber): Dispatcher
    {
        $typeMap = new TypeMap();

        $typeMap->addMapping(
            $subscriberInterface,
            $eventClass
        );

        $dispatcher = new Dispatcher($typeMap);

        $dispatcher->register($subscriber);

        return $dispatcher;
    }

    private static function createTelemetrySystem(): Telemetry\System
    {
        return new Telemetry\System(
            new Telemetry\SystemStopWatch(),
            new Telemetry\SystemMemoryMeter()
        );
    }
}
