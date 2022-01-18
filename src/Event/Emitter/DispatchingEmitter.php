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

use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\TestSuite\Filtered as TestSuiteFiltered;
use PHPUnit\Event\TestSuite\Finished as TestSuiteFinished;
use PHPUnit\Event\TestSuite\Loaded as TestSuiteLoaded;
use PHPUnit\Event\TestSuite\Result;
use PHPUnit\Event\TestSuite\Sorted as TestSuiteSorted;
use PHPUnit\Event\TestSuite\Started as TestSuiteStarted;
use PHPUnit\Event\TestSuite\TestSuite;
use PHPUnit\Framework\Constraint;
use PHPUnit\TextUI\Configuration\Configuration;
use SebastianBergmann\GlobalState\Snapshot;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class DispatchingEmitter implements Emitter
{
    private Dispatcher $dispatcher;
    private Telemetry\System $system;
    private Telemetry\Snapshot $startSnapshot;
    private Telemetry\Snapshot $previousSnapshot;
    private bool $testExecutionStarted = false;

    public function __construct(Dispatcher $dispatcher, Telemetry\System $system)
    {
        $this->dispatcher = $dispatcher;
        $this->system     = $system;

        $this->startSnapshot    = $system->snapshot();
        $this->previousSnapshot = $system->snapshot();
    }

    public function eventFacadeSealed(): void
    {
        $this->dispatcher->dispatch(
            new TestRunner\EventFacadeSealed(
                $this->telemetryInfo()
            )
        );
    }

    public function testRunnerStarted(): void
    {
        $this->dispatcher->dispatch(
            new TestRunner\Started(
                $this->telemetryInfo(),
                new Runtime\Runtime()
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

    public function testRunnerFinished(): void
    {
        $this->dispatcher->dispatch(
            new TestRunner\Finished($this->telemetryInfo())
        );
    }

    public function assertionMade(mixed $value, Constraint\Constraint $constraint, string $message, bool $hasFailed): void
    {
        $this->dispatcher->dispatch(
            new Test\AssertionMade(
                $this->telemetryInfo(),
                $value,
                $constraint,
                $message,
                $hasFailed
            )
        );
    }

    public function bootstrapFinished(string $filename): void
    {
        $this->dispatcher->dispatch(
            new TestRunner\BootstrapFinished(
                $this->telemetryInfo(),
                $filename
            )
        );
    }

    /**
     * @psalm-param class-string $className
     */
    public function comparatorRegistered(string $className): void
    {
        $this->dispatcher->dispatch(
            new Test\ComparatorRegistered(
                $this->telemetryInfo(),
                $className
            )
        );
    }

    public function extensionLoaded(string $name, string $version): void
    {
        $this->dispatcher->dispatch(
            new TestRunner\ExtensionLoaded(
                $this->telemetryInfo(),
                $name,
                $version
            )
        );
    }

    public function globalStateCaptured(Snapshot $snapshot): void
    {
        $this->dispatcher->dispatch(
            new GlobalState\Captured(
                $this->telemetryInfo(),
                $snapshot
            )
        );
    }

    public function globalStateModified(Snapshot $snapshotBefore, Snapshot $snapshotAfter, string $diff): void
    {
        $this->dispatcher->dispatch(
            new GlobalState\Modified(
                $this->telemetryInfo(),
                $snapshotBefore,
                $snapshotAfter,
                $diff
            )
        );
    }

    public function globalStateRestored(Snapshot $snapshot): void
    {
        $this->dispatcher->dispatch(
            new GlobalState\Restored(
                $this->telemetryInfo(),
                $snapshot
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

    public function testOutputPrinted(Code\Test $test, string $output): void
    {
        $this->dispatcher->dispatch(
            new Test\OutputPrinted(
                $this->telemetryInfo(),
                $test,
                $output
            )
        );
    }

    public function testPassed(Code\Test $test): void
    {
        $this->dispatcher->dispatch(
            new Test\Passed(
                $this->telemetryInfo(),
                $test
            )
        );
    }

    public function testPassedWithWarning(Code\Test $test, Throwable $throwable): void
    {
        $this->dispatcher->dispatch(
            new Test\PassedWithWarning(
                $this->telemetryInfo(),
                $test,
                $throwable
            )
        );
    }

    public function testConsideredRisky(Code\Test $test, Throwable $throwable): void
    {
        $this->dispatcher->dispatch(
            new Test\ConsideredRisky(
                $this->telemetryInfo(),
                $test,
                $throwable
            )
        );
    }

    public function testAborted(Code\Test $test, Throwable $throwable): void
    {
        $this->dispatcher->dispatch(
            new Test\Aborted(
                $this->telemetryInfo(),
                $test,
                $throwable
            )
        );
    }

    public function testSkipped(Code\Test $test, ?Throwable $throwable, string $message): void
    {
        $this->dispatcher->dispatch(
            new Test\Skipped(
                $this->telemetryInfo(),
                $test,
                $throwable,
                $message
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

    public function testUsedDeprecatedPhpunitFeature(Code\Test $test, string $message): void
    {
        $this->dispatcher->dispatch(
            new Test\DeprecatedPhpunitFeatureUsed(
                $this->telemetryInfo(),
                $test,
                $message
            )
        );
    }

    public function testUsedDeprecatedPhpFeature(Code\Test $test, string $message, string $file, int $line): void
    {
        $this->dispatcher->dispatch(
            new Test\DeprecatedPhpFeatureUsed(
                $this->telemetryInfo(),
                $test,
                $message,
                $file,
                $line
            )
        );
    }

    public function testUsedDeprecatedFeature(Code\Test $test, string $message, string $file, int $line): void
    {
        $this->dispatcher->dispatch(
            new Test\DeprecatedFeatureUsed(
                $this->telemetryInfo(),
                $test,
                $message,
                $file,
                $line
            )
        );
    }

    /**
     * @psalm-param class-string $className
     */
    public function testMockObjectCreated(string $className): void
    {
        $this->dispatcher->dispatch(
            new TestDouble\MockObjectCreated(
                $this->telemetryInfo(),
                $className
            )
        );
    }

    /**
     * @psalm-param trait-string $traitName
     */
    public function testMockObjectCreatedForTrait(string $traitName): void
    {
        $this->dispatcher->dispatch(
            new TestDouble\MockObjectCreatedForTrait(
                $this->telemetryInfo(),
                $traitName
            )
        );
    }

    /**
     * @psalm-param class-string $className
     */
    public function testMockObjectCreatedForAbstractClass(string $className): void
    {
        $this->dispatcher->dispatch(
            new TestDouble\MockObjectCreatedForAbstractClass(
                $this->telemetryInfo(),
                $className
            )
        );
    }

    /**
     * @psalm-param class-string $originalClassName
     * @psalm-param class-string $mockClassName
     */
    public function testMockObjectCreatedFromWsdl(string $wsdlFile, string $originalClassName, string $mockClassName, array $methods, bool $callOriginalConstructor, array $options): void
    {
        $this->dispatcher->dispatch(
            new TestDouble\MockObjectCreatedFromWsdl(
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
    public function testPartialMockObjectCreated(string $className, string ...$methodNames): void
    {
        $this->dispatcher->dispatch(
            new TestDouble\PartialMockObjectCreated(
                $this->telemetryInfo(),
                $className,
                ...$methodNames
            )
        );
    }

    /**
     * @psalm-param class-string $className
     */
    public function testTestProxyCreated(string $className, array $constructorArguments): void
    {
        $this->dispatcher->dispatch(
            new TestDouble\TestProxyCreated(
                $this->telemetryInfo(),
                $className,
                $constructorArguments
            )
        );
    }

    /**
     * @psalm-param class-string $className
     */
    public function testTestStubCreated(string $className): void
    {
        $this->dispatcher->dispatch(
            new TestDouble\TestStubCreated(
                $this->telemetryInfo(),
                $className
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

    public function testSuiteStarted(TestSuite $testSuite): void
    {
        if (!$this->testExecutionStarted) {
            $this->dispatcher->dispatch(
                new TestRunner\ExecutionStarted(
                    $this->telemetryInfo(),
                    $testSuite
                )
            );

            $this->testExecutionStarted = true;
        }

        $this->dispatcher->dispatch(
            new TestSuiteStarted(
                $this->telemetryInfo(),
                $testSuite
            )
        );
    }

    public function testSuiteFinished(TestSuite $testSuite, Result $result): void
    {
        $this->dispatcher->dispatch(
            new TestSuiteFinished(
                $this->telemetryInfo(),
                $testSuite,
                $result,
            )
        );
    }

    private function telemetryInfo(): Telemetry\Info
    {
        $current = $this->system->snapshot();

        $info = new Telemetry\Info(
            $current,
            $current->time()->duration($this->startSnapshot->time()),
            $current->memoryUsage()->diff($this->startSnapshot->memoryUsage()),
            $current->time()->duration($this->previousSnapshot->time()),
            $current->memoryUsage()->diff($this->previousSnapshot->memoryUsage())
        );

        $this->previousSnapshot = $current;

        return $info;
    }
}
