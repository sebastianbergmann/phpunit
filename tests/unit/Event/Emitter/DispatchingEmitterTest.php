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
use function version_compare;
use Exception;
use PHPUnit\Event\Code\ClassMethod;
use PHPUnit\Event\Code\IssueTrigger\IssueTrigger;
use PHPUnit\Event\Code\TestCollection;
use PHPUnit\Event\Code\TestDoxBuilder;
use PHPUnit\Event\Code\ThrowableBuilder;
use PHPUnit\Event\Telemetry\Php81GarbageCollectorStatusProvider;
use PHPUnit\Event\Telemetry\Php83GarbageCollectorStatusProvider;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Event\TestRunner\ChildProcessFinished;
use PHPUnit\Event\TestRunner\ChildProcessFinishedSubscriber;
use PHPUnit\Event\TestRunner\ChildProcessStarted;
use PHPUnit\Event\TestRunner\ChildProcessStartedSubscriber;
use PHPUnit\Event\TestRunner\DeprecationTriggered as TestRunnerDeprecationTriggered;
use PHPUnit\Event\TestRunner\DeprecationTriggeredSubscriber as TestRunnerDeprecationTriggeredSubscriber;
use PHPUnit\Event\TestRunner\ExecutionAborted;
use PHPUnit\Event\TestRunner\ExecutionAbortedSubscriber;
use PHPUnit\Event\TestRunner\ExecutionFinished;
use PHPUnit\Event\TestRunner\ExecutionFinishedSubscriber;
use PHPUnit\Event\TestRunner\ExecutionStarted;
use PHPUnit\Event\TestRunner\ExecutionStartedSubscriber;
use PHPUnit\Event\TestRunner\GarbageCollectionDisabled;
use PHPUnit\Event\TestRunner\GarbageCollectionDisabledSubscriber;
use PHPUnit\Event\TestRunner\GarbageCollectionEnabled;
use PHPUnit\Event\TestRunner\GarbageCollectionEnabledSubscriber;
use PHPUnit\Event\TestRunner\GarbageCollectionTriggered;
use PHPUnit\Event\TestRunner\GarbageCollectionTriggeredSubscriber;
use PHPUnit\Event\TestRunner\WarningTriggered as TestRunnerWarningTriggered;
use PHPUnit\Event\TestRunner\WarningTriggeredSubscriber as TestRunnerWarningTriggeredSubscriber;
use PHPUnit\Event\TestSuite\Filtered as TestSuiteFiltered;
use PHPUnit\Event\TestSuite\FilteredSubscriber as TestSuiteFilteredSubscriber;
use PHPUnit\Event\TestSuite\Finished as TestSuiteFinished;
use PHPUnit\Event\TestSuite\FinishedSubscriber as TestSuiteFinishedSubscriber;
use PHPUnit\Event\TestSuite\Loaded as TestSuiteLoaded;
use PHPUnit\Event\TestSuite\LoadedSubscriber as TestSuiteLoadedSubscriber;
use PHPUnit\Event\TestSuite\Skipped as TestSuiteSkipped;
use PHPUnit\Event\TestSuite\SkippedSubscriber as TestSuiteSkippedSubscriber;
use PHPUnit\Event\TestSuite\Sorted as TestSuiteSorted;
use PHPUnit\Event\TestSuite\SortedSubscriber as TestSuiteSortedSubscriber;
use PHPUnit\Event\TestSuite\Started as TestSuiteStarted;
use PHPUnit\Event\TestSuite\StartedSubscriber as TestSuiteStartedSubscriber;
use PHPUnit\Event\TestSuite\TestSuiteWithName;
use PHPUnit\Framework;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\TestFixture\RecordingSubscriber;
use PHPUnit\TextUI\CliArguments\Builder;
use PHPUnit\TextUI\Configuration\Merger;
use PHPUnit\TextUI\XmlConfiguration\DefaultConfiguration;

#[CoversClass(DispatchingEmitter::class)]
#[Small]
final class DispatchingEmitterTest extends Framework\TestCase
{
    #[TestDox('applicationStarted() emits Application\Started event')]
    public function testApplicationStartedEmitsApplicationStartedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Application\StartedSubscriber
        {
            public function notify(Application\Started $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Application\StartedSubscriber::class,
            Application\Started::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->applicationStarted();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Application\Started::class, $subscriber->lastRecordedEvent());
    }

    #[TestDox('testRunnerStarted() emits TestRunner\Started event')]
    public function testTestRunnerStartedEmitsTestRunnerStartedEvent(): void
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

