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

use const PHP_VERSION;
use function array_map;
use function array_values;
use function explode;
use function get_class_methods;
use function version_compare;
use Exception;
use PHPUnit\Event\Code\TestCollection;
use PHPUnit\Event\Code\TestDoxBuilder;
use PHPUnit\Event\Code\ThrowableBuilder;
use PHPUnit\Event\Telemetry\Php81GarbageCollectorStatusProvider;
use PHPUnit\Event\Telemetry\Php83GarbageCollectorStatusProvider;
use PHPUnit\Event\TestData\TestDataCollection;
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
use PHPUnit\Event\TestSuite\TestSuiteWithName;
use PHPUnit\Framework;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\TestFixture;
use PHPUnit\TestFixture\RecordingSubscriber;
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
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
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
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->testRunnerFinished();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(TestRunner\Finished::class, $subscriber->lastRecordedEvent());
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
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->testRunnerBootstrapFinished($filename);

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
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->testRegisteredComparator($className);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\ComparatorRegistered::class, $event);

        $this->assertSame($className, $event->className());
    }

    public function testExtensionLoadedDispatchesExtensionLoadedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements TestRunner\ExtensionLoadedFromPharSubscriber
        {
            public function notify(TestRunner\ExtensionLoadedFromPhar $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            TestRunner\ExtensionLoadedFromPharSubscriber::class,
            TestRunner\ExtensionLoadedFromPhar::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->testRunnerLoadedExtensionFromPhar(
            'filename',
            'example-extension',
            '1.2.3',
        );

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(TestRunner\ExtensionLoadedFromPhar::class, $subscriber->lastRecordedEvent());
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
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $throwable = ThrowableBuilder::from(new Exception('error'));

        $emitter->testErrored(
            $test,
            $throwable,
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
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $throwable = ThrowableBuilder::from(new Exception('failure'));
        $failure   = null;

        $emitter->testFailed(
            $test,
            $throwable,
            $failure,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\Failed::class, $event);

        $this->assertSame($test, $event->test());
        $this->assertSame($throwable, $event->throwable());
        $this->assertFalse($event->hasComparisonFailure());
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
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
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
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->testPassed($test);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\Passed::class, $event);

        $this->assertSame($test, $event->test());
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
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $message = 'message';

        $emitter->testConsideredRisky($test, $message);

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Test\ConsideredRisky::class, $subscriber->lastRecordedEvent());

        $event = $subscriber->lastRecordedEvent();

        $this->assertSame($test, $event->test());
        $this->assertSame($message, $event->message());
    }

    public function testTestMarkedIncompleteDispatchesTestMarkedIncompleteEvent(): void
    {
        $test = $this->testValueObject();

        $subscriber = new class extends RecordingSubscriber implements Test\MarkedIncompleteSubscriber
        {
            public function notify(Test\MarkedIncomplete $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\MarkedIncompleteSubscriber::class,
            Test\MarkedIncomplete::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $throwable = ThrowableBuilder::from(new Exception('incomplete'));

        $emitter->testMarkedAsIncomplete(
            $test,
            $throwable,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\MarkedIncomplete::class, $event);

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
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $message = 'skipped';

        $emitter->testSkipped(
            $test,
            $message,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\Skipped::class, $event);

        $this->assertSame($test, $event->test());
        $this->assertSame($message, $event->message());
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
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
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
        $calledMethods = array_map(
            static fn (string $methodName): Code\ClassMethod => new Code\ClassMethod(
                self::class,
                $methodName,
            ),
            get_class_methods($this),
        );

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
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->afterTestMethodFinished(
            $testClassName,
            ...$calledMethods,
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
            __METHOD__,
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
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->afterTestMethodCalled(
            $testClassName,
            $calledMethod,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\AfterTestMethodCalled::class, $event);

        $this->assertSame($testClassName, $event->testClassName());
        $this->assertSame($calledMethod, $event->calledMethod());
    }

    public function testTestAfterTestMethodErroredDispatchesTestAfterTestMethodErroredEvent(): void
    {
        $testClassName = self::class;
        $calledMethod  = new Code\ClassMethod(...array_values(explode(
            '::',
            __METHOD__,
        )));

        $subscriber = new class extends RecordingSubscriber implements Test\AfterTestMethodErroredSubscriber
        {
            public function notify(Test\AfterTestMethodErrored $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\AfterTestMethodErroredSubscriber::class,
            Test\AfterTestMethodErrored::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $throwable = ThrowableBuilder::from(new Exception('error'));

        $emitter->afterTestMethodErrored(
            $testClassName,
            $calledMethod,
            $throwable,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\AfterTestMethodErrored::class, $event);

        $this->assertSame($testClassName, $event->testClassName());
        $this->assertSame($calledMethod, $event->calledMethod());
        $this->assertSame($throwable, $event->throwable());
    }

    public function testTestAfterLastTestMethodFinishedDispatchesTestAfterLastTestMethodFinishedEvent(): void
    {
        $testClassName = self::class;
        $calledMethods = array_map(
            static fn (string $methodName): Code\ClassMethod => new Code\ClassMethod(
                self::class,
                $methodName,
            ),
            get_class_methods($this),
        );

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
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->afterLastTestMethodFinished(
            $testClassName,
            ...$calledMethods,
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
            __METHOD__,
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
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->beforeFirstTestMethodCalled(
            $testClassName,
            $calledMethod,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\BeforeFirstTestMethodCalled::class, $event);

        $this->assertSame($testClassName, $event->testClassName());
        $this->assertSame($calledMethod, $event->calledMethod());
    }

    public function testTestAfterLastTestMethodErroredDispatchesTestAfterLastTestMethodErroredEvent(): void
    {
        $testClassName = self::class;
        $calledMethod  = new Code\ClassMethod(...array_values(explode(
            '::',
            __METHOD__,
        )));

        $subscriber = new class extends RecordingSubscriber implements Test\AfterLastTestMethodErroredSubscriber
        {
            public function notify(Test\AfterLastTestMethodErrored $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\AfterLastTestMethodErroredSubscriber::class,
            Test\AfterLastTestMethodErrored::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $throwable = ThrowableBuilder::from(new Exception('error'));

        $emitter->afterLastTestMethodErrored(
            $testClassName,
            $calledMethod,
            $throwable,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\AfterLastTestMethodErrored::class, $event);

        $this->assertSame($testClassName, $event->testClassName());
        $this->assertSame($calledMethod, $event->calledMethod());
        $this->assertSame($throwable, $event->throwable());
    }

    public function testTestBeforeFirstTestMethodFinishedDispatchesTestBeforeFirstTestMethodFinishedEvent(): void
    {
        $testClassName = self::class;
        $calledMethods = array_map(
            static fn (string $methodName): Code\ClassMethod => new Code\ClassMethod(
                self::class,
                $methodName,
            ),
            get_class_methods($this),
        );

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
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->beforeFirstTestMethodFinished(
            $testClassName,
            ...$calledMethods,
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
            __METHOD__,
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
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->beforeTestMethodCalled(
            $testClassName,
            $calledMethod,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\BeforeTestMethodCalled::class, $event);

        $this->assertSame($testClassName, $event->testClassName());
        $this->assertSame($calledMethod, $event->calledMethod());
    }

    public function testTestBeforeTestMethodErroredDispatchesTestBeforeTestMethodErroredEvent(): void
    {
        $testClassName = self::class;
        $calledMethod  = new Code\ClassMethod(...array_values(explode(
            '::',
            __METHOD__,
        )));

        $subscriber = new class extends RecordingSubscriber implements Test\BeforeTestMethodErroredSubscriber
        {
            public function notify(Test\BeforeTestMethodErrored $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\BeforeTestMethodErroredSubscriber::class,
            Test\BeforeTestMethodErrored::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $throwable = ThrowableBuilder::from(new Exception('error'));

        $emitter->beforeTestMethodErrored(
            $testClassName,
            $calledMethod,
            $throwable,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\BeforeTestMethodErrored::class, $event);

        $this->assertSame($testClassName, $event->testClassName());
        $this->assertSame($calledMethod, $event->calledMethod());
        $this->assertSame($throwable, $event->throwable());
    }

    public function testTestPreConditionCalledDispatchesTestPreConditionCalledEvent(): void
    {
        $testClassName = self::class;
        $calledMethod  = new Code\ClassMethod(...array_values(explode(
            '::',
            __METHOD__,
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
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->preConditionCalled(
            $testClassName,
            $calledMethod,
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
            __METHOD__,
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
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->postConditionCalled(
            $testClassName,
            $calledMethod,
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
        $calledMethods = array_map(
            static fn (string $methodName): Code\ClassMethod => new Code\ClassMethod(
                self::class,
                $methodName,
            ),
            get_class_methods($this),
        );

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
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->postConditionFinished(
            $testClassName,
            ...$calledMethods,
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
        $calledMethods = array_map(
            static fn (string $methodName): Code\ClassMethod => new Code\ClassMethod(
                self::class,
                $methodName,
            ),
            get_class_methods($this),
        );

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
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->beforeTestMethodFinished(
            $testClassName,
            ...$calledMethods,
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
        $calledMethods = array_map(
            static fn (string $methodName): Code\ClassMethod => new Code\ClassMethod(
                self::class,
                $methodName,
            ),
            get_class_methods($this),
        );

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
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->preConditionFinished(
            $testClassName,
            ...$calledMethods,
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
            __METHOD__,
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
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->afterLastTestMethodCalled(
            $testClassName,
            $calledMethod,
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

        $subscriber = new class extends RecordingSubscriber implements Test\MockObjectCreatedSubscriber
        {
            public function notify(Test\MockObjectCreated $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\MockObjectCreatedSubscriber::class,
            Test\MockObjectCreated::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->testCreatedMockObject($className);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\MockObjectCreated::class, $event);

        $this->assertSame($className, $event->className());
    }

    public function testTestMockObjectCreatedForTraitDispatchesTestDoubleMockObjectCreatedForTraitEvent(): void
    {
        $traitName = TestFixture\MockObject\ExampleTrait::class;

        $subscriber = new class extends RecordingSubscriber implements Test\MockObjectForTraitCreatedSubscriber
        {
            public function notify(Test\MockObjectForTraitCreated $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\MockObjectForTraitCreatedSubscriber::class,
            Test\MockObjectForTraitCreated::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->testCreatedMockObjectForTrait($traitName);

        $this->assertSame(1, $subscriber->recordedEventCount());
        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\MockObjectForTraitCreated::class, $event);

        $this->assertSame($traitName, $event->traitName());
    }

    public function testTestMockObjectCreatedForAbstractClassDispatchesTestDoubleMockObjectCreatedForAbstractClassEvent(): void
    {
        $className = stdClass::class;

        $subscriber = new class extends RecordingSubscriber implements Test\MockObjectForAbstractClassCreatedSubscriber
        {
            public function notify(Test\MockObjectForAbstractClassCreated $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\MockObjectForAbstractClassCreatedSubscriber::class,
            Test\MockObjectForAbstractClassCreated::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->testCreatedMockObjectForAbstractClass($className);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\MockObjectForAbstractClassCreated::class, $event);

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

        $subscriber = new class extends RecordingSubscriber implements Test\MockObjectFromWsdlCreatedSubscriber
        {
            public function notify(Test\MockObjectFromWsdlCreated $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\MockObjectFromWsdlCreatedSubscriber::class,
            Test\MockObjectFromWsdlCreated::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->testCreatedMockObjectFromWsdl(
            $wsdlFile,
            $originalClassName,
            $mockClassName,
            $methods,
            $callOriginalConstructor,
            $options,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\MockObjectFromWsdlCreated::class, $event);

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

        $subscriber = new class extends RecordingSubscriber implements Test\PartialMockObjectCreatedSubscriber
        {
            public function notify(Test\PartialMockObjectCreated $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\PartialMockObjectCreatedSubscriber::class,
            Test\PartialMockObjectCreated::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->testCreatedPartialMockObject(
            $className,
            ...$methodNames,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\PartialMockObjectCreated::class, $event);

        $this->assertSame($className, $event->className());
        $this->assertSame($methodNames, $event->methodNames());
    }

    public function testTestTestProxyCreatedDispatchesTestDoubleTestProxyCreatedEvent(): void
    {
        $className            = self::class;
        $constructorArguments = ['foo'];

        $subscriber = new class extends RecordingSubscriber implements Test\TestProxyCreatedSubscriber
        {
            public function notify(Test\TestProxyCreated $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\TestProxyCreatedSubscriber::class,
            Test\TestProxyCreated::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->testCreatedTestProxy(
            $className,
            $constructorArguments,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\TestProxyCreated::class, $event);

        $this->assertSame($className, $event->className());
        $this->assertSame("Array &0 [\n    0 => 'foo',\n]", $event->constructorArguments());
    }

    public function testTestTestStubCreatedDispatchesTestDoubleTestStubCreatedEvent(): void
    {
        $className = self::class;

        $subscriber = new class extends RecordingSubscriber implements Test\TestStubCreatedSubscriber
        {
            public function notify(Test\TestStubCreated $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\TestStubCreatedSubscriber::class,
            Test\TestStubCreated::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->testCreatedStub($className);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\TestStubCreated::class, $event);

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
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->testSuiteLoaded($this->testSuiteValueObject());

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
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->testSuiteFinished($this->testSuiteValueObject());

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(TestSuiteFinished::class, $event);

        $this->assertSame('Test Suite', $event->testSuite()->name());
        $this->assertSame(0, $event->testSuite()->count());
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
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->testSuiteSorted(
            $executionOrder,
            $executionOrderDefects,
            $resolveDependencies,
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
            $telemetrySystem,
        );

        $emitter->testSuiteStarted($this->testSuiteValueObject());

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(TestSuiteStarted::class, $event);

        $this->assertSame('Test Suite', $event->testSuite()->name());
        $this->assertSame(0, $event->testSuite()->count());
    }

    private function testSuiteValueObject(): TestSuiteWithName
    {
        return new TestSuiteWithName(
            'Test Suite',
            0,
            TestCollection::fromArray([]),
        );
    }

    private function dispatcherWithRegisteredSubscriber(string $subscriberInterface, string $eventClass, Subscriber $subscriber): DirectDispatcher
    {
        $typeMap = new TypeMap;

        $typeMap->addMapping(
            $subscriberInterface,
            $eventClass,
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
            $eventClassOne,
        );

        $typeMap->addMapping(
            $subscriberInterfaceTwo,
            $eventClassTwo,
        );

        $dispatcher = new DirectDispatcher($typeMap);

        $dispatcher->registerSubscriber($subscriberOne);
        $dispatcher->registerSubscriber($subscriberTwo);

        return $dispatcher;
    }

    private function telemetrySystem(): Telemetry\System
    {
        if (version_compare('8.3.0', PHP_VERSION, '>')) {
            $garbageCollectorStatusProvider = new Php81GarbageCollectorStatusProvider;
        } else {
            $garbageCollectorStatusProvider = new Php83GarbageCollectorStatusProvider;
        }

        return new Telemetry\System(
            new Telemetry\SystemStopWatch,
            new Telemetry\SystemMemoryMeter,
            $garbageCollectorStatusProvider,
        );
    }

    private function testValueObject(): Code\TestMethod
    {
        return new Code\TestMethod(
            'FooTest',
            'testBar',
            'FooTest.php',
            1,
            TestDoxBuilder::fromClassNameAndMethodName('Foo', 'bar'),
            MetadataCollection::fromArray([]),
            TestDataCollection::fromArray([]),
        );
    }
}
