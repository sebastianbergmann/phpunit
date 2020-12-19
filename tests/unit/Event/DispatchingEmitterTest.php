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
use SebastianBergmann\CodeCoverage;
use SebastianBergmann\GlobalState\Snapshot;
use stdClass;

/**
 * @covers \PHPUnit\Event\DispatchingEmitter
 */
final class DispatchingEmitterTest extends Framework\TestCase
{
    public function testTestRunnerStartedDispatchesTestRunnerStartedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements TestRunner\StartedSubscriber {
            public function notify(TestRunner\Started $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            TestRunner\StartedSubscriber::class,
            TestRunner\Started::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testRunnerStarted();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(TestRunner\Started::class, $subscriber->lastRecordedEvent());
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

    public function testTestErroredDispatchesTestErroredEvent(): void
    {
        $test = new Code\Test(...array_values(explode(
            '::',
            __METHOD__
        )));
        $message = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';

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

        $emitter->testErrored(
            $test,
            $message
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\Errored::class, $event);

        $this->assertSame($test, $event->test());
        $this->assertSame($message, $event->message());
    }

    public function testTestFailedDispatchesTestFailedEvent(): void
    {
        $test = new Code\Test(...array_values(explode(
            '::',
            __METHOD__
        )));
        $message = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';

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

        $emitter->testFailed(
            $test,
            $message
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\Failed::class, $event);

        $this->assertSame($test, $event->test());
        $this->assertSame($message, $event->message());
    }

    public function testTestFinishedDispatchesTestFinishedEvent(): void
    {
        $test = new Code\Test(...array_values(explode(
            '::',
            __METHOD__
        )));

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

        $emitter->testFinished($test);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\Finished::class, $event);

        $this->assertSame($test, $event->test());
    }

    public function testTestPassedDispatchesTestPassedEvent(): void
    {
        $test = new Code\Test(...array_values(explode(
            '::',
            __METHOD__
        )));

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

        $emitter->testPassed($test);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\Passed::class, $event);

        $this->assertSame($test, $event->test());
    }

