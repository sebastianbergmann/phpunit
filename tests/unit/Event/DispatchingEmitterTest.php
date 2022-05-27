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

use Exception;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\TestRunner\ExecutionStarted;
use PHPUnit\Event\TestRunner\ExecutionStartedSubscriber;
use PHPUnit\Event\TestSuite\Finished as TestSuiteFinished;
use PHPUnit\Event\TestSuite\FinishedSubscriber as TestSuiteFinishedSubscriber;
use PHPUnit\Event\TestSuite\Loaded as TestSuiteLoaded;
use PHPUnit\Event\TestSuite\LoadedSubscriber as TestSuiteLoadedSubscriber;
use PHPUnit\Event\TestSuite\Sorted as TestSuiteSorted;
use PHPUnit\Event\TestSuite\SortedSubscriber as TestSuiteSortedSubscriber;
use PHPUnit\Event\TestSuite\Started as TestSuiteStarted;
use PHPUnit\Event\TestSuite\StartedSubscriber as TestSuiteStartedSubscriber;
use PHPUnit\Event\TestSuite\TestSuite;
use PHPUnit\Framework;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\TestFixture;
use PHPUnit\TestFixture\RecordingSubscriber;
use SebastianBergmann\GlobalState\Snapshot;
use stdClass;

#[CoversClass(DispatchingEmitter::class)]
final class DispatchingEmitterTest extends Framework\TestCase
{
    public function testTestRunnerStartedDispatchesTestRunnerStartedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements TestRunner\StartedSubscriber
        {
            public function notify(TestRunner\Started $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            TestRunner\StartedSubscriber::class,
            TestRunner\Started::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testRunnerStarted();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(TestRunner\Started::class, $subscriber->lastRecordedEvent());
    }

    public function testTestRunnerFinishedDispatchesTestRunnerFinishedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements TestRunner\FinishedSubscriber
        {
            public function notify(TestRunner\Finished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            TestRunner\FinishedSubscriber::class,
            TestRunner\Finished::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testRunnerFinished();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(TestRunner\Finished::class, $subscriber->lastRecordedEvent());
    }

    public function testAssertionMadeDispatchesAssertionMadeEvent(): void
    {
        $value      = 'Hmm';
        $constraint = new Framework\Constraint\IsEqual('Ok');
        $message    = 'Well, that did not go as planned!';
        $hasFailed  = true;

        $subscriber = new class extends RecordingSubscriber implements Test\AssertionMadeSubscriber
        {
            public function notify(Test\AssertionMade $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\AssertionMadeSubscriber::class,
            Test\AssertionMade::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

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

        $this->assertInstanceOf(Test\AssertionMade::class, $event);

        $this->assertSame($value, $event->value());
        $this->assertSame($constraint, $event->constraint());
        $this->assertSame($message, $event->message());
        $this->assertSame($hasFailed, $event->hasFailed());
    }

    public function testBootstrapFinishedDispatchesBootstrapFinishedEvent(): void
    {
        $filename = __FILE__;

        $subscriber = new class extends RecordingSubscriber implements TestRunner\BootstrapFinishedSubscriber
        {
            public function notify(TestRunner\BootstrapFinished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            TestRunner\BootstrapFinishedSubscriber::class,
            TestRunner\BootstrapFinished::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->bootstrapFinished($filename);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(TestRunner\BootstrapFinished::class, $event);

        $this->assertSame($filename, $event->filename());
    }

    public function testComparatorRegisteredDispatchesComparatorRegisteredEvent(): void
    {
        $className = self::class;

        $subscriber = new class extends RecordingSubscriber implements Test\ComparatorRegisteredSubscriber
        {
            public function notify(Test\ComparatorRegistered $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\ComparatorRegisteredSubscriber::class,
            Test\ComparatorRegistered::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->comparatorRegistered($className);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\ComparatorRegistered::class, $event);

        $this->assertSame($className, $event->className());
    }

    public function testExtensionLoadedDispatchesExtensionLoadedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements TestRunner\ExtensionLoadedSubscriber
        {
            public function notify(TestRunner\ExtensionLoaded $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            TestRunner\ExtensionLoadedSubscriber::class,
            TestRunner\ExtensionLoaded::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->extensionLoaded(
            'example-extension',
            '1.2.3'
        );

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(TestRunner\ExtensionLoaded::class, $subscriber->lastRecordedEvent());
    }

    public function testGlobalStateCapturedDispatchesGlobalStateCapturedEvent(): void
    {
        $snapshot = new Snapshot;

        $subscriber = new class extends RecordingSubscriber implements GlobalState\CapturedSubscriber
        {
            public function notify(GlobalState\Captured $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            GlobalState\CapturedSubscriber::class,
            GlobalState\Captured::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

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
        $snapshotBefore = new Snapshot;
        $snapshotAfter  = new Snapshot;
        $diff           = 'Hmm, who would have thought?';

        $subscriber = new class extends RecordingSubscriber implements GlobalState\ModifiedSubscriber
        {
            public function notify(GlobalState\Modified $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            GlobalState\ModifiedSubscriber::class,
            GlobalState\Modified::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->globalStateModified(
            $snapshotBefore,
            $snapshotAfter,
            $diff
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(GlobalState\Modified::class, $event);

        $this->assertSame($snapshotBefore, $event->snapshotBefore());
        $this->assertSame($snapshotAfter, $event->snapshotAfter());
        $this->assertSame($diff, $event->diff());
    }

    public function testGlobalStateRestoredDispatchesGlobalStateRestoredEvent(): void
    {
        $snapshot = new Snapshot;

        $subscriber = new class extends RecordingSubscriber implements GlobalState\RestoredSubscriber
        {
            public function notify(GlobalState\Restored $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            GlobalState\RestoredSubscriber::class,
            GlobalState\Restored::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

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
        $test = $this->testValueObject();

        $subscriber = new class extends RecordingSubscriber implements Test\ErroredSubscriber
        {
            public function notify(Test\Errored $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\ErroredSubscriber::class,
            Test\Errored::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $throwable = Throwable::from(new Exception('error'));

        $emitter->testErrored(
            $test,
            $throwable
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\Errored::class, $event);

        $this->assertSame($test, $event->test());
        $this->assertSame($throwable, $event->throwable());
    }

    public function testTestFailedDispatchesTestFailedEvent(): void
    {
        $test = $this->testValueObject();

        $subscriber = new class extends RecordingSubscriber implements Test\FailedSubscriber
        {
            public function notify(Test\Failed $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\FailedSubscriber::class,
            Test\Failed::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $throwable = Throwable::from(new Exception('failure'));

        $emitter->testFailed(
            $test,
            $throwable
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\Failed::class, $event);

        $this->assertSame($test, $event->test());
        $this->assertSame($throwable, $event->throwable());
    }

    public function testTestFinishedDispatchesTestFinishedEvent(): void
    {
        $test = $this->testValueObject();

        $subscriber = new class extends RecordingSubscriber implements Test\FinishedSubscriber
        {
            public function notify(Test\Finished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\FinishedSubscriber::class,
            Test\Finished::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testFinished($test, 1);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\Finished::class, $event);

        $this->assertSame($test, $event->test());
        $this->assertSame(1, $event->numberOfAssertionsPerformed());
    }

    public function testTestPassedDispatchesTestPassedEvent(): void
    {
        $test = $this->testValueObject();

        $subscriber = new class extends RecordingSubscriber implements Test\PassedSubscriber
        {
            public function notify(Test\Passed $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\PassedSubscriber::class,
            Test\Passed::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

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
        $test = $this->testValueObject();

        $subscriber = new class extends RecordingSubscriber implements Test\PassedWithWarningSubscriber
        {
            public function notify(Test\PassedWithWarning $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\PassedWithWarningSubscriber::class,
            Test\PassedWithWarning::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $throwable = Throwable::from(new Exception('failure'));

        $emitter->testPassedWithWarning(
            $test,
            $throwable
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\PassedWithWarning::class, $event);

        $this->assertSame($test, $event->test());
        $this->assertSame($throwable, $event->throwable());
    }

    public function testTestConsideredRiskyDispatchesTestConsideredRiskyEvent(): void
    {
        $test = $this->testValueObject();

        $subscriber = new class extends RecordingSubscriber implements Test\ConsideredRiskySubscriber
        {
            public function notify(Test\ConsideredRisky $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\ConsideredRiskySubscriber::class,
            Test\ConsideredRisky::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $throwable = Throwable::from(new Exception('failure'));

        $emitter->testConsideredRisky(
            $test,
            $throwable
        );

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Test\ConsideredRisky::class, $subscriber->lastRecordedEvent());

        $event = $subscriber->lastRecordedEvent();

        $this->assertSame($test, $event->test());
        $this->assertSame($throwable, $event->throwable());
    }

    public function testTestAbortedDispatchesTestAbortedEvent(): void
    {
        $test = $this->testValueObject();

        $subscriber = new class extends RecordingSubscriber implements Test\AbortedSubscriber
        {
            public function notify(Test\Aborted $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\AbortedSubscriber::class,
            Test\Aborted::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $throwable = Throwable::from(new Exception('aborted'));

        $emitter->testAborted(
            $test,
            $throwable
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\Aborted::class, $event);

        $this->assertSame($test, $event->test());
        $this->assertSame($throwable, $event->throwable());
    }

    public function testTestSkippedDispatchesTestSkippedEvent(): void
    {
        $test = $this->testValueObject();

        $subscriber = new class extends RecordingSubscriber implements Test\SkippedSubscriber
        {
            public function notify(Test\Skipped $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\SkippedSubscriber::class,
            Test\Skipped::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $message = 'skipped';

        $emitter->testSkipped(
            $test,
            Throwable::from(new Exception),
            $message
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\Skipped::class, $event);

        $this->assertSame($test, $event->test());
        $this->assertSame($message, $event->message());
        $this->assertTrue($event->hasThrowable());
        $this->assertSame(Exception::class, $event->throwable()->className());
    }

    public function testTestPreparedDispatchesTestPreparedEvent(): void
    {
        $test = $this->testValueObject();

        $subscriber = new class extends RecordingSubscriber implements Test\PreparedSubscriber
        {
            public function notify(Test\Prepared $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\PreparedSubscriber::class,
            Test\Prepared::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

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
        $calledMethods = array_map(static function (string $methodName): Code\ClassMethod
        {
            return new Code\ClassMethod(
                self::class,
                $methodName
            );
        }, get_class_methods($this));

        $subscriber = new class extends RecordingSubscriber implements Test\AfterTestMethodFinishedSubscriber
        {
            public function notify(Test\AfterTestMethodFinished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\AfterTestMethodFinishedSubscriber::class,
            Test\AfterTestMethodFinished::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

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

        $subscriber = new class extends RecordingSubscriber implements Test\AfterTestMethodCalledSubscriber
        {
            public function notify(Test\AfterTestMethodCalled $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\AfterTestMethodCalledSubscriber::class,
            Test\AfterTestMethodCalled::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

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
        $calledMethods = array_map(static function (string $methodName): Code\ClassMethod
        {
            return new Code\ClassMethod(
                self::class,
                $methodName
            );
        }, get_class_methods($this));

        $subscriber = new class extends RecordingSubscriber implements Test\AfterLastTestMethodFinishedSubscriber
        {
            public function notify(Test\AfterLastTestMethodFinished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\AfterLastTestMethodFinishedSubscriber::class,
            Test\AfterLastTestMethodFinished::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

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

        $subscriber = new class extends RecordingSubscriber implements Test\BeforeFirstTestMethodCalledSubscriber
        {
            public function notify(Test\BeforeFirstTestMethodCalled $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\BeforeFirstTestMethodCalledSubscriber::class,
            Test\BeforeFirstTestMethodCalled::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

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
        $calledMethods = array_map(static function (string $methodName): Code\ClassMethod
        {
            return new Code\ClassMethod(
                self::class,
                $methodName
            );
        }, get_class_methods($this));

        $subscriber = new class extends RecordingSubscriber implements Test\BeforeFirstTestMethodFinishedSubscriber
        {
            public function notify(Test\BeforeFirstTestMethodFinished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\BeforeFirstTestMethodFinishedSubscriber::class,
            Test\BeforeFirstTestMethodFinished::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

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

        $subscriber = new class extends RecordingSubscriber implements Test\BeforeTestMethodCalledSubscriber
        {
            public function notify(Test\BeforeTestMethodCalled $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\BeforeTestMethodCalledSubscriber::class,
            Test\BeforeTestMethodCalled::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

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

        $subscriber = new class extends RecordingSubscriber implements Test\PreConditionCalledSubscriber
        {
            public function notify(Test\PreConditionCalled $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\PreConditionCalledSubscriber::class,
            Test\PreConditionCalled::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

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

        $subscriber = new class extends RecordingSubscriber implements Test\PostConditionCalledSubscriber
        {
            public function notify(Test\PostConditionCalled $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\PostConditionCalledSubscriber::class,
            Test\PostConditionCalled::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

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
        $calledMethods = array_map(static function (string $methodName): Code\ClassMethod
        {
            return new Code\ClassMethod(
                self::class,
                $methodName
            );
        }, get_class_methods($this));

        $subscriber = new class extends RecordingSubscriber implements Test\PostConditionFinishedSubscriber
        {
            public function notify(Test\PostConditionFinished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\PostConditionFinishedSubscriber::class,
            Test\PostConditionFinished::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

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
        $calledMethods = array_map(static function (string $methodName): Code\ClassMethod
        {
            return new Code\ClassMethod(
                self::class,
                $methodName
            );
        }, get_class_methods($this));

        $subscriber = new class extends RecordingSubscriber implements Test\BeforeTestMethodFinishedSubscriber
        {
            public function notify(Test\BeforeTestMethodFinished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\BeforeTestMethodFinishedSubscriber::class,
            Test\BeforeTestMethodFinished::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

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
        $calledMethods = array_map(static function (string $methodName): Code\ClassMethod
        {
            return new Code\ClassMethod(
                self::class,
                $methodName
            );
        }, get_class_methods($this));

        $subscriber = new class extends RecordingSubscriber implements Test\PreConditionFinishedSubscriber
        {
            public function notify(Test\PreConditionFinished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\PreConditionFinishedSubscriber::class,
            Test\PreConditionFinished::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

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

        $subscriber = new class extends RecordingSubscriber implements Test\AfterLastTestMethodCalledSubscriber
        {
            public function notify(Test\AfterLastTestMethodCalled $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\AfterLastTestMethodCalledSubscriber::class,
            Test\AfterLastTestMethodCalled::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

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

    public function testTestMockObjectCreatedDispatchesTestDoubleMockObjectCreatedEvent(): void
    {
        $className = self::class;

        $subscriber = new class extends RecordingSubscriber implements TestDouble\MockObjectCreatedSubscriber
        {
            public function notify(TestDouble\MockObjectCreated $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            TestDouble\MockObjectCreatedSubscriber::class,
            TestDouble\MockObjectCreated::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testMockObjectCreated($className);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(TestDouble\MockObjectCreated::class, $event);

        $this->assertSame($className, $event->className());
    }

    public function testTestMockObjectCreatedForTraitDispatchesTestDoubleMockObjectCreatedForTraitEvent(): void
    {
        $traitName = TestFixture\MockObject\ExampleTrait::class;

        $subscriber = new class extends RecordingSubscriber implements TestDouble\MockObjectCreatedForTraitSubscriber
        {
            public function notify(TestDouble\MockObjectCreatedForTrait $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            TestDouble\MockObjectCreatedForTraitSubscriber::class,
            TestDouble\MockObjectCreatedForTrait::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testMockObjectCreatedForTrait($traitName);

        $this->assertSame(1, $subscriber->recordedEventCount());
        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(TestDouble\MockObjectCreatedForTrait::class, $event);

        $this->assertSame($traitName, $event->traitName());
    }

    public function testTestMockObjectCreatedForAbstractClassDispatchesTestDoubleMockObjectCreatedForAbstractClassEvent(): void
    {
        $className = stdClass::class;

        $subscriber = new class extends RecordingSubscriber implements TestDouble\MockObjectCreatedForAbstractClassSubscriber
        {
            public function notify(TestDouble\MockObjectCreatedForAbstractClass $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            TestDouble\MockObjectCreatedForAbstractClassSubscriber::class,
            TestDouble\MockObjectCreatedForAbstractClass::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testMockObjectCreatedForAbstractClass($className);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(TestDouble\MockObjectCreatedForAbstractClass::class, $event);

        $this->assertSame($className, $event->className());
    }

    public function testTestMockObjectCreatedFromWsdlDispatchesTestDoubleMockObjectCreatedFromWsdlEvent(): void
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

        $subscriber = new class extends RecordingSubscriber implements TestDouble\MockObjectCreatedFromWsdlSubscriber
        {
            public function notify(TestDouble\MockObjectCreatedFromWsdl $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            TestDouble\MockObjectCreatedFromWsdlSubscriber::class,
            TestDouble\MockObjectCreatedFromWsdl::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testMockObjectCreatedFromWsdl(
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

    public function testTestPartialMockObjectCreatedDispatchesTestDoublePartialMockObjectCreatedEvent(): void
    {
        $className   = self::class;
        $methodNames = [
            'foo',
            'bar',
            'baz',
        ];

        $subscriber = new class extends RecordingSubscriber implements TestDouble\PartialMockObjectCreatedSubscriber
        {
            public function notify(TestDouble\PartialMockObjectCreated $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            TestDouble\PartialMockObjectCreatedSubscriber::class,
            TestDouble\PartialMockObjectCreated::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testPartialMockObjectCreated(
            $className,
            ...$methodNames
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(TestDouble\PartialMockObjectCreated::class, $event);

        $this->assertSame($className, $event->className());
        $this->assertSame($methodNames, $event->methodNames());
    }

    public function testTestTestProxyCreatedDispatchesTestDoubleTestProxyCreatedEvent(): void
    {
        $className            = self::class;
        $constructorArguments = [
            'foo',
            new stdClass,
            [
                'bar',
                'baz',
            ],
        ];

        $subscriber = new class extends RecordingSubscriber implements TestDouble\TestProxyCreatedSubscriber
        {
            public function notify(TestDouble\TestProxyCreated $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            TestDouble\TestProxyCreatedSubscriber::class,
            TestDouble\TestProxyCreated::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testTestProxyCreated(
            $className,
            $constructorArguments
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(TestDouble\TestProxyCreated::class, $event);

        $this->assertSame($className, $event->className());
        $this->assertSame($constructorArguments, $event->constructorArguments());
    }

    public function testTestTestStubCreatedDispatchesTestDoubleTestStubCreatedEvent(): void
    {
        $className = self::class;

        $subscriber = new class extends RecordingSubscriber implements TestDouble\TestStubCreatedSubscriber
        {
            public function notify(TestDouble\TestStubCreated $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            TestDouble\TestStubCreatedSubscriber::class,
            TestDouble\TestStubCreated::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testTestStubCreated($className);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(TestDouble\TestStubCreated::class, $event);

        $this->assertSame($className, $event->className());
    }

    public function testTestSuiteLoadedDispatchesTestSuiteLoadedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements TestSuiteLoadedSubscriber
        {
            public function notify(TestSuiteLoaded $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            TestSuiteLoadedSubscriber::class,
            TestSuiteLoaded::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $emitter->testSuiteLoaded(
            TestSuite::fromTestSuite($this->createMock(Framework\TestSuite::class))
        );

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(TestSuiteLoaded::class, $subscriber->lastRecordedEvent());
    }

    public function testTestSuiteFinishedDispatchesTestSuiteFinishedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements TestSuiteFinishedSubscriber
        {
            public function notify(TestSuiteFinished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            TestSuiteFinishedSubscriber::class,
            TestSuiteFinished::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $testSuite = $this->createStub(Framework\TestSuite::class);

        $testSuite->method('count')->willReturn(1);
        $testSuite->method('getName')->willReturn('foo');

        $emitter->testSuiteFinished(
            TestSuite::fromTestSuite($testSuite),
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(TestSuiteFinished::class, $event);

        $this->assertSame(1, $event->testSuite()->count());
        $this->assertSame('foo', $event->testSuite()->name());
    }

    public function testTestSuiteSortedDispatchesTestSuiteSortedEvent(): void
    {
        $executionOrder        = 9001;
        $executionOrderDefects = 5;
        $resolveDependencies   = true;

        $subscriber = new class extends RecordingSubscriber implements TestSuiteSortedSubscriber
        {
            public function notify(TestSuiteSorted $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            TestSuiteSortedSubscriber::class,
            TestSuiteSorted::class,
            $subscriber
        );

        $telemetrySystem = $this->telemetrySystem();

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

        $this->assertInstanceOf(TestSuiteSorted::class, $event);

        $this->assertSame($executionOrder, $event->executionOrder());
        $this->assertSame($executionOrderDefects, $event->executionOrderDefects());
        $this->assertSame($resolveDependencies, $event->resolveDependencies());
    }

    public function testTestSuiteStartedDispatchesTestSuiteStartedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements TestSuiteStartedSubscriber
        {
            public function notify(TestSuiteStarted $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscribers(
            TestSuiteStartedSubscriber::class,
            TestSuiteStarted::class,
            $subscriber,
            ExecutionStartedSubscriber::class,
            ExecutionStarted::class,
            new class extends RecordingSubscriber implements ExecutionStartedSubscriber
            {
                public function notify(ExecutionStarted $event): void
                {
                    $this->record($event);
                }
            },
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem
        );

        $testSuite = $this->createStub(Framework\TestSuite::class);

        $testSuite->method('count')->willReturn(1);
        $testSuite->method('getName')->willReturn('foo');

        $emitter->testSuiteStarted(TestSuite::fromTestSuite($testSuite));

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(TestSuiteStarted::class, $event);

        $this->assertSame(1, $event->testSuite()->count());
        $this->assertSame('foo', $event->testSuite()->name());
    }

    private function dispatcherWithRegisteredSubscriber(string $subscriberInterface, string $eventClass, Subscriber $subscriber): DirectDispatcher
    {
        $typeMap = new TypeMap;

        $typeMap->addMapping(
            $subscriberInterface,
            $eventClass
        );

        $dispatcher = new DirectDispatcher($typeMap);

        $dispatcher->registerSubscriber($subscriber);

        return $dispatcher;
    }

    private function dispatcherWithRegisteredSubscribers(string $subscriberInterfaceOne, string $eventClassOne, Subscriber $subscriberOne, string $subscriberInterfaceTwo, string $eventClassTwo, Subscriber $subscriberTwo): DirectDispatcher
    {
        $typeMap = new TypeMap;

        $typeMap->addMapping(
            $subscriberInterfaceOne,
            $eventClassOne
        );

        $typeMap->addMapping(
            $subscriberInterfaceTwo,
            $eventClassTwo
        );

        $dispatcher = new DirectDispatcher($typeMap);

        $dispatcher->registerSubscriber($subscriberOne);
        $dispatcher->registerSubscriber($subscriberTwo);

        return $dispatcher;
    }

    private function telemetrySystem(): Telemetry\System
    {
        return new Telemetry\System(
            new Telemetry\SystemStopWatch,
            new Telemetry\SystemMemoryMeter
        );
    }

    private function testValueObject(): Code\TestMethod
    {
        return new Code\TestMethod(
            self::class,
            'foo',
            '',
            0,
            MetadataCollection::fromArray([]),
            TestDataCollection::fromArray([]),
        );
    }
}
