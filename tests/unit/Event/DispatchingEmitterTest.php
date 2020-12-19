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

    public function testTestRunErroredDispatchesTestRunErroredEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\RunErroredSubscriber {
            public function notify(Test\RunErrored $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\RunErroredSubscriber::class,
            Test\RunErrored::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testRunErrored();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Test\RunErrored::class, $subscriber->lastRecordedEvent());
    }

    public function testTestRunFailedDispatchesTestRunFailedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\RunFailedSubscriber {
            public function notify(Test\RunFailed $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\RunFailedSubscriber::class,
            Test\RunFailed::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testRunFailed();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Test\RunFailed::class, $subscriber->lastRecordedEvent());
    }

    public function testTestRunFinishedDispatchesTestRunFinishedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\RunFinishedSubscriber {
            public function notify(Test\RunFinished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\RunFinishedSubscriber::class,
            Test\RunFinished::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testRunFinished();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Test\RunFinished::class, $subscriber->lastRecordedEvent());
    }

    public function testTestRunPassedDispatchesTestRunPassedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\RunPassedSubscriber {
            public function notify(Test\RunPassed $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\RunPassedSubscriber::class,
            Test\RunPassed::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testRunPassed();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Test\RunPassed::class, $subscriber->lastRecordedEvent());
    }

    public function testTestRunRiskyDispatchesTestRunRiskyEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\RunRiskySubscriber {
            public function notify(Test\RunRisky $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\RunRiskySubscriber::class,
            Test\RunRisky::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testRunRisky();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Test\RunRisky::class, $subscriber->lastRecordedEvent());
    }

    public function testTestRunSkippedByDataProviderDispatchesTestRunSkippedByDataProviderEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\RunSkippedByDataProviderSubscriber {
            public function notify(Test\RunSkippedByDataProvider $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\RunSkippedByDataProviderSubscriber::class,
            Test\RunSkippedByDataProvider::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testRunSkippedByDataProvider();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Test\RunSkippedByDataProvider::class, $subscriber->lastRecordedEvent());
    }

    public function testTestRunSkippedIncompleteDispatchesTestRunSkippedIncompleteEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\RunSkippedIncompleteSubscriber {
            public function notify(Test\RunSkippedIncomplete $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\RunSkippedIncompleteSubscriber::class,
            Test\RunSkippedIncomplete::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testRunSkippedIncomplete();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Test\RunSkippedIncomplete::class, $subscriber->lastRecordedEvent());
    }

    public function testTestRunSkippedWithFailedRequirementsDispatchesTestRunSkippedWithFailedRequirementsEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\RunSkippedWithFailedRequirementsSubscriber {
            public function notify(Test\RunSkippedWithFailedRequirements $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\RunSkippedWithFailedRequirementsSubscriber::class,
            Test\RunSkippedWithFailedRequirements::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testRunSkippedWithFailedRequirements();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Test\RunSkippedWithFailedRequirements::class, $subscriber->lastRecordedEvent());
    }

    public function testTestRunSkippedWithWarningDispatchesTestRunSkippedWithWarningEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\RunSkippedWithWarningSubscriber {
            public function notify(Test\RunSkippedWithWarning $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\RunSkippedWithWarningSubscriber::class,
            Test\RunSkippedWithWarning::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testRunSkippedWithWarning();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Test\RunSkippedWithWarning::class, $subscriber->lastRecordedEvent());
    }

    public function testTestRunStartedDispatchesTestRunStartedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\RunStartedSubscriber {
            public function notify(Test\RunStarted $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\RunStartedSubscriber::class,
            Test\RunStarted::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testRunStarted();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Test\RunStarted::class, $subscriber->lastRecordedEvent());
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

    public function testTestTearDownFinishedDispatchesTestTearDownFinishedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\TearDownFinishedSubscriber {
            public function notify(Test\TearDownFinished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\TearDownFinishedSubscriber::class,
            Test\TearDownFinished::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testTearDownFinished();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Test\TearDownFinished::class, $subscriber->lastRecordedEvent());
    }

    public function testTestCaseAfterClassFinishedDispatchesTestCaseAfterClassFinishedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements TestCase\AfterClassFinishedSubscriber {
            public function notify(TestCase\AfterClassFinished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            TestCase\AfterClassFinishedSubscriber::class,
            TestCase\AfterClassFinished::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testCaseAfterClassFinished();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(TestCase\AfterClassFinished::class, $subscriber->lastRecordedEvent());
    }

    public function testTestCaseBeforeClassCalledDispatchesTestCaseBeforeClassCalledEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements TestCase\BeforeClassCalledSubscriber {
            public function notify(TestCase\BeforeClassCalled $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            TestCase\BeforeClassCalledSubscriber::class,
            TestCase\BeforeClassCalled::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testCaseBeforeClassCalled();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(TestCase\BeforeClassCalled::class, $subscriber->lastRecordedEvent());
    }

    public function testTestCaseBeforeClassFinishedDispatchesTestCaseBeforeClassFinishedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements TestCase\BeforeClassFinishedSubscriber {
            public function notify(TestCase\BeforeClassFinished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            TestCase\BeforeClassFinishedSubscriber::class,
            TestCase\BeforeClassFinished::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testCaseBeforeClassFinished();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(TestCase\BeforeClassFinished::class, $subscriber->lastRecordedEvent());
    }

    public function testTestCaseSetUpBeforeClassFinishedDispatchesTestSetUpBeforeClassFinishedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements TestCase\SetUpBeforeClassFinishedSubscriber {
            public function notify(TestCase\SetUpBeforeClassFinished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            TestCase\SetUpBeforeClassFinishedSubscriber::class,
            TestCase\SetUpBeforeClassFinished::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testCaseSetUpBeforeClassFinished();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(TestCase\SetUpBeforeClassFinished::class, $subscriber->lastRecordedEvent());
    }

    public function testTestCaseSetUpFinishedDispatchesTestCaseSetUpFinishedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements TestCase\SetUpFinishedSubscriber {
            public function notify(TestCase\SetUpFinished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            TestCase\SetUpFinishedSubscriber::class,
            TestCase\SetUpFinished::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testCaseSetUpFinished();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(TestCase\SetUpFinished::class, $subscriber->lastRecordedEvent());
    }

    public function testTestCaseTearDownAfterClassFinishedDispatchesTestCaseTearDownAfterClassFinishedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements TestCase\TearDownAfterClassFinishedSubscriber {
            public function notify(TestCase\TearDownAfterClassFinished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            TestCase\TearDownAfterClassFinishedSubscriber::class,
            TestCase\TearDownAfterClassFinished::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testCaseTearDownAfterClassFinished();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(TestCase\TearDownAfterClassFinished::class, $subscriber->lastRecordedEvent());
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

    public function testTestSuiteBeforeClassFinishedDispatchesTestSuiteBeforeClassFinishedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements TestSuite\BeforeClassFinishedSubscriber {
            public function notify(TestSuite\BeforeClassFinished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            TestSuite\BeforeClassFinishedSubscriber::class,
            TestSuite\BeforeClassFinished::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testSuiteBeforeClassFinished();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(TestSuite\BeforeClassFinished::class, $subscriber->lastRecordedEvent());
    }

    public function testTestSuiteConfiguredDispatchesTestSuiteConfiguredEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements TestSuite\ConfiguredSubscriber {
            public function notify(TestSuite\Configured $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            TestSuite\ConfiguredSubscriber::class,
            TestSuite\Configured::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testSuiteConfigured();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(TestSuite\Configured::class, $subscriber->lastRecordedEvent());
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

        $emitter->testSuiteLoaded();

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
