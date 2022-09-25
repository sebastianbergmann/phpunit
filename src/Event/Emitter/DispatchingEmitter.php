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

use function assert;
use function debug_backtrace;
use PHPUnit\Event\Code\ClassMethod;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\TestSuite\Filtered as TestSuiteFiltered;
use PHPUnit\Event\TestSuite\Finished as TestSuiteFinished;
use PHPUnit\Event\TestSuite\Loaded as TestSuiteLoaded;
use PHPUnit\Event\TestSuite\Sorted as TestSuiteSorted;
use PHPUnit\Event\TestSuite\Started as TestSuiteStarted;
use PHPUnit\Event\TestSuite\TestSuite;
use PHPUnit\Framework\Constraint;
use PHPUnit\TextUI\Configuration\Configuration;
use SebastianBergmann\Exporter\Exporter;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class DispatchingEmitter implements Emitter
{
    private readonly Dispatcher $dispatcher;
    private readonly Telemetry\System $system;
    private Telemetry\Snapshot $startSnapshot;
    private Telemetry\Snapshot $previousSnapshot;

    public function __construct(Dispatcher $dispatcher, Telemetry\System $system)
    {
        $this->dispatcher = $dispatcher;
        $this->system     = $system;

        $this->startSnapshot    = $system->snapshot();
        $this->previousSnapshot = $system->snapshot();
    }

    public function testRunnerStarted(): void
    {
        $this->dispatcher->dispatch(
            new TestRunner\Started(
                $this->telemetryInfo(),
                new Runtime\Runtime
            )
        );
    }

    public function testRunnerConfigured(Configuration $configuration): void
    {
        $this->dispatcher->dispatch(
            new TestRunner\Configured(
                $this->telemetryInfo(),
                $configuration
            )
        );
    }

    public function testRunnerBootstrapFinished(string $filename): void
    {
        $this->dispatcher->dispatch(
            new TestRunner\BootstrapFinished(
                $this->telemetryInfo(),
                $filename
            )
        );
    }

    public function testRunnerLoadedExtensionFromPhar(string $filename, string $name, string $version): void
    {
        $this->dispatcher->dispatch(
            new TestRunner\ExtensionLoadedFromPhar(
                $this->telemetryInfo(),
                $filename,
                $name,
                $version
            )
        );
    }

    /**
     * @psalm-param class-string $className
     * @psalm-param array<string, string> $parameters
     */
    public function testRunnerBootstrappedExtension(string $className, array $parameters): void
    {
        $this->dispatcher->dispatch(
            new TestRunner\ExtensionBootstrapped(
                $this->telemetryInfo(),
                $className,
                $parameters
            )
        );
    }

    public function testSuiteLoaded(TestSuite $testSuite): void
    {
        $this->dispatcher->dispatch(
            new TestSuiteLoaded(
                $this->telemetryInfo(),
                $testSuite
            )
        );
    }

    public function testSuiteFiltered(TestSuite $testSuite): void
    {
        $this->dispatcher->dispatch(
            new TestSuiteFiltered(
                $this->telemetryInfo(),
                $testSuite
            )
        );
    }

    public function testSuiteSorted(int $executionOrder, int $executionOrderDefects, bool $resolveDependencies): void
    {
        $this->dispatcher->dispatch(
            new TestSuiteSorted(
                $this->telemetryInfo(),
                $executionOrder,
                $executionOrderDefects,
                $resolveDependencies
            )
        );
    }

    public function testRunnerEventFacadeSealed(): void
    {
        $this->dispatcher->dispatch(
            new TestRunner\EventFacadeSealed(
                $this->telemetryInfo()
            )
        );
    }

    public function testRunnerExecutionStarted(TestSuite $testSuite): void
    {
        $this->dispatcher->dispatch(
            new TestRunner\ExecutionStarted(
                $this->telemetryInfo(),
                $testSuite
            )
        );
    }

    public function testSuiteStarted(TestSuite $testSuite): void
    {
        $this->dispatcher->dispatch(
            new TestSuiteStarted(
                $this->telemetryInfo(),
                $testSuite
            )
        );
    }

    public function testPreparationStarted(Code\Test $test): void
    {
        $this->dispatcher->dispatch(
            new Test\PreparationStarted(
                $this->telemetryInfo(),
                $test
            )
        );
    }

    /**
     * @psalm-param class-string $testClassName
     */
    public function testBeforeFirstTestMethodCalled(string $testClassName, Code\ClassMethod $calledMethod): void
    {
        $this->dispatcher->dispatch(
            new Test\BeforeFirstTestMethodCalled(
                $this->telemetryInfo(),
                $testClassName,
                $calledMethod
            )
        );
    }

    /**
     * @psalm-param class-string $testClassName
     */
    public function testBeforeFirstTestMethodErrored(string $testClassName, Code\ClassMethod $calledMethod, Throwable $throwable): void
    {
        $this->dispatcher->dispatch(
            new Test\BeforeFirstTestMethodErrored(
                $this->telemetryInfo(),
                $testClassName,
                $calledMethod,
                $throwable
            )
        );
    }

    /**
     * @psalm-param class-string $testClassName
     */
    public function testBeforeFirstTestMethodFinished(string $testClassName, Code\ClassMethod ...$calledMethods): void
    {
        $this->dispatcher->dispatch(
            new Test\BeforeFirstTestMethodFinished(
                $this->telemetryInfo(),
                $testClassName,
                ...$calledMethods
            )
        );
    }

    /**
     * @psalm-param class-string $testClassName
     */
    public function testBeforeTestMethodCalled(string $testClassName, Code\ClassMethod $calledMethod): void
    {
        $this->dispatcher->dispatch(
            new Test\BeforeTestMethodCalled(
                $this->telemetryInfo(),
                $testClassName,
                $calledMethod
            )
        );
    }

    /**
     * @psalm-param class-string $testClassName
     */
    public function testBeforeTestMethodFinished(string $testClassName, Code\ClassMethod ...$calledMethods): void
    {
        $this->dispatcher->dispatch(
            new Test\BeforeTestMethodFinished(
                $this->telemetryInfo(),
                $testClassName,
                ...$calledMethods
            )
        );
    }

    /**
     * @psalm-param class-string $testClassName
     */
    public function testPreConditionCalled(string $testClassName, Code\ClassMethod $calledMethod): void
    {
        $this->dispatcher->dispatch(
            new Test\PreConditionCalled(
                $this->telemetryInfo(),
                $testClassName,
                $calledMethod
            )
        );
    }

    /**
     * @psalm-param class-string $testClassName
     */
    public function testPreConditionFinished(string $testClassName, Code\ClassMethod ...$calledMethods): void
    {
        $this->dispatcher->dispatch(
            new Test\PreConditionFinished(
                $this->telemetryInfo(),
                $testClassName,
                ...$calledMethods
            )
        );
    }

    public function testPrepared(Code\Test $test): void
    {
        $this->dispatcher->dispatch(
            new Test\Prepared(
                $this->telemetryInfo(),
                $test
            )
        );
    }

    /**
     * @psalm-param class-string $className
     */
    public function testRegisteredComparator(string $className): void
    {
        $this->dispatcher->dispatch(
            new Test\ComparatorRegistered(
                $this->telemetryInfo(),
                $className
            )
        );
    }

    public function testAssertionSucceeded(mixed $value, Constraint\Constraint $constraint, string $message): void
    {
        $this->dispatcher->dispatch(
            new Test\AssertionSucceeded(
                $this->telemetryInfo(),
                (new Exporter)->export($value),
                $constraint->toString(),
                $constraint->count(),
                $message,
            )
        );
    }

    public function testAssertionFailed(mixed $value, Constraint\Constraint $constraint, string $message): void
    {
        $this->dispatcher->dispatch(
            new Test\AssertionFailed(
                $this->telemetryInfo(),
                (new Exporter)->export($value),
                $constraint->toString(),
                $constraint->count(),
                $message,
            )
        );
    }

    /**
     * @psalm-param class-string $className
     */
    public function testCreatedMockObject(string $className): void
    {
        $this->dispatcher->dispatch(
            new Test\MockObjectCreated(
                $this->telemetryInfo(),
                $className
            )
        );
    }

    /**
     * @psalm-param trait-string $traitName
     */
    public function testCreatedMockObjectForTrait(string $traitName): void
    {
        $this->dispatcher->dispatch(
            new Test\MockObjectForTraitCreated(
                $this->telemetryInfo(),
                $traitName
            )
        );
    }

    /**
     * @psalm-param class-string $className
     */
    public function testCreatedMockObjectForAbstractClass(string $className): void
    {
        $this->dispatcher->dispatch(
            new Test\MockObjectForAbstractClassCreated(
                $this->telemetryInfo(),
                $className
            )
        );
    }

    /**
     * @psalm-param class-string $originalClassName
     * @psalm-param class-string $mockClassName
     */
    public function testCreatedMockObjectFromWsdl(string $wsdlFile, string $originalClassName, string $mockClassName, array $methods, bool $callOriginalConstructor, array $options): void
    {
        $this->dispatcher->dispatch(
            new Test\MockObjectFromWsdlCreated(
                $this->telemetryInfo(),
                $wsdlFile,
                $originalClassName,
                $mockClassName,
                $methods,
                $callOriginalConstructor,
                $options
            )
        );
    }

    /**
     * @psalm-param class-string $className
     */
    public function testCreatedPartialMockObject(string $className, string ...$methodNames): void
    {
        $this->dispatcher->dispatch(
            new Test\PartialMockObjectCreated(
                $this->telemetryInfo(),
                $className,
                ...$methodNames
            )
        );
    }

    /**
     * @psalm-param class-string $className
     */
    public function testCreatedTestProxy(string $className, array $constructorArguments): void
    {
        $this->dispatcher->dispatch(
            new Test\TestProxyCreated(
                $this->telemetryInfo(),
                $className,
                (new Exporter)->export($constructorArguments)
            )
        );
    }

    /**
     * @psalm-param class-string $className
     */
    public function testCreatedStub(string $className): void
    {
        $this->dispatcher->dispatch(
            new Test\TestStubCreated(
                $this->telemetryInfo(),
                $className
            )
        );
    }

    public function testErrored(Code\Test $test, Throwable $throwable): void
    {
        $this->dispatcher->dispatch(
            new Test\Errored(
                $this->telemetryInfo(),
                $test,
                $throwable
            )
        );
    }

    public function testFailed(Code\Test $test, Throwable $throwable): void
    {
        $this->dispatcher->dispatch(
            new Test\Failed(
                $this->telemetryInfo(),
                $test,
                $throwable
            )
        );
    }

    public function testPassed(Code\Test $test): void
    {
        $this->dispatcher->dispatch(
            new Test\Passed(
                $this->telemetryInfo(),
                $test,
            )
        );
    }

    public function testConsideredRisky(Code\Test $test, string $message): void
    {
        $this->dispatcher->dispatch(
            new Test\ConsideredRisky(
                $this->telemetryInfo(),
                $test,
                $message
            )
        );
    }

    public function testMarkedAsIncomplete(Code\Test $test, Throwable $throwable): void
    {
        $this->dispatcher->dispatch(
            new Test\MarkedIncomplete(
                $this->telemetryInfo(),
                $test,
                $throwable
            )
        );
    }

    public function testSkipped(Code\Test $test, string $message): void
    {
        $this->dispatcher->dispatch(
            new Test\Skipped(
                $this->telemetryInfo(),
                $test,
                $message
            )
        );
    }

    public function testTriggeredPhpunitDeprecation(Code\Test $test, string $message): void
    {
        $this->dispatcher->dispatch(
            new Test\PhpunitDeprecationTriggered(
                $this->telemetryInfo(),
                $test,
                $message
            )
        );
    }

    public function testTriggeredPhpDeprecation(Code\Test $test, string $message, string $file, int $line): void
    {
        $this->dispatcher->dispatch(
            new Test\PhpDeprecationTriggered(
                $this->telemetryInfo(),
                $test,
                $message,
                $file,
                $line
            )
        );
    }

    public function testTriggeredDeprecation(Code\Test $test, string $message, string $file, int $line): void
    {
        $this->dispatcher->dispatch(
            new Test\DeprecationTriggered(
                $this->telemetryInfo(),
                $test,
                $message,
                $file,
                $line
            )
        );
    }

    public function testTriggeredError(Code\Test $test, string $message, string $file, int $line): void
    {
        $this->dispatcher->dispatch(
            new Test\ErrorTriggered(
                $this->telemetryInfo(),
                $test,
                $message,
                $file,
                $line
            )
        );
    }

    public function testTriggeredNotice(Code\Test $test, string $message, string $file, int $line): void
    {
        $this->dispatcher->dispatch(
            new Test\NoticeTriggered(
                $this->telemetryInfo(),
                $test,
                $message,
                $file,
                $line
            )
        );
    }

    public function testTriggeredPhpNotice(Code\Test $test, string $message, string $file, int $line): void
    {
        $this->dispatcher->dispatch(
            new Test\PhpNoticeTriggered(
                $this->telemetryInfo(),
                $test,
                $message,
                $file,
                $line
            )
        );
    }

    public function testTriggeredWarning(Code\Test $test, string $message, string $file, int $line): void
    {
        $this->dispatcher->dispatch(
            new Test\WarningTriggered(
                $this->telemetryInfo(),
                $test,
                $message,
                $file,
                $line
            )
        );
    }

    public function testTriggeredPhpWarning(Code\Test $test, string $message, string $file, int $line): void
    {
        $this->dispatcher->dispatch(
            new Test\PhpWarningTriggered(
                $this->telemetryInfo(),
                $test,
                $message,
                $file,
                $line
            )
        );
    }

    public function testTriggeredPhpunitError(Code\Test $test, string $message): void
    {
        $this->dispatcher->dispatch(
            new Test\PhpunitErrorTriggered(
                $this->telemetryInfo(),
                $test,
                $message,
            )
        );
    }

    public function testTriggeredPhpunitWarning(Code\Test $test, string $message): void
    {
        $this->dispatcher->dispatch(
            new Test\PhpunitWarningTriggered(
                $this->telemetryInfo(),
                $test,
                $message,
            )
        );
    }

    public function testFinished(Code\Test $test, int $numberOfAssertionsPerformed): void
    {
        $this->dispatcher->dispatch(
            new Test\Finished(
                $this->telemetryInfo(),
                $test,
                $numberOfAssertionsPerformed
            )
        );
    }

    /**
     * @psalm-param class-string $testClassName
     */
    public function testPostConditionCalled(string $testClassName, Code\ClassMethod $calledMethod): void
    {
        $this->dispatcher->dispatch(
            new Test\PostConditionCalled(
                $this->telemetryInfo(),
                $testClassName,
                $calledMethod
            )
        );
    }

    /**
     * @psalm-param class-string $testClassName
     */
    public function testPostConditionFinished(string $testClassName, Code\ClassMethod ...$calledMethods): void
    {
        $this->dispatcher->dispatch(
            new Test\PostConditionFinished(
                $this->telemetryInfo(),
                $testClassName,
                ...$calledMethods
            )
        );
    }

    /**
     * @psalm-param class-string $testClassName
     */
    public function testAfterTestMethodCalled(string $testClassName, Code\ClassMethod $calledMethod): void
    {
        $this->dispatcher->dispatch(
            new Test\AfterTestMethodCalled(
                $this->telemetryInfo(),
                $testClassName,
                $calledMethod
            )
        );
    }

    /**
     * @psalm-param class-string $testClassName
     */
    public function testAfterTestMethodFinished(string $testClassName, Code\ClassMethod ...$calledMethods): void
    {
        $this->dispatcher->dispatch(
            new Test\AfterTestMethodFinished(
                $this->telemetryInfo(),
                $testClassName,
                ...$calledMethods
            )
        );
    }

    /**
     * @psalm-param class-string $testClassName
     */
    public function testAfterLastTestMethodCalled(string $testClassName, Code\ClassMethod $calledMethod): void
    {
        $this->dispatcher->dispatch(
            new Test\AfterLastTestMethodCalled(
                $this->telemetryInfo(),
                $testClassName,
                $calledMethod
            )
        );
    }

    /**
     * @psalm-param class-string $testClassName
     */
    public function testAfterLastTestMethodFinished(string $testClassName, Code\ClassMethod ...$calledMethods): void
    {
        $this->dispatcher->dispatch(
            new Test\AfterLastTestMethodFinished(
                $this->telemetryInfo(),
                $testClassName,
                ...$calledMethods
            )
        );
    }

    public function testSuiteFinished(TestSuite $testSuite): void
    {
        $this->dispatcher->dispatch(
            new TestSuiteFinished(
                $this->telemetryInfo(),
                $testSuite,
            )
        );
    }

    public function testRunnerTriggeredDeprecation(string $message): void
    {
        $this->dispatcher->dispatch(
            new TestRunner\DeprecationTriggered(
                $this->telemetryInfo(),
                $message
            )
        );
    }

    public function testRunnerTriggeredWarning(string $message): void
    {
        $this->dispatcher->dispatch(
            new TestRunner\WarningTriggered(
                $this->telemetryInfo(),
                $message
            )
        );
    }

    public function testRunnerExecutionFinished(): void
    {
        $this->dispatcher->dispatch(
            new TestRunner\ExecutionFinished($this->telemetryInfo())
        );
    }

    public function testRunnerFinished(): void
    {
        $this->dispatcher->dispatch(
            new TestRunner\Finished($this->telemetryInfo())
        );
    }

    private function telemetryInfo(): Telemetry\Info
    {
        $current = $this->system->snapshot();
        $emitter = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2];

        assert(isset($emitter['class'], $emitter['function']));

        $info = new Telemetry\Info(
            $current,
            $current->time()->duration($this->startSnapshot->time()),
            $current->memoryUsage()->diff($this->startSnapshot->memoryUsage()),
            $current->time()->duration($this->previousSnapshot->time()),
            $current->memoryUsage()->diff($this->previousSnapshot->memoryUsage()),
            new ClassMethod($emitter['class'], $emitter['function'])
        );

        $this->previousSnapshot = $current;

        return $info;
    }
}
