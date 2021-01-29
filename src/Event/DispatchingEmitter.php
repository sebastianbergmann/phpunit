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

use PHPUnit\Event\TestSuite\Info;
use PHPUnit\Framework\Constraint;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestSuite as FrameworkTestSuite;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\GlobalState\Snapshot;

final class DispatchingEmitter implements Emitter
{
    private Dispatcher $dispatcher;

    private Telemetry\System $system;

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
        $this->dispatcher->dispatch(new TestRunner\Started(
            $this->telemetryInfo(),
            new TestRunner\Runtime()
        ));
    }

    public function testRunnerFinished(): void
    {
        $this->dispatcher->dispatch(new TestRunner\Finished($this->telemetryInfo()));
    }

    public function assertionMade($value, Constraint\Constraint $constraint, string $message, bool $hasFailed): void
    {
        $this->dispatcher->dispatch(new Assertion\Made(
            $this->telemetryInfo(),
            $value,
            $constraint,
            $message,
            $hasFailed
        ));
    }

    public function bootstrapFinished(string $filename): void
    {
        $this->dispatcher->dispatch(new Bootstrap\Finished(
            $this->telemetryInfo(),
            $filename
        ));
    }

    public function comparatorRegistered(string $className): void
    {
        $this->dispatcher->dispatch(new Comparator\Registered(
            $this->telemetryInfo(),
            $className
        ));
    }

    public function extensionLoaded(string $name, string $version): void
    {
        $this->dispatcher->dispatch(new Extension\Loaded(
            $this->telemetryInfo(),
            $name,
            $version
        ));
    }

    public function globalStateCaptured(Snapshot $snapshot): void
    {
        $this->dispatcher->dispatch(new GlobalState\Captured(
            $this->telemetryInfo(),
            $snapshot
        ));
    }

    public function globalStateModified(Snapshot $snapshotBefore, Snapshot $snapshotAfter, string $message): void
    {
        $this->dispatcher->dispatch(new GlobalState\Modified(
            $this->telemetryInfo(),
            $snapshotBefore,
            $snapshotAfter,
            $message
        ));
    }

    public function globalStateRestored(Snapshot $snapshot): void
    {
        $this->dispatcher->dispatch(new GlobalState\Restored(
            $this->telemetryInfo(),
            $snapshot
        ));
    }

    public function testErrored(Code\Test $test, string $message): void
    {
        $this->dispatcher->dispatch(new Test\Errored(
            $this->telemetryInfo(),
            $test,
            $message
        ));
    }

    public function testFailed(Code\Test $test, string $message): void
    {
        $this->dispatcher->dispatch(new Test\Failed(
            $this->telemetryInfo(),
            $test,
            $message
        ));
    }

    public function testFinished(Code\Test $test): void
    {
        $this->dispatcher->dispatch(new Test\Finished(
            $this->telemetryInfo(),
            $test
        ));
    }

    public function testPassed(Code\Test $test): void
    {
        $this->dispatcher->dispatch(new Test\Passed(
            $this->telemetryInfo(),
            $test
        ));
    }

    public function testPassedWithWarning(Code\Test $test, string $message): void
    {
        $this->dispatcher->dispatch(new Test\PassedWithWarning(
            $this->telemetryInfo(),
            $test,
            $message
        ));
    }

    public function testPassedButRisky(Code\Test $test, string $message): void
    {
        $this->dispatcher->dispatch(new Test\PassedButRisky(
            $this->telemetryInfo(),
            $test,
            $message
        ));
    }

    public function testSkippedByDataProvider(Code\ClassMethod $testMethod, string $message): void
    {
        $this->dispatcher->dispatch(new Test\SkippedByDataProvider(
            $this->telemetryInfo(),
            $testMethod,
            $message
        ));
    }

    public function testAbortedWithMessage(Code\Test $test, string $message): void
    {
        $this->dispatcher->dispatch(new Test\AbortedWithMessage(
            $this->telemetryInfo(),
            $test,
            $message
        ));
    }

    public function testSkippedDueToUnsatisfiedRequirements(Code\ClassMethod $testMethod, string ...$missingRequirements): void
    {
        $this->dispatcher->dispatch(new Test\SkippedDueToUnsatisfiedRequirements(
            $this->telemetryInfo(),
            $testMethod,
            ...$missingRequirements
        ));
    }

    public function testSkippedWithMessage(Code\Test $test, string $message): void
    {
        $this->dispatcher->dispatch(new Test\SkippedWithMessage(
            $this->telemetryInfo(),
            $test,
            $message
        ));
    }

    public function testPrepared(Code\Test $test): void
    {
        $this->dispatcher->dispatch(new Test\Prepared(
            $this->telemetryInfo(),
            $test
        ));
    }

    public function testAfterTestMethodFinished(string $testClassName, Code\ClassMethod ...$calledMethods): void
    {
        $this->dispatcher->dispatch(new Test\AfterTestMethodFinished(
            $this->telemetryInfo(),
            $testClassName,
            ...$calledMethods
        ));
    }

    public function testAfterLastTestMethodFinished(string $testClassName, Code\ClassMethod ...$calledMethods): void
    {
        $this->dispatcher->dispatch(new Test\AfterLastTestMethodFinished(
            $this->telemetryInfo(),
            $testClassName,
            ...$calledMethods
        ));
    }

    public function testBeforeFirstTestMethodCalled(string $testClassName, Code\ClassMethod $calledMethod): void
    {
        $this->dispatcher->dispatch(new Test\BeforeFirstTestMethodCalled(
            $this->telemetryInfo(),
            $testClassName,
            $calledMethod
        ));
    }

    public function testBeforeFirstTestMethodFinished(string $testClassName, Code\ClassMethod ...$calledMethods): void
    {
        $this->dispatcher->dispatch(new Test\BeforeFirstTestMethodFinished(
            $this->telemetryInfo(),
            $testClassName,
            ...$calledMethods
        ));
    }

    public function testBeforeTestMethodCalled(string $testClassName, Code\ClassMethod $calledMethod): void
    {
        $this->dispatcher->dispatch(new Test\BeforeTestMethodCalled(
            $this->telemetryInfo(),
            $testClassName,
            $calledMethod
        ));
    }

    public function testBeforeTestMethodFinished(string $testClassName, Code\ClassMethod ...$calledMethods): void
    {
        $this->dispatcher->dispatch(new Test\BeforeTestMethodFinished(
            $this->telemetryInfo(),
            $testClassName,
            ...$calledMethods
        ));
    }

    public function testPreConditionCalled(string $testClassName, Code\ClassMethod $calledMethod): void
    {
        $this->dispatcher->dispatch(new Test\PreConditionCalled(
            $this->telemetryInfo(),
            $testClassName,
            $calledMethod
        ));
    }

    public function testPreConditionFinished(string $testClassName, Code\ClassMethod ...$calledMethods): void
    {
        $this->dispatcher->dispatch(new Test\PreConditionFinished(
            $this->telemetryInfo(),
            $testClassName,
            ...$calledMethods
        ));
    }

    public function testPostConditionCalled(string $testClassName, Code\ClassMethod $calledMethod): void
    {
        $this->dispatcher->dispatch(new Test\PostConditionCalled(
            $this->telemetryInfo(),
            $testClassName,
            $calledMethod
        ));
    }

    public function testPostConditionFinished(string $testClassName, Code\ClassMethod ...$calledMethods): void
    {
        $this->dispatcher->dispatch(new Test\PostConditionFinished(
            $this->telemetryInfo(),
            $testClassName,
            ...$calledMethods
        ));
    }

    public function testAfterTestMethodCalled(string $testClassName, Code\ClassMethod $calledMethod): void
    {
        $this->dispatcher->dispatch(new Test\AfterTestMethodCalled(
            $this->telemetryInfo(),
            $testClassName,
            $calledMethod
        ));
    }

    public function testAfterLastTestMethodCalled(string $testClassName, Code\ClassMethod $calledMethod): void
    {
        $this->dispatcher->dispatch(new Test\AfterLastTestMethodCalled(
            $this->telemetryInfo(),
            $testClassName,
            $calledMethod
        ));
    }

    public function testMockObjectCreated(string $className): void
    {
        $this->dispatcher->dispatch(new TestDouble\MockObjectCreated(
            $this->telemetryInfo(),
            $className
        ));
    }

    public function testMockObjectCreatedForTrait(string $traitName): void
    {
        $this->dispatcher->dispatch(new TestDouble\MockObjectCreatedForTrait(
            $this->telemetryInfo(),
            $traitName
        ));
    }

    public function testMockObjectCreatedForAbstractClass(string $className): void
    {
        $this->dispatcher->dispatch(new TestDouble\MockObjectCreatedForAbstractClass(
            $this->telemetryInfo(),
            $className
        ));
    }

    public function testMockObjectCreatedFromWsdl(
        string $wsdlFile,
        string $originalClassName,
        string $mockClassName,
        array $methods,
        bool $callOriginalConstructor,
        array $options
    ): void {
        $this->dispatcher->dispatch(new TestDouble\MockObjectCreatedFromWsdl(
            $this->telemetryInfo(),
            $wsdlFile,
            $originalClassName,
            $mockClassName,
            $methods,
            $callOriginalConstructor,
            $options
        ));
    }

    public function testPartialMockObjectCreated(string $className, string ...$methodNames): void
    {
        $this->dispatcher->dispatch(new TestDouble\PartialMockObjectCreated(
            $this->telemetryInfo(),
            $className,
            ...$methodNames
        ));
    }

    /**
     * @psalm-param class-string $className
     */
    public function testTestProxyCreated(string $className, array $constructorArguments): void
    {
        $this->dispatcher->dispatch(new TestDouble\TestProxyCreated(
            $this->telemetryInfo(),
            $className,
            $constructorArguments
        ));
    }

    /**
     * @psalm-param class-string $className
     */
    public function testTestStubCreated(string $className): void
    {
        $this->dispatcher->dispatch(new TestDouble\TestStubCreated(
            $this->telemetryInfo(),
            $className
        ));
    }

    public function testSuiteLoaded(FrameworkTestSuite $testSuite): void
    {
        $this->dispatcher->dispatch(new TestSuite\Loaded(
            $this->telemetryInfo(),
            Info::fromTestSuite($testSuite)
        ));
    }

    public function testSuiteSorted(int $executionOrder, int $executionOrderDefects, bool $resolveDependencies): void
    {
        $this->dispatcher->dispatch(new TestSuite\Sorted(
            $this->telemetryInfo(),
            $executionOrder,
            $executionOrderDefects,
            $resolveDependencies
        ));
    }

    public function testSuiteStarted(string $name): void
    {
        $this->dispatcher->dispatch(new TestSuite\Started(
            $this->telemetryInfo(),
            $name
        ));
    }

    public function testSuiteFinished(string $testSuiteName, TestResult $result, ?CodeCoverage $codeCoverage): void
    {
        $this->dispatcher->dispatch(new TestSuite\Finished(
            $this->telemetryInfo(),
            $testSuiteName,
            (new TestResultMapper())->map($result),
            $codeCoverage
        ));
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