    public function testTestPassedWithWarningDispatchesTestPassedWithWarningEvent(): void
    {
        $test = new Code\Test(...array_values(explode(
            '::',
            __METHOD__
        )));
        $message = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';

        $subscriber = new class extends RecordingSubscriber implements Test\PassedWithWarningSubscriber {
            public function notify(Test\PassedWithWarning $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\PassedWithWarningSubscriber::class,
            Test\PassedWithWarning::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testPassedWithWarning(
            $test,
            $message
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\PassedWithWarning::class, $event);

        $this->assertSame($test, $event->test());
        $this->assertSame($message, $event->message());
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
        $testMethod = new Code\ClassMethod(...array_values(explode(
            '::',
            __METHOD__
        )));
        $message = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';

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

        $emitter->testSkippedByDataProvider(
            $testMethod,
            $message
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\SkippedByDataProvider::class, $event);

        $this->assertSame($testMethod, $event->testMethod());
        $this->assertSame($message, $event->message());
    }

    public function testTestAbortedWithMessageDispatchesTestAbortedWithMessage(): void
    {
        $test = new Code\Test(...array_values(explode(
            '::',
            __METHOD__
        )));
        $message = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';

        $subscriber = new class extends RecordingSubscriber implements Test\AbortedWithMessageSubscriber {
            public function notify(Test\AbortedWithMessage $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\AbortedWithMessageSubscriber::class,
            Test\AbortedWithMessage::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testAbortedWithMessage(
            $test,
            $message
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\AbortedWithMessage::class, $event);

        $this->assertSame($test, $event->test());
        $this->assertSame($message, $event->message());
    }

    public function testTestSkippedDueToUnsatisfiedRequirementsDispatchesSkippedDueToUnsatisfiedRequirementsEvent(): void
    {
        $testMethod = new Code\ClassMethod(...array_values(explode(
            '::',
            __METHOD__
        )));
        $missingRequirements = [
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'Nunc felis nulla, euismod vel convallis ac, tincidunt quis ante.',
            'Maecenas aliquam eget nunc sed iaculis.',
        ];

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

        $emitter->testSkippedDueToUnsatisfiedRequirements(
            $testMethod,
            ...$missingRequirements
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\SkippedDueToUnsatisfiedRequirements::class, $event);

        $this->assertSame($testMethod, $event->testMethod());
        $this->assertSame($missingRequirements, $event->missingRequirements());
    }

    public function testTestSkippedMessageDispatchesTestSkippedWithMessageEvent(): void
    {
        $test = new Code\Test(...array_values(explode(
            '::',
            __METHOD__
        )));
        $message = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';

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

        $emitter->testSkippedWithMessage(
            $test,
            $message
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\SkippedWithMessage::class, $event);

        $this->assertSame($test, $event->test());
        $this->assertSame($message, $event->message());
    }

    public function testTestPreparedDispatchesTestPreparedEvent(): void
    {
        $test = new Code\Test(...array_values(explode(
            '::',
            __METHOD__
        )));

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

        $emitter->testPrepared($test);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\Prepared::class, $event);

        $this->assertSame($test, $event->test());
    }

    public function testTestAfterTestMethodFinishedDispatchesTestAfterTestMethodFinishedEvent(): void
    {
        $testClassName = self::class;
        $calledMethods = array_map(static function (string $methodName): Code\ClassMethod {
            return new Code\ClassMethod(
                self::class,
                $methodName
            );
        }, get_class_methods($this));

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

        $emitter->testAfterTestMethodFinished(
            $testClassName,
            ...$calledMethods
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\AfterTestMethodFinished::class, $event);

        $this->assertSame($testClassName, $event->testClassName());
        $this->assertSame($calledMethods, $event->calledMethods());
    }

    public function testTestAfterTestMethodCalledDispatchesTestAfterTestMethodCalledEvent(): void
    {
        $testClassName = self::class;
        $calledMethod  = new Code\ClassMethod(...array_values(explode(
            '::',
            __METHOD__
        )));

        $subscriber = new class extends RecordingSubscriber implements Test\AfterTestMethodCalledSubscriber {
            public function notify(Test\AfterTestMethodCalled $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\AfterTestMethodCalledSubscriber::class,
            Test\AfterTestMethodCalled::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testAfterTestMethodCalled(
            $testClassName,
            $calledMethod
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\AfterTestMethodCalled::class, $event);

        $this->assertSame($testClassName, $event->testClassName());
        $this->assertSame($calledMethod, $event->calledMethod());
    }

    public function testTestAfterLastTestMethodFinishedDispatchesTestAfterLastTestMethodFinishedEvent(): void
    {
        $testClassName = self::class;
        $calledMethods = array_map(static function (string $methodName): Code\ClassMethod {
            return new Code\ClassMethod(
                self::class,
                $methodName
            );
        }, get_class_methods($this));

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

        $emitter->testAfterLastTestMethodFinished(
            $testClassName,
            ...$calledMethods
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\AfterLastTestMethodFinished::class, $event);

        $this->assertSame($testClassName, $event->testClassName());
        $this->assertSame($calledMethods, $event->calledMethods());
    }

    public function testTestBeforeFirstTestMethodCalledDispatchesTestBeforeFirstTestMethodEvent(): void
    {
        $testClassName = self::class;
        $calledMethod  = new Code\ClassMethod(...array_values(explode(
            '::',
            __METHOD__
        )));

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

        $emitter->testBeforeFirstTestMethodCalled(
            $testClassName,
            $calledMethod
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\BeforeFirstTestMethodCalled::class, $event);

        $this->assertSame($testClassName, $event->testClassName());
        $this->assertSame($calledMethod, $event->calledMethod());
    }

    public function testTestBeforeFirstTestMethodFinishedDispatchesTestBeforeFirstTestMethodFinishedEvent(): void
    {
        $testClassName = self::class;
        $calledMethods = array_map(static function (string $methodName): Code\ClassMethod {
            return new Code\ClassMethod(
                self::class,
                $methodName
            );
        }, get_class_methods($this));

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

        $emitter->testBeforeFirstTestMethodFinished(
            $testClassName,
            ...$calledMethods
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\BeforeFirstTestMethodFinished::class, $event);

        $this->assertSame($testClassName, $event->testClassName());
        $this->assertSame($calledMethods, $event->calledMethods());
    }

    public function testTestBeforeTestMethodCalledDispatchesTestBeforeTestMethodEvent(): void
    {
        $testClassName = self::class;
        $calledMethod  = new Code\ClassMethod(...array_values(explode(
            '::',
            __METHOD__
        )));

        $subscriber = new class extends RecordingSubscriber implements Test\BeforeTestMethodCalledSubscriber {
            public function notify(Test\BeforeTestMethodCalled $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\BeforeTestMethodCalledSubscriber::class,
            Test\BeforeTestMethodCalled::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testBeforeTestMethodCalled(
            $testClassName,
            $calledMethod
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\BeforeTestMethodCalled::class, $event);

        $this->assertSame($testClassName, $event->testClassName());
        $this->assertSame($calledMethod, $event->calledMethod());
    }

    public function testTestPreConditionCalledDispatchesTestPreConditionCalledEvent(): void
    {
        $testClassName = self::class;
        $calledMethod  = new Code\ClassMethod(...array_values(explode(
            '::',
            __METHOD__
        )));

        $subscriber = new class extends RecordingSubscriber implements Test\PreConditionCalledSubscriber {
            public function notify(Test\PreConditionCalled $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\PreConditionCalledSubscriber::class,
            Test\PreConditionCalled::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testPreConditionCalled(
            $testClassName,
            $calledMethod
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\PreConditionCalled::class, $event);

        $this->assertSame($testClassName, $event->testClassName());
        $this->assertSame($calledMethod, $event->calledMethod());
    }

    public function testTestPostConditionCalledDispatchesTestPostConditionCalledEvent(): void
    {
        $testClassName = self::class;
        $calledMethod  = new Code\ClassMethod(...array_values(explode(
            '::',
            __METHOD__
        )));

        $subscriber = new class extends RecordingSubscriber implements Test\PostConditionCalledSubscriber {
            public function notify(Test\PostConditionCalled $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\PostConditionCalledSubscriber::class,
            Test\PostConditionCalled::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testPostConditionCalled(
            $testClassName,
            $calledMethod
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\PostConditionCalled::class, $event);

        $this->assertSame($testClassName, $event->testClassName());
        $this->assertSame($calledMethod, $event->calledMethod());
    }

    public function testTestPostConditionFinishedDispatchesTestPostConditionFinishedEvent(): void
    {
        $testClassName = self::class;
        $calledMethods = array_map(static function (string $methodName): Code\ClassMethod {
            return new Code\ClassMethod(
                self::class,
                $methodName
            );
        }, get_class_methods($this));

        $subscriber = new class extends RecordingSubscriber implements Test\PostConditionFinishedSubscriber {
            public function notify(Test\PostConditionFinished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\PostConditionFinishedSubscriber::class,
            Test\PostConditionFinished::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testPostConditionFinished(
            $testClassName,
            ...$calledMethods
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\PostConditionFinished::class, $event);

        $this->assertSame($testClassName, $event->testClassName());
        $this->assertSame($calledMethods, $event->calledMethods());
    }

    public function testTestBeforeTestMethodFinishedDispatchesTestBeforeTestMethodFinishedEvent(): void
    {
        $testClassName = self::class;
        $calledMethods = array_map(static function (string $methodName): Code\ClassMethod {
            return new Code\ClassMethod(
                self::class,
                $methodName
            );
        }, get_class_methods($this));

        $subscriber = new class extends RecordingSubscriber implements Test\BeforeTestMethodFinishedSubscriber {
            public function notify(Test\BeforeTestMethodFinished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\BeforeTestMethodFinishedSubscriber::class,
            Test\BeforeTestMethodFinished::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testBeforeTestMethodFinished(
            $testClassName,
            ...$calledMethods
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\BeforeTestMethodFinished::class, $event);

        $this->assertSame($testClassName, $event->testClassName());
        $this->assertSame($calledMethods, $event->calledMethods());
    }

    public function testTestPreConditionFinishedDispatchesTestPreConditionFinishedEvent(): void
    {
        $testClassName = self::class;
        $calledMethods = array_map(static function (string $methodName): Code\ClassMethod {
            return new Code\ClassMethod(
                self::class,
                $methodName
            );
        }, get_class_methods($this));

        $subscriber = new class extends RecordingSubscriber implements Test\PreConditionFinishedSubscriber {
            public function notify(Test\PreConditionFinished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            Test\PreConditionFinishedSubscriber::class,
            Test\PreConditionFinished::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testPreConditionFinished(
            $testClassName,
            ...$calledMethods
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\PreConditionFinished::class, $event);

        $this->assertSame($testClassName, $event->testClassName());
        $this->assertSame($calledMethods, $event->calledMethods());
    }

    public function testTestAfterLastTestMethodCalledDispatchesTestAfterLastTestMethodCalledEvent(): void
    {
        $testClassName = self::class;
        $calledMethod  = new Code\ClassMethod(...array_values(explode(
            '::',
            __METHOD__
        )));

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

        $emitter->testAfterLastTestMethodCalled(
            $testClassName,
            $calledMethod
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\AfterLastTestMethodCalled::class, $event);

        $this->assertSame($testClassName, $event->testClassName());
        $this->assertSame($calledMethod, $event->calledMethod());
    }

    public function testTestDoubleMockObjectCreatedDispatchesTestDoubleMockObjectCreatedEvent(): void
    {
        $className = self::class;

        $subscriber = new class extends RecordingSubscriber implements TestDouble\MockObjectCreatedSubscriber {
            public function notify(TestDouble\MockObjectCreated $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            TestDouble\MockObjectCreatedSubscriber::class,
            TestDouble\MockObjectCreated::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testDoubleMockObjectCreated($className);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(TestDouble\MockObjectCreated::class, $event);

        $this->assertSame($className, $event->className());
    }

    public function testTestDoubleMockObjectCreatedForTraitDispatchesTestDoubleMockObjectCreatedForTraitEvent(): void
    {
        $traitName = TestFixture\ExampleTrait::class;

        $subscriber = new class extends RecordingSubscriber implements TestDouble\MockObjectCreatedForTraitSubscriber {
            public function notify(TestDouble\MockObjectCreatedForTrait $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            TestDouble\MockObjectCreatedForTraitSubscriber::class,
            TestDouble\MockObjectCreatedForTrait::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testDoubleMockObjectCreatedForTrait($traitName);

        $this->assertSame(1, $subscriber->recordedEventCount());
        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(TestDouble\MockObjectCreatedForTrait::class, $event);

        $this->assertSame($traitName, $event->traitName());
    }

    public function testTestDoubleMockObjectCreatedForAbstractClassDispatchesTestDoubleMockObjectCreatedForAbstractClassEvent(): void
    {
        $className = stdClass::class;

        $subscriber = new class extends RecordingSubscriber implements TestDouble\MockObjectCreatedForAbstractClassSubscriber {
            public function notify(TestDouble\MockObjectCreatedForAbstractClass $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            TestDouble\MockObjectCreatedForAbstractClassSubscriber::class,
            TestDouble\MockObjectCreatedForAbstractClass::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testDoubleMockObjectCreatedForAbstractClass($className);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(TestDouble\MockObjectCreatedForAbstractClass::class, $event);

        $this->assertSame($className, $event->className());
    }

    public function testTestDoubleMockObjectCreatedFromWsdlDispatchesTestDoubleMockObjectCreatedFromWsdlEvent(): void
    {
        $wsdlFile          = __FILE__;
        $originalClassName = self::class;
        $mockClassName     = stdClass::class;
        $methods           = [
            'foo',
            'bar',
        ];
        $callOriginalConstructor = false;
        $options                 = [
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 9000,
        ];

        $subscriber = new class extends RecordingSubscriber implements TestDouble\MockObjectCreatedFromWsdlSubscriber {
            public function notify(TestDouble\MockObjectCreatedFromWsdl $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            TestDouble\MockObjectCreatedFromWsdlSubscriber::class,
            TestDouble\MockObjectCreatedFromWsdl::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testDoubleMockObjectCreatedFromWsdl(
            $wsdlFile,
            $originalClassName,
            $mockClassName,
            $methods,
            $callOriginalConstructor,
            $options
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(TestDouble\MockObjectCreatedFromWsdl::class, $event);

        $this->assertSame($wsdlFile, $event->wsdlFile());
        $this->assertSame($originalClassName, $event->originalClassName());
        $this->assertSame($mockClassName, $event->mockClassName());
        $this->assertSame($methods, $event->methods());
        $this->assertSame($callOriginalConstructor, $event->callOriginalConstructor());
        $this->assertSame($options, $event->options());
    }

    public function testTestDoublePartialMockObjectCreatedDispatchesTestDoublePartialMockObjectCreatedEvent(): void
    {
        $className   = self::class;
        $methodNames = [
            'foo',
            'bar',
            'baz',
        ];

        $subscriber = new class extends RecordingSubscriber implements TestDouble\PartialMockObjectCreatedSubscriber {
            public function notify(TestDouble\PartialMockObjectCreated $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            TestDouble\PartialMockObjectCreatedSubscriber::class,
            TestDouble\PartialMockObjectCreated::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testDoublePartialMockObjectCreated(
            $className,
            ...$methodNames
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(TestDouble\PartialMockObjectCreated::class, $event);

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

    public function testTestDoubleTestStubCreatedDispatchesTestDoubleTestStubCreatedEvent(): void
    {
        $className = self::class;

        $subscriber = new class extends RecordingSubscriber implements TestDouble\TestStubCreatedSubscriber {
            public function notify(TestDouble\TestStubCreated $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            TestDouble\TestStubCreatedSubscriber::class,
            TestDouble\TestStubCreated::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testDoubleTestStubCreated($className);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(TestDouble\TestStubCreated::class, $event);

        $this->assertSame($className, $event->className());
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
        $name         = 'foo';
        $result       = new Framework\TestResult();
        $codeCoverage = new CodeCoverage\CodeCoverage(
            $this->createMock(CodeCoverage\Driver\Driver::class),
            new CodeCoverage\Filter()
        );

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

        $emitter->testSuiteRunFinished(
            $name,
            $result,
            $codeCoverage
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(TestSuite\RunFinished::class, $event);

        $this->assertSame($name, $event->name());

        $mappedResult = (new TestResultMapper())->map($result);

        $this->assertEquals($mappedResult, $event->result());
        $this->assertSame($codeCoverage, $event->codeCoverage());
    }

    public function testTestSuiteSortedDispatchesTestSuiteSortedEvent(): void
    {
        $executionOrder        = 9001;
        $executionOrderDefects = 5;
        $resolveDependencies   = true;

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

        $emitter->testSuiteSorted(
            $executionOrder,
            $executionOrderDefects,
            $resolveDependencies
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(TestSuite\Sorted::class, $event);

        $this->assertSame($executionOrder, $event->executionOrder());
        $this->assertSame($executionOrderDefects, $event->executionOrderDefects());
        $this->assertSame($resolveDependencies, $event->resolveDependencies());
    }

    public function testTestSuiteStartedDispatchesTestSuiteStartedEvent(): void
    {
        $name = 'foo';

        $subscriber = new class extends RecordingSubscriber implements TestSuite\StartedSubscriber {
            public function notify(TestSuite\Started $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = self::createDispatcherWithRegisteredSubscriber(
            TestSuite\StartedSubscriber::class,
            TestSuite\Started::class,
            $subscriber
        );

        $telemetrySystem = self::createTelemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testSuiteStarted($name);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(TestSuite\Started::class, $event);

        $this->assertSame($name, $event->name());
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