    #[TestDox('testRunnerConfigured() emits TestRunner\Configured event')]
    public function testTestRunnerConfiguredEmitsTestRunnerConfiguredEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements TestRunner\ConfiguredSubscriber
        {
            public function notify(TestRunner\Configured $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            TestRunner\ConfiguredSubscriber::class,
            TestRunner\Configured::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $configuration = (new Merger)->merge(
            (new Builder)->fromParameters([]),
            DefaultConfiguration::create(),
        );

        $emitter->testRunnerConfigured($configuration);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(TestRunner\Configured::class, $event);
        $this->assertSame($configuration, $event->configuration());
    }

    #[TestDox('bootstrapFinished() emits TestRunner\BootstrapFinished event')]
    public function testBootstrapFinishedEmitsBootstrapFinishedEvent(): void
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

    #[TestDox('testRunnerLoadedExtensionFromPhar() emits TestRunner\ExtensionLoadedFromPhar event')]
    public function testTestRunnerLoadedExtensionFromPharEmitsExtensionLoadedFromPharEvent(): void
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

    #[TestDox('testRunnerBootstrappedExtension() emits TestRunner\ExtensionBootstrapped event')]
    public function testTestRunnerBootstrappedExtensionEmitsExtensionBootstrappedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements TestRunner\ExtensionBootstrappedSubscriber
        {
            public function notify(TestRunner\ExtensionBootstrapped $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            TestRunner\ExtensionBootstrappedSubscriber::class,
            TestRunner\ExtensionBootstrapped::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $className  = 'the-extension';
        $parameters = ['foo' => 'bar'];

        $emitter->testRunnerBootstrappedExtension($className, $parameters);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(TestRunner\ExtensionBootstrapped::class, $event);
        $this->assertSame($className, $event->className());
        $this->assertSame($parameters, $event->parameters());
    }

    #[TestDox('dataProviderMethodCalled() emits Test\DataProviderMethodCalled event')]
    public function testDataProviderMethodCalledEmitsDataProviderMethodCalledEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\DataProviderMethodCalledSubscriber
        {
            public function notify(Test\DataProviderMethodCalled $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\DataProviderMethodCalledSubscriber::class,
            Test\DataProviderMethodCalled::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $testMethod         = new ClassMethod('test-class', 'test-method');
        $dataProviderMethod = new ClassMethod('test-class', 'data-provider-method');

        $emitter->dataProviderMethodCalled($testMethod, $dataProviderMethod);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\DataProviderMethodCalled::class, $event);
        $this->assertSame($testMethod, $event->testMethod());
        $this->assertSame($dataProviderMethod, $event->dataProviderMethod());
    }

    #[TestDox('dataProviderMethodFinished() emits Test\DataProviderMethodFinished event')]
    public function testDataProviderMethodFinishedEmitsDataProviderMethodFinishedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\DataProviderMethodFinishedSubscriber
        {
            public function notify(Test\DataProviderMethodFinished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\DataProviderMethodFinishedSubscriber::class,
            Test\DataProviderMethodFinished::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $testMethod         = new ClassMethod('test-class', 'test-method');
        $dataProviderMethod = new ClassMethod('test-class', 'data-provider-method');

        $emitter->dataProviderMethodFinished($testMethod, $dataProviderMethod);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\DataProviderMethodFinished::class, $event);
        $this->assertSame($testMethod, $event->testMethod());
        $this->assertSame([$dataProviderMethod], $event->calledMethods());
    }

    #[TestDox('testSuiteLoaded() emits TestSuite\Loaded event')]
    public function testTestSuiteLoadedEmitsTestSuiteLoadedEvent(): void
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

    #[TestDox('testSuiteFiltered() emits TestSuite\Filtered event')]
    public function testTestSuiteFilteredEmitsTestSuiteFilteredEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements TestSuiteFilteredSubscriber
        {
            public function notify(TestSuiteFiltered $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            TestSuiteFilteredSubscriber::class,
            TestSuiteFiltered::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $testSuite = $this->testSuiteValueObject();

        $emitter->testSuiteFiltered($testSuite);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(TestSuiteFiltered::class, $event);

        $this->assertSame($testSuite, $event->testSuite());
    }

    #[TestDox('testSuiteSorted() emits TestSuite\Sorted event')]
    public function testTestSuiteSortedEmitsTestSuiteSortedEvent(): void
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

    #[TestDox('testRunnerEventFacadeSealed() emits TestRunner\EventFacadeSealed event')]
    public function testTestRunnerEventFacadeSealedEmitsTestRunnerEventFacadeSealedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements TestRunner\EventFacadeSealedSubscriber
        {
            public function notify(TestRunner\EventFacadeSealed $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            TestRunner\EventFacadeSealedSubscriber::class,
            TestRunner\EventFacadeSealed::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->testRunnerEventFacadeSealed();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(TestRunner\EventFacadeSealed::class, $subscriber->lastRecordedEvent());
    }

    #[TestDox('testRunnerExecutionStarted() emits TestRunner\ExecutionStarted event')]
    public function testTestRunnerExecutionStartedEmitsTestRunnerExecutionStartedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements ExecutionStartedSubscriber
        {
            public function notify(ExecutionStarted $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            ExecutionStartedSubscriber::class,
            ExecutionStarted::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $testSuite = $this->testSuiteValueObject();

        $emitter->testRunnerExecutionStarted($testSuite);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(ExecutionStarted::class, $event);
        $this->assertSame($testSuite, $event->testSuite());
    }

    #[TestDox('testRunnerDisabledGarbageCollection() emits TestRunner\GarbageCollectionDisabled event')]
    public function testTestRunnerDisabledGarbageCollectionEmitsTestRunnerGarbageCollectionDisabledEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements GarbageCollectionDisabledSubscriber
        {
            public function notify(GarbageCollectionDisabled $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            GarbageCollectionDisabledSubscriber::class,
            GarbageCollectionDisabled::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->testRunnerDisabledGarbageCollection();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(GarbageCollectionDisabled::class, $subscriber->lastRecordedEvent());
    }

    #[TestDox('testRunnerTriggeredGarbageCollection() emits TestRunner\GarbageCollectionTriggered event')]
    public function testTestRunnerTriggeredGarbageCollectionEmitsTestRunnerGarbageCollectionTriggeredEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements GarbageCollectionTriggeredSubscriber
        {
            public function notify(GarbageCollectionTriggered $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            GarbageCollectionTriggeredSubscriber::class,
            GarbageCollectionTriggered::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->testRunnerTriggeredGarbageCollection();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(GarbageCollectionTriggered::class, $subscriber->lastRecordedEvent());
    }

    #[TestDox('testRunnerStartedChildProcess() emits TestRunner\ChildProcessStarted event')]
    public function testTestRunnerStartedChildProcessEmitsTestRunnerChildProcessStartedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements ChildProcessStartedSubscriber
        {
            public function notify(ChildProcessStarted $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            ChildProcessStartedSubscriber::class,
            ChildProcessStarted::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->testRunnerStartedChildProcess();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(ChildProcessStarted::class, $subscriber->lastRecordedEvent());
    }

    #[TestDox('testRunnerFinishedChildProcess() emits TestRunner\ChildProcessFinished event')]
    public function testTestRunnerFinishedChildProcessEmitsTestRunnerChildProcessFinishedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements ChildProcessFinishedSubscriber
        {
            public function notify(ChildProcessFinished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            ChildProcessFinishedSubscriber::class,
            ChildProcessFinished::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $stdout = 'stdout';
        $stderr = 'stderr';

        $emitter->testRunnerFinishedChildProcess($stdout, $stderr);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(ChildProcessFinished::class, $event);
        $this->assertSame($stdout, $event->stdout());
        $this->assertSame($stderr, $event->stderr());
    }

    #[TestDox('testSuiteSkipped() emits TestSuite\Skipped event')]
    public function testTestSuiteSkippedEmitsTestSuiteSkippedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements TestSuiteSkippedSubscriber
        {
            public function notify(TestSuiteSkipped $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            TestSuiteSkippedSubscriber::class,
            TestSuiteSkipped::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $testSuite = $this->testSuiteValueObject();
        $message   = 'message';

        $emitter->testSuiteSkipped($testSuite, $message);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(TestSuiteSkipped::class, $event);
        $this->assertSame($testSuite, $event->testSuite());
        $this->assertSame($message, $event->message());
    }

    #[TestDox('testSuiteStarted() emits TestSuite\Started event')]
    public function testTestSuiteStartedEmitsTestSuiteStartedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements TestSuiteStartedSubscriber
        {
            public function notify(TestSuiteStarted $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            TestSuiteStartedSubscriber::class,
            TestSuiteStarted::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $testSuite = $this->testSuiteValueObject();

        $emitter->testSuiteStarted($testSuite);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(TestSuiteStarted::class, $event);
        $this->assertSame($testSuite, $event->testSuite());
    }

    #[TestDox('testPreparationStarted() emits Test\PreparationStarted event')]
    public function testTestPreparationStartedEmitsTestPreparationStartedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\PreparationStartedSubscriber
        {
            public function notify(Test\PreparationStarted $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\PreparationStartedSubscriber::class,
            Test\PreparationStarted::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $test = $this->testValueObject();

        $emitter->testPreparationStarted($test);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\PreparationStarted::class, $event);
        $this->assertSame($test, $event->test());
    }

    #[TestDox('testPreparationFailed() emits Test\PreparationFailed event')]
    public function testTestPreparationFailedEmitsTestPreparationFailedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\PreparationFailedSubscriber
        {
            public function notify(Test\PreparationFailed $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\PreparationFailedSubscriber::class,
            Test\PreparationFailed::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $test = $this->testValueObject();

        $emitter->testPreparationFailed($test);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\PreparationFailed::class, $event);
        $this->assertSame($test, $event->test());
    }

    #[TestDox('beforeFirstTestMethodCalled() emits Test\BeforeFirstTestMethodCalled event')]
    public function testTestBeforeFirstTestMethodCalledEmitsTestBeforeFirstTestMethodEvent(): void
    {
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

        $testClassName = 'test-class';
        $calledMethod  = new ClassMethod('test-class', 'method');

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

    #[TestDox('beforeFirstTestMethodErrored() emits Test\BeforeFirstTestMethodErrored event')]
    public function testTestBeforeFirstTestMethodErroredEmitsTestBeforeFirstTestMethodErroredEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\BeforeFirstTestMethodErroredSubscriber
        {
            public function notify(Test\BeforeFirstTestMethodErrored $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\BeforeFirstTestMethodErroredSubscriber::class,
            Test\BeforeFirstTestMethodErrored::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $testClassName = 'test-class';
        $calledMethod  = new ClassMethod('test-class', 'method');
        $throwable     = ThrowableBuilder::from(new Exception('message'));

        $emitter->beforeFirstTestMethodErrored(
            $testClassName,
            $calledMethod,
            $throwable,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\BeforeFirstTestMethodErrored::class, $event);

        $this->assertSame($testClassName, $event->testClassName());
        $this->assertSame($calledMethod, $event->calledMethod());
        $this->assertSame($throwable, $event->throwable());
    }

    #[TestDox('beforeFirstTestMethodFinished() emits Test\BeforeFirstTestMethodFinished event')]
    public function testTestBeforeFirstTestMethodFinishedEmitsTestBeforeFirstTestMethodFinishedEvent(): void
    {
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

        $testClassName = 'test-class';
        $calledMethod  = new ClassMethod('test-class', 'method');

        $emitter->beforeFirstTestMethodFinished(
            $testClassName,
            $calledMethod,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\BeforeFirstTestMethodFinished::class, $event);

        $this->assertSame($testClassName, $event->testClassName());
        $this->assertSame([$calledMethod], $event->calledMethods());
    }

    #[TestDox('beforeTestMethodCalled() emits Test\BeforeTestMethodCalled event')]
    public function testTestBeforeTestMethodCalledEmitsTestBeforeTestMethodEvent(): void
    {
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

        $testClassName = 'test-class';
        $calledMethod  = new ClassMethod('test-class', 'method');

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

    #[TestDox('beforeTestMethodErrored() emits Test\BeforeTestMethodErrored event')]
    public function testTestBeforeTestMethodErroredEmitsTestBeforeTestMethodErroredEvent(): void
    {
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

        $testClassName = 'test-class';
        $calledMethod  = new ClassMethod('test-class', 'method');
        $throwable     = ThrowableBuilder::from(new Exception('message'));

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

    #[TestDox('beforeTestMethodFinished() emits Test\BeforeTestMethodFinished event')]
    public function testTestBeforeTestMethodFinishedEmitsTestBeforeTestMethodFinishedEvent(): void
    {
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

        $testClassName = 'test-class';
        $calledMethod  = new ClassMethod('test-class', 'method');

        $emitter->beforeTestMethodFinished(
            $testClassName,
            $calledMethod,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\BeforeTestMethodFinished::class, $event);

        $this->assertSame($testClassName, $event->testClassName());
        $this->assertSame([$calledMethod], $event->calledMethods());
    }

    #[TestDox('preConditionCalled() emits Test\PreConditionCalled event')]
    public function testPreConditionCalledEmitsTestPreConditionCalledEvent(): void
    {
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

        $testClassName = 'test-class';
        $calledMethod  = new ClassMethod('test-class', 'method');

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

    #[TestDox('preConditionErrored() emits Test\PreConditionErrored event')]
    public function testPreConditionErroredEmitsTestPreConditionErroredEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\PreConditionErroredSubscriber
        {
            public function notify(Test\PreConditionErrored $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\PreConditionErroredSubscriber::class,
            Test\PreConditionErrored::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $testClassName = 'test-class';
        $calledMethod  = new ClassMethod('test-class', 'method');
        $throwable     = ThrowableBuilder::from(new Exception('message'));

        $emitter->preConditionErrored(
            $testClassName,
            $calledMethod,
            $throwable,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\PreConditionErrored::class, $event);

        $this->assertSame($testClassName, $event->testClassName());
        $this->assertSame($calledMethod, $event->calledMethod());
        $this->assertSame($throwable, $event->throwable());
    }

    #[TestDox('preConditionFinished() emits Test\PreConditionFinished event')]
    public function testPreConditionFinishedEmitsTestPreConditionFinishedEvent(): void
    {
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

        $testClassName = 'test-class';
        $calledMethod  = new ClassMethod('test-class', 'method');

        $emitter->preConditionFinished(
            $testClassName,
            $calledMethod,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\PreConditionFinished::class, $event);

        $this->assertSame($testClassName, $event->testClassName());
        $this->assertSame([$calledMethod], $event->calledMethods());
    }

    #[TestDox('testPrepared() emits Test\Prepared event')]
    public function testTestPreparedEmitsTestPreparedEvent(): void
    {
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

        $test = $this->testValueObject();

        $emitter->testPrepared($test);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\Prepared::class, $event);

        $this->assertSame($test, $event->test());
    }

    #[TestDox('testRegisteredComparator() emits Test\ComparatorRegistered event')]
    public function testComparatorRegisteredEmitsComparatorRegisteredEvent(): void
    {
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

        $className = 'the-class';

        $emitter->testRegisteredComparator($className);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\ComparatorRegistered::class, $event);

        $this->assertSame($className, $event->className());
    }

    #[TestDox('testCreatedMockObject() emits Test\MockObjectCreated event')]
    public function testTestCreatedMockObjectEmitsTestMockObjectCreatedEvent(): void
    {
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

        $className = 'the-class';

        $emitter->testCreatedMockObject($className);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\MockObjectCreated::class, $event);

        $this->assertSame($className, $event->className());
    }

    #[TestDox('testCreatedMockObjectForIntersectionOfInterfaces() emits Test\MockObjectForIntersectionOfInterfacesCreated event')]
    public function testTestCreatedMockObjectForIntersectionOfInterfacesEmitsTestMockObjectForIntersectionOfInterfacesCreatedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\MockObjectForIntersectionOfInterfacesCreatedSubscriber
        {
            public function notify(Test\MockObjectForIntersectionOfInterfacesCreated $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\MockObjectForIntersectionOfInterfacesCreatedSubscriber::class,
            Test\MockObjectForIntersectionOfInterfacesCreated::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $interfaces = ['a', 'b'];

        $emitter->testCreatedMockObjectForIntersectionOfInterfaces($interfaces);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\MockObjectForIntersectionOfInterfacesCreated::class, $event);

        $this->assertSame($interfaces, $event->interfaces());
    }

    #[TestDox('testCreatedMockObjectForTrait() emits Test\MockObjectForTraitCreated event')]
    public function testTestMockObjectCreatedForTraitEmitsTestMockObjectForTraitCreatedEvent(): void
    {
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

        $traitName = 'the-trait';

        $emitter->testCreatedMockObjectForTrait($traitName);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\MockObjectForTraitCreated::class, $event);

        $this->assertSame($traitName, $event->traitName());
    }

    #[TestDox('testCreatedMockObjectForAbstractClass() emits Test\MockObjectForAbstractClassCreated event')]
    public function testTestMockObjectCreatedForAbstractClassEmitsTestMockObjectForAbstractClassCreatedEvent(): void
    {
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

        $className = 'the-class';

        $emitter->testCreatedMockObjectForAbstractClass($className);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\MockObjectForAbstractClassCreated::class, $event);

        $this->assertSame($className, $event->className());
    }

    #[TestDox('testCreatedMockObjectFromWsdl() emits Test\MockObjectFromWsdlCreated event')]
    public function testTestCreatedMockObjectFromWsdlEmitsTestMockObjectFromWsdlCreatedEvent(): void
    {
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

        $wsdlFile                = 'wsdl.xml';
        $originalClassName       = 'original-class';
        $mockClassName           = 'mock-class';
        $methods                 = ['foo', 'bar'];
        $callOriginalConstructor = false;
        $options                 = ['foo' => 'bar'];

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

    #[TestDox('testCreatedPartialMockObject() emits Test\PartialMockObjectCreated event')]
    public function testTestCreatedPartialMockObjectEmitsTestPartialMockObjectCreatedEvent(): void
    {
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

        $className   = 'the-class';
        $methodNames = ['foo', 'bar', 'baz'];

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

    #[TestDox('testCreatedTestProxy() emits Test\TestProxyCreated event')]
    public function testTestCreatedTestProxyEmitsTestTestProxyCreatedEvent(): void
    {
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

        $className            = 'the-class';
        $constructorArguments = ['foo'];

        $emitter->testCreatedTestProxy(
            $className,
            $constructorArguments,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\TestProxyCreated::class, $event);

        $this->assertSame($className, $event->className());
        $this->assertSame("'foo'", $event->constructorArguments());
    }

    #[TestDox('testCreatedStub() emits Test\TestStubCreated event')]
    public function testTestStubCreatedEmitsTestTestStubCreatedEvent(): void
    {
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

        $className = 'the-class';

        $emitter->testCreatedStub($className);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\TestStubCreated::class, $event);

        $this->assertSame($className, $event->className());
    }

    #[TestDox('testCreatedStubForIntersectionOfInterfaces() emits Test\TestStubForIntersectionOfInterfacesCreated event')]
    public function testTestCreatedTestStubForIntersectionOfInterfacesEmitsTestTestStubForIntersectionOfInterfacesCreatedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\TestStubForIntersectionOfInterfacesCreatedSubscriber
        {
            public function notify(Test\TestStubForIntersectionOfInterfacesCreated $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\TestStubForIntersectionOfInterfacesCreatedSubscriber::class,
            Test\TestStubForIntersectionOfInterfacesCreated::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $interfaces = ['a', 'b'];

        $emitter->testCreatedStubForIntersectionOfInterfaces($interfaces);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\TestStubForIntersectionOfInterfacesCreated::class, $event);

        $this->assertSame($interfaces, $event->interfaces());
    }

    #[TestDox('testErrored() emits Test\Errored event')]
    public function testTestErroredEmitsTestErroredEvent(): void
    {
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

        $test      = $this->testValueObject();
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

    #[TestDox('testFailed() emits Test\Failed event')]
    public function testTestFailedEmitsTestFailedEvent(): void
    {
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

        $test      = $this->testValueObject();
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

    #[TestDox('testPassed() emits Test\Passed event')]
    public function testTestPassedEmitsTestPassedEvent(): void
    {
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

        $test = $this->testValueObject();

        $emitter->testPassed($test);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\Passed::class, $event);

        $this->assertSame($test, $event->test());
    }

    #[TestDox('testConsideredRisky() emits Test\ConsideredRisky event')]
    public function testTestConsideredRiskyEmitsTestConsideredRiskyEvent(): void
    {
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

        $test    = $this->testValueObject();
        $message = 'message';

        $emitter->testConsideredRisky($test, $message);

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(Test\ConsideredRisky::class, $subscriber->lastRecordedEvent());

        $event = $subscriber->lastRecordedEvent();

        $this->assertSame($test, $event->test());
        $this->assertSame($message, $event->message());
    }

    #[TestDox('testMarkedAsIncomplete() emits Test\MarkedIncomplete event')]
    public function testTestMarkedIncompleteEmitsTestMarkedIncompleteEvent(): void
    {
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

        $test      = $this->testValueObject();
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

    #[TestDox('testSkipped() emits Test\Skipped event')]
    public function testTestSkippedEmitsTestSkippedEvent(): void
    {
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

        $test    = $this->testValueObject();
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

    #[TestDox('testTriggeredPhpunitDeprecation() emits Test\PhpunitDeprecationTriggered event')]
    public function testTestTriggeredPhpunitDeprecationEmitsTestPhpunitDeprecationTriggeredEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\PhpunitDeprecationTriggeredSubscriber
        {
            public function notify(Test\PhpunitDeprecationTriggered $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\PhpunitDeprecationTriggeredSubscriber::class,
            Test\PhpunitDeprecationTriggered::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $test    = $this->testValueObject();
        $message = 'message';

        $emitter->testTriggeredPhpunitDeprecation(
            $test,
            $message,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\PhpunitDeprecationTriggered::class, $event);

        $this->assertSame($test, $event->test());
        $this->assertSame($message, $event->message());
    }

    #[TestDox('testTriggeredPhpDeprecation() emits Test\PhpDeprecationTriggered event')]
    public function testTestTriggeredPhpDeprecationEmitsTestPhpDeprecationTriggeredEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\PhpDeprecationTriggeredSubscriber
        {
            public function notify(Test\PhpDeprecationTriggered $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\PhpDeprecationTriggeredSubscriber::class,
            Test\PhpDeprecationTriggered::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $test              = $this->testValueObject();
        $message           = 'message';
        $file              = 'file.php';
        $line              = 1;
        $suppressed        = false;
        $ignoredByBaseline = false;
        $ignoredByTest     = false;
        $trigger           = IssueTrigger::unknown();

        $emitter->testTriggeredPhpDeprecation(
            $test,
            $message,
            $file,
            $line,
            $suppressed,
            $ignoredByBaseline,
            $ignoredByTest,
            $trigger,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\PhpDeprecationTriggered::class, $event);
        $this->assertSame($test, $event->test());
        $this->assertSame($message, $event->message());
        $this->assertSame($file, $event->file());
        $this->assertSame($line, $event->line());
        $this->assertSame($suppressed, $event->wasSuppressed());
        $this->assertSame($ignoredByBaseline, $event->ignoredByBaseline());
        $this->assertSame($ignoredByTest, $event->ignoredByTest());
        $this->assertSame($trigger, $event->trigger());
    }

    #[TestDox('testTriggeredDeprecation() emits Test\DeprecationTriggered event')]
    public function testTestTriggeredDeprecationEmitsTestDeprecationTriggeredEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\DeprecationTriggeredSubscriber
        {
            public function notify(Test\DeprecationTriggered $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\DeprecationTriggeredSubscriber::class,
            Test\DeprecationTriggered::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $test              = $this->testValueObject();
        $message           = 'message';
        $file              = 'file.php';
        $line              = 1;
        $suppressed        = false;
        $ignoredByBaseline = false;
        $ignoredByTest     = false;
        $trigger           = IssueTrigger::unknown();
        $stackTrace        = 'stack-trace';

        $emitter->testTriggeredDeprecation(
            $test,
            $message,
            $file,
            $line,
            $suppressed,
            $ignoredByBaseline,
            $ignoredByTest,
            $trigger,
            $stackTrace,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\DeprecationTriggered::class, $event);
        $this->assertSame($test, $event->test());
        $this->assertSame($message, $event->message());
        $this->assertSame($file, $event->file());
        $this->assertSame($line, $event->line());
        $this->assertSame($suppressed, $event->wasSuppressed());
        $this->assertSame($ignoredByBaseline, $event->ignoredByBaseline());
        $this->assertSame($ignoredByTest, $event->ignoredByTest());
        $this->assertSame($trigger, $event->trigger());
        $this->assertSame($stackTrace, $event->stackTrace());
    }

    #[TestDox('testTriggeredError() emits Test\ErrorTriggered event')]
    public function testTestTriggeredErrorEmitsTestErrorTriggeredEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\ErrorTriggeredSubscriber
        {
            public function notify(Test\ErrorTriggered $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\ErrorTriggeredSubscriber::class,
            Test\ErrorTriggered::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $test       = $this->testValueObject();
        $message    = 'message';
        $file       = 'file.php';
        $line       = 1;
        $suppressed = false;

        $emitter->testTriggeredError(
            $test,
            $message,
            $file,
            $line,
            $suppressed,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\ErrorTriggered::class, $event);
        $this->assertSame($test, $event->test());
        $this->assertSame($message, $event->message());
        $this->assertSame($file, $event->file());
        $this->assertSame($line, $event->line());
        $this->assertSame($suppressed, $event->wasSuppressed());
    }

    #[TestDox('testTriggeredNotice() emits Test\NoticeTriggered event')]
    public function testTestTriggeredNoticeEmitsTestNoticeTriggeredEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\NoticeTriggeredSubscriber
        {
            public function notify(Test\NoticeTriggered $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\NoticeTriggeredSubscriber::class,
            Test\NoticeTriggered::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $test              = $this->testValueObject();
        $message           = 'message';
        $file              = 'file.php';
        $line              = 1;
        $suppressed        = false;
        $ignoredByBaseline = false;

        $emitter->testTriggeredNotice(
            $test,
            $message,
            $file,
            $line,
            $suppressed,
            $ignoredByBaseline,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\NoticeTriggered::class, $event);
        $this->assertSame($test, $event->test());
        $this->assertSame($message, $event->message());
        $this->assertSame($file, $event->file());
        $this->assertSame($line, $event->line());
        $this->assertSame($suppressed, $event->wasSuppressed());
        $this->assertSame($ignoredByBaseline, $event->ignoredByBaseline());
    }

    #[TestDox('testTriggeredPhpNotice() emits Test\PhpNoticeTriggered event')]
    public function testTestTriggeredPhpNoticeEmitsTestPhpNoticeTriggeredEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\PhpNoticeTriggeredSubscriber
        {
            public function notify(Test\PhpNoticeTriggered $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\PhpNoticeTriggeredSubscriber::class,
            Test\PhpNoticeTriggered::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $test              = $this->testValueObject();
        $message           = 'message';
        $file              = 'file.php';
        $line              = 1;
        $suppressed        = false;
        $ignoredByBaseline = false;

        $emitter->testTriggeredPhpNotice(
            $test,
            $message,
            $file,
            $line,
            $suppressed,
            $ignoredByBaseline,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\PhpNoticeTriggered::class, $event);
        $this->assertSame($test, $event->test());
        $this->assertSame($message, $event->message());
        $this->assertSame($file, $event->file());
        $this->assertSame($line, $event->line());
        $this->assertSame($suppressed, $event->wasSuppressed());
        $this->assertSame($ignoredByBaseline, $event->ignoredByBaseline());
    }

    #[TestDox('testTriggeredWarning() emits Test\WarningTriggered event')]
    public function testTestTriggeredWarningEmitsTestWarningTriggeredEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\WarningTriggeredSubscriber
        {
            public function notify(Test\WarningTriggered $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\WarningTriggeredSubscriber::class,
            Test\WarningTriggered::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $test              = $this->testValueObject();
        $message           = 'message';
        $file              = 'file.php';
        $line              = 1;
        $suppressed        = false;
        $ignoredByBaseline = false;

        $emitter->testTriggeredWarning(
            $test,
            $message,
            $file,
            $line,
            $suppressed,
            $ignoredByBaseline,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\WarningTriggered::class, $event);
        $this->assertSame($test, $event->test());
        $this->assertSame($message, $event->message());
        $this->assertSame($file, $event->file());
        $this->assertSame($line, $event->line());
        $this->assertSame($suppressed, $event->wasSuppressed());
        $this->assertSame($ignoredByBaseline, $event->ignoredByBaseline());
    }

    #[TestDox('testTriggeredPhpWarning() emits Test\PhpWarningTriggered event')]
    public function testTestTriggeredPhpWarningEmitsTestPhpWarningTriggeredEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\PhpWarningTriggeredSubscriber
        {
            public function notify(Test\PhpWarningTriggered $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\PhpWarningTriggeredSubscriber::class,
            Test\PhpWarningTriggered::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $test              = $this->testValueObject();
        $message           = 'message';
        $file              = 'file.php';
        $line              = 1;
        $suppressed        = false;
        $ignoredByBaseline = false;

        $emitter->testTriggeredPhpWarning(
            $test,
            $message,
            $file,
            $line,
            $suppressed,
            $ignoredByBaseline,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\PhpWarningTriggered::class, $event);
        $this->assertSame($test, $event->test());
        $this->assertSame($message, $event->message());
        $this->assertSame($file, $event->file());
        $this->assertSame($line, $event->line());
        $this->assertSame($suppressed, $event->wasSuppressed());
        $this->assertSame($ignoredByBaseline, $event->ignoredByBaseline());
    }

    #[TestDox('testTriggeredPhpunitError() emits Test\PhpunitErrorTriggered event')]
    public function testTestTriggeredPhpunitErrorEmitsTestPhpunitErrorTriggeredEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\PhpunitErrorTriggeredSubscriber
        {
            public function notify(Test\PhpunitErrorTriggered $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\PhpunitErrorTriggeredSubscriber::class,
            Test\PhpunitErrorTriggered::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $test    = $this->testValueObject();
        $message = 'message';

        $emitter->testTriggeredPhpunitError(
            $test,
            $message,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\PhpunitErrorTriggered::class, $event);
        $this->assertSame($test, $event->test());
        $this->assertSame($message, $event->message());
    }

    #[TestDox('testTriggeredPhpunitWarning() emits Test\PhpunitWarningTriggered event')]
    public function testTestTriggeredPhpunitWarningEmitsTestPhpunitWarningTriggeredEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\PhpunitWarningTriggeredSubscriber
        {
            public function notify(Test\PhpunitWarningTriggered $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\PhpunitWarningTriggeredSubscriber::class,
            Test\PhpunitWarningTriggered::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $test    = $this->testValueObject();
        $message = 'message';

        $emitter->testTriggeredPhpunitWarning(
            $test,
            $message,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\PhpunitWarningTriggered::class, $event);
        $this->assertSame($test, $event->test());
        $this->assertSame($message, $event->message());
    }

    #[TestDox('testPrintedUnexpectedOutput() emits Test\PrintedUnexpectedOutput event')]
    public function testTestPrintedUnexpectedOutputEmitsTestPrintedUnexpectedOutputEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\PrintedUnexpectedOutputSubscriber
        {
            public function notify(Test\PrintedUnexpectedOutput $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\PrintedUnexpectedOutputSubscriber::class,
            Test\PrintedUnexpectedOutput::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $output = 'output';

        $emitter->testPrintedUnexpectedOutput(
            $output,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\PrintedUnexpectedOutput::class, $event);
        $this->assertSame($output, $event->output());
    }

    #[TestDox('testFinished() emits Test\Finished event')]
    public function testTestFinishedEmitsTestFinishedEvent(): void
    {
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

        $test = $this->testValueObject();

        $emitter->testFinished($test, 1);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\Finished::class, $event);

        $this->assertSame($test, $event->test());
        $this->assertSame(1, $event->numberOfAssertionsPerformed());
    }

    #[TestDox('postConditionCalled() emits Test\PostConditionCalled event')]
    public function testPostConditionCalledEmitsTestPostConditionCalledEvent(): void
    {
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

        $testClassName = 'test-class';
        $calledMethod  = new ClassMethod('test-class', 'method');

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

    #[TestDox('postConditionErrored() emits Test\PostConditionErrored event')]
    public function testPostConditionErroredEmitsTestPostConditionErroredEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Test\PostConditionErroredSubscriber
        {
            public function notify(Test\PostConditionErrored $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Test\PostConditionErroredSubscriber::class,
            Test\PostConditionErrored::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $testClassName = 'test-class';
        $calledMethod  = new ClassMethod('test-class', 'method');
        $throwable     = ThrowableBuilder::from(new Exception('message'));

        $emitter->postConditionErrored(
            $testClassName,
            $calledMethod,
            $throwable,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\PostConditionErrored::class, $event);

        $this->assertSame($testClassName, $event->testClassName());
        $this->assertSame($calledMethod, $event->calledMethod());
        $this->assertSame($throwable, $event->throwable());
    }

    #[TestDox('postConditionFinished() emits Test\PostConditionFinished event')]
    public function testPostConditionFinishedEmitsTestPostConditionFinishedEvent(): void
    {
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

        $testClassName = 'test-class';
        $calledMethod  = new ClassMethod('test-class', 'method');

        $emitter->postConditionFinished(
            $testClassName,
            $calledMethod,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\PostConditionFinished::class, $event);

        $this->assertSame($testClassName, $event->testClassName());
        $this->assertSame([$calledMethod], $event->calledMethods());
    }

    #[TestDox('afterTestMethodCalled() emits Test\AfterTestMethodCalled event')]
    public function testTestAfterTestMethodCalledEmitsTestAfterTestMethodEvent(): void
    {
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

        $testClassName = 'test-class';
        $calledMethod  = new ClassMethod('test-class', 'method');

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

    #[TestDox('afterTestMethodErrored() emits Test\AfterTestMethodErrored event')]
    public function testTestAfterTestMethodErroredEmitsTestAfterTestMethodErroredEvent(): void
    {
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

        $testClassName = 'test-class';
        $calledMethod  = new ClassMethod('test-class', 'method');
        $throwable     = ThrowableBuilder::from(new Exception('message'));

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

    #[TestDox('afterTestMethodFinished() emits Test\AfterTestMethodFinished event')]
    public function testTestAfterTestMethodFinishedEmitsTestAfterTestMethodFinishedEvent(): void
    {
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

        $testClassName = 'test-class';
        $calledMethod  = new ClassMethod('test-class', 'method');

        $emitter->afterTestMethodFinished(
            $testClassName,
            $calledMethod,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\AfterTestMethodFinished::class, $event);

        $this->assertSame($testClassName, $event->testClassName());
        $this->assertSame([$calledMethod], $event->calledMethods());
    }

    #[TestDox('afterLastTestMethodCalled() emits Test\AfterLastTestMethodCalled event')]
    public function testTestAfterLastTestMethodCalledEmitsTestAfterLastTestMethodEvent(): void
    {
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

        $testClassName = 'test-class';
        $calledMethod  = new ClassMethod('test-class', 'method');

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

    #[TestDox('afterLastTestMethodErrored() emits Test\AfterLastTestMethodErrored event')]
    public function testTestAfterLastTestMethodErroredEmitsTestAfterLastTestMethodErroredEvent(): void
    {
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

        $testClassName = 'test-class';
        $calledMethod  = new ClassMethod('test-class', 'method');
        $throwable     = ThrowableBuilder::from(new Exception('message'));

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

    #[TestDox('afterLastTestMethodFinished() emits Test\AfterLastTestMethodFinished event')]
    public function testTestAfterLastTestMethodFinishedEmitsTestAfterLastTestMethodFinishedEvent(): void
    {
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

        $testClassName = 'test-class';
        $calledMethod  = new ClassMethod('test-class', 'method');

        $emitter->afterLastTestMethodFinished(
            $testClassName,
            $calledMethod,
        );

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Test\AfterLastTestMethodFinished::class, $event);

        $this->assertSame($testClassName, $event->testClassName());
        $this->assertSame([$calledMethod], $event->calledMethods());
    }

    #[TestDox('testSuiteFinished() emits TestSuite\Finished event')]
    public function testTestSuiteFinishedEmitsTestSuiteFinishedEvent(): void
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

    #[TestDox('testRunnerTriggeredPhpunitDeprecation() emits TestRunner\DeprecationTriggered event')]
    public function testTestRunnerTriggeredPhpunitDeprecationEmitsTestRunnerDeprecationTriggeredEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements TestRunnerDeprecationTriggeredSubscriber
        {
            public function notify(TestRunnerDeprecationTriggered $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            TestRunnerDeprecationTriggeredSubscriber::class,
            TestRunnerDeprecationTriggered::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $message = 'message';

        $emitter->testRunnerTriggeredPhpunitDeprecation($message);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(TestRunnerDeprecationTriggered::class, $event);

        $this->assertSame($message, $event->message());
    }

    #[TestDox('testRunnerTriggeredPhpunitWarning() emits TestRunner\WarningTriggered event')]
    public function testTestRunnerTriggeredPhpunitWarningEmitsTestRunnerWarningTriggeredEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements TestRunnerWarningTriggeredSubscriber
        {
            public function notify(TestRunnerWarningTriggered $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            TestRunnerWarningTriggeredSubscriber::class,
            TestRunnerWarningTriggered::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $message = 'message';

        $emitter->testRunnerTriggeredPhpunitWarning($message);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(TestRunnerWarningTriggered::class, $event);

        $this->assertSame($message, $event->message());
    }

    #[TestDox('testRunnerEnabledGarbageCollection() emits TestRunner\GarbageCollectionEnabled event')]
    public function testTestRunnerEnabledGarbageCollectionEmitsTestRunnerGarbageCollectionEnabledEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements GarbageCollectionEnabledSubscriber
        {
            public function notify(GarbageCollectionEnabled $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            GarbageCollectionEnabledSubscriber::class,
            GarbageCollectionEnabled::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->testRunnerEnabledGarbageCollection();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(GarbageCollectionEnabled::class, $subscriber->lastRecordedEvent());
    }

    #[TestDox('testRunnerExecutionAborted() emits TestRunner\ExecutionAborted event')]
    public function testTestRunnerExecutionAbortedEmitsTestRunnerExecutionAbortedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements ExecutionAbortedSubscriber
        {
            public function notify(ExecutionAborted $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            ExecutionAbortedSubscriber::class,
            ExecutionAborted::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->testRunnerExecutionAborted();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(ExecutionAborted::class, $subscriber->lastRecordedEvent());
    }

    #[TestDox('testRunnerExecutionFinished() emits TestRunner\ExecutionFinished event')]
    public function testTestRunnerExecutionFinishedEmitsTestRunnerExecutionFinishedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements ExecutionFinishedSubscriber
        {
            public function notify(ExecutionFinished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            ExecutionFinishedSubscriber::class,
            ExecutionFinished::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $emitter->testRunnerExecutionFinished();

        $this->assertSame(1, $subscriber->recordedEventCount());
        $this->assertInstanceOf(ExecutionFinished::class, $subscriber->lastRecordedEvent());
    }

    #[TestDox('testRunnerFinished() emits TestRunner\Finished event')]
    public function testTestRunnerFinishedEmitsTestRunnerFinishedEvent(): void
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

    #[TestDox('applicationFinished() emits Application\Finished event')]
    public function testApplicationFinishedEmitsApplicationFinishedEvent(): void
    {
        $subscriber = new class extends RecordingSubscriber implements Application\FinishedSubscriber
        {
            public function notify(Application\Finished $event): void
            {
                $this->record($event);
            }
        };

        $dispatcher = $this->dispatcherWithRegisteredSubscriber(
            Application\FinishedSubscriber::class,
            Application\Finished::class,
            $subscriber,
        );

        $telemetrySystem = $this->telemetrySystem();

        $emitter = new DispatchingEmitter(
            $dispatcher,
            $telemetrySystem,
        );

        $shellExitCode = 0;

        $emitter->applicationFinished($shellExitCode);

        $this->assertSame(1, $subscriber->recordedEventCount());

        $event = $subscriber->lastRecordedEvent();

        $this->assertInstanceOf(Application\Finished::class, $event);
        $this->assertSame($shellExitCode, $event->shellExitCode());
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
