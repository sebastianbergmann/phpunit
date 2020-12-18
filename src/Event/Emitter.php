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

final class Emitter
{
    private Dispatcher $dispatcher;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function applicationConfigured(): void
    {
        $this->dispatcher->dispatch(new Application\Configured());
    }

    public function applicationStarted(): void
    {
        $this->dispatcher->dispatch(new Application\Started());
    }

    public function assertionMade(): void
    {
        $this->dispatcher->dispatch(new Assertion\Made());
    }

    public function bootstrapFinished(): void
    {
        $this->dispatcher->dispatch(new Bootstrap\Finished());
    }

    public function comparatorRegistered(): void
    {
        $this->dispatcher->dispatch(new Comparator\Registered());
    }

    public function extensionLoaded(): void
    {
        $this->dispatcher->dispatch(new Extension\Loaded());
    }

    public function globalStateCaptured(): void
    {
        $this->dispatcher->dispatch(new GlobalState\Captured());
    }

    public function globalStateModified(): void
    {
        $this->dispatcher->dispatch(new GlobalState\Modified());
    }

    public function globalStateRestored(): void
    {
        $this->dispatcher->dispatch(new GlobalState\Restored());
    }

    public function testRunConfigured(): void
    {
        $this->dispatcher->dispatch(new Test\RunConfigured());
    }

    public function testRunErrored(): void
    {
        $this->dispatcher->dispatch(new Test\RunErrored());
    }

    public function testRunFailed(): void
    {
        $this->dispatcher->dispatch(new Test\RunFailed());
    }

    public function testRunFinished(): void
    {
        $this->dispatcher->dispatch(new Test\RunFinished());
    }

    public function testRunPassed(): void
    {
        $this->dispatcher->dispatch(new Test\RunPassed());
    }

    public function testRunRisky(): void
    {
        $this->dispatcher->dispatch(new Test\RunRisky());
    }

    public function testRunSkippedByDataProvider(): void
    {
        $this->dispatcher->dispatch(new Test\RunSkippedByDataProvider());
    }

    public function testRunSkippedIncomplete(): void
    {
        $this->dispatcher->dispatch(new Test\RunSkippedIncomplete());
    }

    public function testRunSkippedWithFailedRequirements(): void
    {
        $this->dispatcher->dispatch(new Test\RunSkippedWithFailedRequirements());
    }

    public function testRunSkippedWithWarning(): void
    {
        $this->dispatcher->dispatch(new Test\RunSkippedWithWarning());
    }

    public function testRunStarted(): void
    {
        $this->dispatcher->dispatch(new Test\RunStarted());
    }

    public function testSetUpFinished(): void
    {
        $this->dispatcher->dispatch(new Test\SetUpFinished());
    }

    public function testTearDownFinished(): void
    {
        $this->dispatcher->dispatch(new Test\TearDownFinished());
    }

    public function testCaseAfterClassFinished(): void
    {
        $this->dispatcher->dispatch(new TestCase\AfterClassFinished());
    }

    public function testCaseBeforeClassFinished(): void
    {
        $this->dispatcher->dispatch(new TestCase\BeforeClassFinished());
    }

    public function testCaseSetUpBeforeClassFinished(): void
    {
        $this->dispatcher->dispatch(new TestCase\SetUpBeforeClassFinished());
    }

    public function testCaseSetUpFinished(): void
    {
        $this->dispatcher->dispatch(new TestCase\SetUpFinished());
    }

    public function testCaseTearDownAfterClassFinished(): void
    {
        $this->dispatcher->dispatch(new TestCase\TearDownAfterClassFinished());
    }

    public function testDoubleMockCreated(): void
    {
        $this->dispatcher->dispatch(new TestDouble\MockCreated());
    }

    public function testDoubleMockForTraitCreated(): void
    {
        $this->dispatcher->dispatch(new TestDouble\MockForTraitCreated());
    }

    public function testDoublePartialMockCreated(): void
    {
        $this->dispatcher->dispatch(new TestDouble\PartialMockCreated());
    }

    public function testDoubleProphecyCreated(): void
    {
        $this->dispatcher->dispatch(new TestDouble\ProphecyCreated());
    }

    public function testDoubleTestProxyCreated(): void
    {
        $this->dispatcher->dispatch(new TestDouble\TestProxyCreated());
    }

    public function testSuiteAfterClassFinished(): void
    {
        $this->dispatcher->dispatch(new TestSuite\AfterClassFinished());
    }

    public function testSuiteBeforeClassFinished(): void
    {
        $this->dispatcher->dispatch(new TestSuite\BeforeClassFinished());
    }

    public function testSuiteConfigured(): void
    {
        $this->dispatcher->dispatch(new TestSuite\Configured());
    }

    public function testSuiteLoaded(): void
    {
        $this->dispatcher->dispatch(new TestSuite\Loaded());
    }

    public function testSuiteRunFinished(): void
    {
        $this->dispatcher->dispatch(new TestSuite\RunFinished());
    }

    public function testSuiteRunStarted(): void
    {
        $this->dispatcher->dispatch(new TestSuite\RunStarted());
    }

    public function testSuiteSorted(): void
    {
        $this->dispatcher->dispatch(new TestSuite\Sorted());
    }
}
