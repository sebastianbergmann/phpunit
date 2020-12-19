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

use PHPUnit\Framework\Constraint;
use PHPUnit\Framework\TestSuite as FrameworkTestSuite;
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

    public function applicationConfigured(): void
    {
        $this->dispatcher->dispatch(new Application\Configured($this->telemetryInfo()));
    }

    public function applicationStarted(): void
    {
        $this->dispatcher->dispatch(new Application\Started(
            $this->telemetryInfo(),
            new Application\Runtime()
        ));
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

    public function testRunConfigured(): void
    {
        $this->dispatcher->dispatch(new Test\RunConfigured($this->telemetryInfo()));
    }

    public function testErrored(): void
    {
        $this->dispatcher->dispatch(new Test\Errored($this->telemetryInfo()));
    }

    public function testFailed(): void
    {
        $this->dispatcher->dispatch(new Test\Failed($this->telemetryInfo()));
    }

    public function testFinished(): void
    {
        $this->dispatcher->dispatch(new Test\Finished($this->telemetryInfo()));
    }

    public function testPassed(): void
    {
        $this->dispatcher->dispatch(new Test\Passed($this->telemetryInfo()));
    }

    public function testPassedButRisky(): void
    {
        $this->dispatcher->dispatch(new Test\PassedButRisky($this->telemetryInfo()));
    }

    public function testSkippedByDataProvider(): void
    {
        $this->dispatcher->dispatch(new Test\SkippedByDataProvider($this->telemetryInfo()));
    }

    public function testSkippedIncomplete(): void
    {
        $this->dispatcher->dispatch(new Test\SkippedIncomplete($this->telemetryInfo()));
    }

    public function testSkippedDueToUnsatisfiedRequirements(): void
    {
        $this->dispatcher->dispatch(new Test\SkippedDueToUnsatisfiedRequirements($this->telemetryInfo()));
    }

    public function testSkippedWithMessage(): void
    {
        $this->dispatcher->dispatch(new Test\SkippedWithMessage($this->telemetryInfo()));
    }

    public function testPrepared(): void
    {
        $this->dispatcher->dispatch(new Test\Prepared($this->telemetryInfo()));
    }

    public function testSetUpFinished(): void
    {
        $this->dispatcher->dispatch(new Test\SetUpFinished($this->telemetryInfo()));
    }

    public function testAfterTestMethodFinished(): void
    {
        $this->dispatcher->dispatch(new Test\AfterTestMethodFinished($this->telemetryInfo()));
    }

    public function testAfterLastTestMethodFinished(): void
    {
        $this->dispatcher->dispatch(new Test\AfterLastTestMethodFinished($this->telemetryInfo()));
    }

    public function testBeforeFirstTestMethodCalled(): void
    {
        $this->dispatcher->dispatch(new Test\BeforeFirstTestMethodCalled($this->telemetryInfo()));
    }

    public function testBeforeFirstTestMethodFinished(): void
    {
        $this->dispatcher->dispatch(new Test\BeforeFirstTestMethodFinished($this->telemetryInfo()));
    }

    public function testCaseSetUpBeforeClassFinished(): void
    {
        $this->dispatcher->dispatch(new TestCase\SetUpBeforeClassFinished($this->telemetryInfo()));
    }

    public function testCaseSetUpFinished(): void
    {
        $this->dispatcher->dispatch(new TestCase\SetUpFinished($this->telemetryInfo()));
    }

    public function testCaseTearDownAfterClassFinished(): void
    {
        $this->dispatcher->dispatch(new TestCase\TearDownAfterClassFinished($this->telemetryInfo()));
    }

    public function testDoubleMockCreated(string $className): void
    {
        $this->dispatcher->dispatch(new TestDouble\MockCreated(
            $this->telemetryInfo(),
            $className
        ));
    }

    public function testDoubleMockForTraitCreated(string $traitName): void
    {
        $this->dispatcher->dispatch(new TestDouble\MockForTraitCreated(
            $this->telemetryInfo(),
            $traitName
        ));
    }

    public function testDoublePartialMockCreated(string $className, string ...$methodNames): void
    {
        $this->dispatcher->dispatch(new TestDouble\PartialMockCreated(
            $this->telemetryInfo(),
            $className,
            ...$methodNames
        ));
    }

    /**
     * @param class-string $className
     */
    public function testDoubleTestProxyCreated(string $className, array $constructorArguments): void
    {
        $this->dispatcher->dispatch(new TestDouble\TestProxyCreated(
            $this->telemetryInfo(),
            $className,
            $constructorArguments
        ));
    }

    public function testSuiteAfterClassFinished(): void
    {
        $this->dispatcher->dispatch(new TestSuite\AfterClassFinished($this->telemetryInfo()));
    }

    public function testSuiteLoaded(FrameworkTestSuite $testSuite): void
    {
        $this->dispatcher->dispatch(new TestSuite\Loaded(
            $this->telemetryInfo(),
            TestSuite\Info::fromTestSuite($testSuite)
        ));
    }

    public function testSuiteRunFinished(): void
    {
        $this->dispatcher->dispatch(new TestSuite\RunFinished($this->telemetryInfo()));
    }

    public function testSuiteRunStarted(): void
    {
        $this->dispatcher->dispatch(new TestSuite\RunStarted($this->telemetryInfo()));
    }

    public function testSuiteSorted(): void
    {
        $this->dispatcher->dispatch(new TestSuite\Sorted($this->telemetryInfo()));
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
