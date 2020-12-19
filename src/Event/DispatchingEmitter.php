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

    public function testRunErrored(): void
    {
        $this->dispatcher->dispatch(new Test\RunErrored($this->telemetryInfo()));
    }

    public function testRunFailed(): void
    {
        $this->dispatcher->dispatch(new Test\RunFailed($this->telemetryInfo()));
    }

    public function testRunFinished(): void
    {
        $this->dispatcher->dispatch(new Test\RunFinished($this->telemetryInfo()));
    }

    public function testRunPassed(): void
    {
        $this->dispatcher->dispatch(new Test\RunPassed($this->telemetryInfo()));
    }

    public function testRunRisky(): void
    {
        $this->dispatcher->dispatch(new Test\RunRisky($this->telemetryInfo()));
    }

    public function testRunSkippedByDataProvider(): void
    {
        $this->dispatcher->dispatch(new Test\RunSkippedByDataProvider($this->telemetryInfo()));
    }

    public function testRunSkippedIncomplete(): void
    {
        $this->dispatcher->dispatch(new Test\RunSkippedIncomplete($this->telemetryInfo()));
    }

    public function testRunSkippedWithFailedRequirements(): void
    {
        $this->dispatcher->dispatch(new Test\RunSkippedWithFailedRequirements($this->telemetryInfo()));
    }

    public function testRunSkippedWithWarning(): void
    {
        $this->dispatcher->dispatch(new Test\RunSkippedWithWarning($this->telemetryInfo()));
    }

    public function testRunStarted(): void
    {
        $this->dispatcher->dispatch(new Test\RunStarted($this->telemetryInfo()));
    }

    public function testSetUpFinished(): void
    {
        $this->dispatcher->dispatch(new Test\SetUpFinished($this->telemetryInfo()));
    }

    public function testTearDownFinished(): void
    {
        $this->dispatcher->dispatch(new Test\TearDownFinished($this->telemetryInfo()));
    }

    public function testCaseAfterClassFinished(): void
    {
        $this->dispatcher->dispatch(new TestCase\AfterClassFinished($this->telemetryInfo()));
    }

    public function testCaseBeforeClassFinished(): void
    {
        $this->dispatcher->dispatch(new TestCase\BeforeClassFinished($this->telemetryInfo()));
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

    public function testDoubleTestProxyCreated(): void
    {
        $this->dispatcher->dispatch(new TestDouble\TestProxyCreated($this->telemetryInfo()));
    }

    public function testSuiteAfterClassFinished(): void
    {
        $this->dispatcher->dispatch(new TestSuite\AfterClassFinished($this->telemetryInfo()));
    }

    public function testSuiteBeforeClassFinished(): void
    {
        $this->dispatcher->dispatch(new TestSuite\BeforeClassFinished($this->telemetryInfo()));
    }

    public function testSuiteConfigured(): void
    {
        $this->dispatcher->dispatch(new TestSuite\Configured($this->telemetryInfo()));
    }

    public function testSuiteLoaded(): void
    {
        $this->dispatcher->dispatch(new TestSuite\Loaded($this->telemetryInfo()));
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
