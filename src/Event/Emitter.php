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

use PHPUnit\Event\Telemetry\Info;
use PHPUnit\Event\Telemetry\Snapshot;
use PHPUnit\Event\Telemetry\System;

final class Emitter
{
    private Dispatcher $dispatcher;

    private System $system;

    private Snapshot $startSnapshot;

    private Snapshot $previousSnapshot;

    public function __construct(Dispatcher $dispatcher, System $system)
    {
        $this->dispatcher = $dispatcher;
        $this->system     = $system;

        $this->startSnapshot    = $system->snapshot();
        $this->previousSnapshot = $system->snapshot();
    }

    public function applicationConfigured(): void
    {
        $this->dispatcher->dispatch(new Application\Configured($this->getTelemetryInfo()));
    }

    public function applicationStarted(): void
    {
        $this->dispatcher->dispatch(new Application\Started($this->getTelemetryInfo()));
    }

    public function assertionMade(): void
    {
        $this->dispatcher->dispatch(new Assertion\Made($this->getTelemetryInfo()));
    }

    public function bootstrapFinished(): void
    {
        $this->dispatcher->dispatch(new Bootstrap\Finished($this->getTelemetryInfo()));
    }

    public function comparatorRegistered(): void
    {
        $this->dispatcher->dispatch(new Comparator\Registered($this->getTelemetryInfo()));
    }

    public function extensionLoaded(): void
    {
        $this->dispatcher->dispatch(new Extension\Loaded($this->getTelemetryInfo()));
    }

    public function globalStateCaptured(): void
    {
        $this->dispatcher->dispatch(new GlobalState\Captured($this->getTelemetryInfo()));
    }

    public function globalStateModified(): void
    {
        $this->dispatcher->dispatch(new GlobalState\Modified($this->getTelemetryInfo()));
    }

    public function globalStateRestored(): void
    {
        $this->dispatcher->dispatch(new GlobalState\Restored($this->getTelemetryInfo()));
    }

    public function testRunConfigured(): void
    {
        $this->dispatcher->dispatch(new Test\RunConfigured($this->getTelemetryInfo()));
    }

    public function testRunErrored(): void
    {
        $this->dispatcher->dispatch(new Test\RunErrored($this->getTelemetryInfo()));
    }

    public function testRunFailed(): void
    {
        $this->dispatcher->dispatch(new Test\RunFailed($this->getTelemetryInfo()));
    }

    public function testRunFinished(): void
    {
        $this->dispatcher->dispatch(new Test\RunFinished($this->getTelemetryInfo()));
    }

    public function testRunPassed(): void
    {
        $this->dispatcher->dispatch(new Test\RunPassed($this->getTelemetryInfo()));
    }

    public function testRunRisky(): void
    {
        $this->dispatcher->dispatch(new Test\RunRisky($this->getTelemetryInfo()));
    }

    public function testRunSkippedByDataProvider(): void
    {
        $this->dispatcher->dispatch(new Test\RunSkippedByDataProvider($this->getTelemetryInfo()));
    }

    public function testRunSkippedIncomplete(): void
    {
        $this->dispatcher->dispatch(new Test\RunSkippedIncomplete($this->getTelemetryInfo()));
    }

    public function testRunSkippedWithFailedRequirements(): void
    {
        $this->dispatcher->dispatch(new Test\RunSkippedWithFailedRequirements($this->getTelemetryInfo()));
    }

    public function testRunSkippedWithWarning(): void
    {
        $this->dispatcher->dispatch(new Test\RunSkippedWithWarning($this->getTelemetryInfo()));
    }

    public function testRunStarted(): void
    {
        $this->dispatcher->dispatch(new Test\RunStarted($this->getTelemetryInfo()));
    }

    public function testSetUpFinished(): void
    {
        $this->dispatcher->dispatch(new Test\SetUpFinished($this->getTelemetryInfo()));
    }

    public function testTearDownFinished(): void
    {
        $this->dispatcher->dispatch(new Test\TearDownFinished($this->getTelemetryInfo()));
    }

    public function testCaseAfterClassFinished(): void
    {
        $this->dispatcher->dispatch(new TestCase\AfterClassFinished($this->getTelemetryInfo()));
    }

    public function testCaseBeforeClassFinished(): void
    {
        $this->dispatcher->dispatch(new TestCase\BeforeClassFinished($this->getTelemetryInfo()));
    }

    public function testCaseSetUpBeforeClassFinished(): void
    {
        $this->dispatcher->dispatch(new TestCase\SetUpBeforeClassFinished($this->getTelemetryInfo()));
    }

    public function testCaseSetUpFinished(): void
    {
        $this->dispatcher->dispatch(new TestCase\SetUpFinished($this->getTelemetryInfo()));
    }

    public function testCaseTearDownAfterClassFinished(): void
    {
        $this->dispatcher->dispatch(new TestCase\TearDownAfterClassFinished($this->getTelemetryInfo()));
    }

    public function testDoubleMockCreated(): void
    {
        $this->dispatcher->dispatch(new TestDouble\MockCreated($this->getTelemetryInfo()));
    }

    public function testDoubleMockForTraitCreated(): void
    {
        $this->dispatcher->dispatch(new TestDouble\MockForTraitCreated($this->getTelemetryInfo()));
    }

    public function testDoublePartialMockCreated(): void
    {
        $this->dispatcher->dispatch(new TestDouble\PartialMockCreated($this->getTelemetryInfo()));
    }

    public function testDoubleProphecyCreated(): void
    {
        $this->dispatcher->dispatch(new TestDouble\ProphecyCreated($this->getTelemetryInfo()));
    }

    public function testDoubleTestProxyCreated(): void
    {
        $this->dispatcher->dispatch(new TestDouble\TestProxyCreated($this->getTelemetryInfo()));
    }

    public function testSuiteAfterClassFinished(): void
    {
        $this->dispatcher->dispatch(new TestSuite\AfterClassFinished($this->getTelemetryInfo()));
    }

    public function testSuiteBeforeClassFinished(): void
    {
        $this->dispatcher->dispatch(new TestSuite\BeforeClassFinished($this->getTelemetryInfo()));
    }

    public function testSuiteConfigured(): void
    {
        $this->dispatcher->dispatch(new TestSuite\Configured($this->getTelemetryInfo()));
    }

    public function testSuiteLoaded(): void
    {
        $this->dispatcher->dispatch(new TestSuite\Loaded($this->getTelemetryInfo()));
    }

    public function testSuiteRunFinished(): void
    {
        $this->dispatcher->dispatch(new TestSuite\RunFinished($this->getTelemetryInfo()));
    }

    public function testSuiteRunStarted(): void
    {
        $this->dispatcher->dispatch(new TestSuite\RunStarted($this->getTelemetryInfo()));
    }

    public function testSuiteSorted(): void
    {
        $this->dispatcher->dispatch(new TestSuite\Sorted($this->getTelemetryInfo()));
    }

    private function getTelemetryInfo(): Info
    {
        $current = $this->system->snapshot();

        $info = new Info(
            $current,
            $current->time()->diff($this->startSnapshot->time()),
            $current->memoryUsage()->diff($this->startSnapshot->memoryUsage()),
            $current->time()->diff($this->previousSnapshot->time()),
            $current->memoryUsage()->diff($this->previousSnapshot->memoryUsage())
        );

        $this->previousSnapshot = $current;

        return $info;
    }
}
