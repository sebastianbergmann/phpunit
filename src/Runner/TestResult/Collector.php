<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestRunner\TestResult;

use PHPUnit\Event\EventFacadeIsSealedException;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\BeforeFirstTestMethodErrored;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\MarkedIncomplete;
use PHPUnit\Event\Test\PassedWithWarning;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\TestRunner\ExecutionStarted;
use PHPUnit\Event\UnknownSubscriberTypeException;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Collector
{
    private int $numberOfTests      = 0;
    private int $numberOfTestsRun   = 0;
    private int $numberOfAssertions = 0;
    private bool $prepared          = false;

    /**
     * @psalm-var list<Skipped>
     */
    private array $skippedTests = [];

    /**
     * @psalm-var list<MarkedIncomplete>
     */
    private array $incompleteTests = [];

    /**
     * @psalm-var array<string,list<ConsideredRisky>>
     */
    private array $riskyTests = [];

    /**
     * @psalm-var array<string,list<PassedWithWarning>>
     */
    private array $testsWithWarnings = [];

    /**
     * @psalm-var list<Failed>
     */
    private array $failedTests = [];

    /**
     * @psalm-var list<BeforeFirstTestMethodErrored|Errored>
     */
    private array $erroredTests = [];

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public function __construct()
    {
        Facade::registerSubscriber(new ExecutionStartedSubscriber($this));
        Facade::registerSubscriber(new BeforeTestClassMethodErroredSubscriber($this));
        Facade::registerSubscriber(new TestMarkedIncompleteSubscriber($this));
        Facade::registerSubscriber(new TestConsideredRiskySubscriber($this));
        Facade::registerSubscriber(new TestErroredSubscriber($this));
        Facade::registerSubscriber(new TestFailedSubscriber($this));
        Facade::registerSubscriber(new TestPassedWithWarningSubscriber($this));
        Facade::registerSubscriber(new TestSkippedSubscriber($this));
        Facade::registerSubscriber(new TestFinishedSubscriber($this));
        Facade::registerSubscriber(new TestPreparedSubscriber($this));
    }

    public function result(): TestResult
    {
        return new TestResult(
            $this->numberOfTests,
            $this->numberOfTestsRun,
            $this->numberOfAssertions,
            $this->erroredTests,
            $this->failedTests,
            $this->testsWithWarnings,
            $this->riskyTests,
            $this->skippedTests,
            $this->incompleteTests
        );
    }

    public function executionStarted(ExecutionStarted $event): void
    {
        $this->numberOfTests = $event->testSuite()->count();
    }

    public function beforeTestClassMethodErrored(BeforeFirstTestMethodErrored $event): void
    {
        $this->erroredTests[] = $event;

        $this->numberOfTestsRun++;
    }

    public function testPrepared(): void
    {
        $this->prepared = true;
    }

    public function testSkipped(Skipped $event): void
    {
        $this->skippedTests[] = $event;

        if (!$this->prepared) {
            $this->numberOfTestsRun++;
        }
    }

    public function testMarkedIncomplete(MarkedIncomplete $event): void
    {
        $this->incompleteTests[] = $event;
    }

    public function testConsideredRisky(ConsideredRisky $event): void
    {
        if (!isset($this->riskyTests[$event->test()->id()])) {
            $this->riskyTests[$event->test()->id()] = [];
        }

        $this->riskyTests[$event->test()->id()][] = $event;
    }

    public function testPassedWithWarning(PassedWithWarning $event): void
    {
        if (!isset($this->testsWithWarnings[$event->test()->id()])) {
            $this->testsWithWarnings[$event->test()->id()] = [];
        }

        $this->testsWithWarnings[$event->test()->id()][] = $event;
    }

    public function testFailed(Failed $event): void
    {
        $this->failedTests[] = $event;
    }

    public function testErrored(Errored $event): void
    {
        $this->erroredTests[] = $event;

        /*
         * @todo Eliminate this special case
         */
        if (str_contains($event->asString(), 'Test was run in child process and ended unexpectedly')) {
            return;
        }

        if (!$this->prepared) {
            $this->numberOfTestsRun++;
        }
    }

    public function testFinished(Finished $event): void
    {
        $this->numberOfAssertions += $event->numberOfAssertionsPerformed();

        $this->numberOfTestsRun++;

        $this->prepared = false;
    }
}
