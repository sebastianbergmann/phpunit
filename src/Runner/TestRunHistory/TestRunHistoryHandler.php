<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestRunHistory;

use function round;
use PHPUnit\Event\Event;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\MarkedIncomplete;
use PHPUnit\Event\Test\Passed;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Framework\InvalidArgumentException;
use PHPUnit\Framework\TestStatus\TestStatus;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestRunHistoryHandler
{
    private readonly TestRunHistory $testRunHistory;
    private bool $pruneOnPersist;
    private ?HRTime $time        = null;
    private bool $testWasSkipped = false;
    private int $testSuite       = 0;

    /**
     * A status loaded from a previous run must always be replaced by the
     * current run's first result for a test; after that, only a more
     * important status may replace it, and a pass must not clear a defect
     * recorded by an earlier repetition of the same test.
     *
     * @var array<string, TestStatus>
     */
    private array $statusesRecordedInThisRun = [];

    public function __construct(TestRunHistory $testRunHistory, Facade $facade, bool $pruneOnPersist)
    {
        $this->testRunHistory = $testRunHistory;
        $this->pruneOnPersist = $pruneOnPersist;

        $this->registerSubscribers($facade);
    }

    public function testSuiteStarted(): void
    {
        $this->testSuite++;
    }

    public function testSuiteFinished(): void
    {
        $this->testSuite--;

        if ($this->testSuite !== 0) {
            return;
        }

        if ($this->pruneOnPersist) {
            $this->testRunHistory->persistAndPrune();
        } else {
            $this->testRunHistory->persist();
        }
    }

    public function testRunnerExecutionAborted(): void
    {
        // An aborted run did not execute every test that exists, so pruning
        // would drop the entries of tests that were never reached
        $this->pruneOnPersist = false;
    }

    public function testPrepared(Prepared $event): void
    {
        $this->time           = $event->telemetryInfo()->time();
        $this->testWasSkipped = false;
    }

    public function testMarkedIncomplete(MarkedIncomplete $event): void
    {
        $this->recordStatus(
            TestRunHistoryId::fromTest($event->test()),
            TestStatus::incomplete($event->throwable()->message()),
        );
    }

    public function testConsideredRisky(ConsideredRisky $event): void
    {
        $this->recordStatus(
            TestRunHistoryId::fromTest($event->test()),
            TestStatus::risky($event->message()),
        );
    }

    public function testErrored(Errored $event): void
    {
        $this->recordStatus(
            TestRunHistoryId::fromTest($event->test()),
            TestStatus::error($event->throwable()->message()),
        );
    }

    public function testFailed(Failed $event): void
    {
        $this->recordStatus(
            TestRunHistoryId::fromTest($event->test()),
            TestStatus::failure($event->throwable()->message()),
        );
    }

    public function testPassed(Passed $event): void
    {
        $id = TestRunHistoryId::fromTest($event->test());

        if (isset($this->statusesRecordedInThisRun[$id->asString()])) {
            return;
        }

        $this->testRunHistory->remove($id);
    }

    public function testSkipped(Skipped $event): void
    {
        $this->recordStatus(
            TestRunHistoryId::fromTest($event->test()),
            TestStatus::skipped($event->message()),
        );

        $this->testWasSkipped = true;
    }

    /**
     * @throws \PHPUnit\Event\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testFinished(Finished $event): void
    {
        // The duration of a skipped test measures the time to reach the skip,
        // not the cost of running the test; it must not replace a duration
        // that was measured when the test actually ran
        if (!$this->testWasSkipped) {
            $this->testRunHistory->setTime(TestRunHistoryId::fromTest($event->test()), $this->duration($event));
        }

        $this->time           = null;
        $this->testWasSkipped = false;
    }

    private function recordStatus(TestRunHistoryId $id, TestStatus $status): void
    {
        $key = $id->asString();

        if (isset($this->statusesRecordedInThisRun[$key]) &&
            !$status->isMoreImportantThan($this->statusesRecordedInThisRun[$key])) {
            return;
        }

        $this->statusesRecordedInThisRun[$key] = $status;

        $this->testRunHistory->setStatus($id, $status);
    }

    /**
     * @throws \PHPUnit\Event\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    private function duration(Event $event): float
    {
        if ($this->time === null) {
            return 0.0;
        }

        return round($event->telemetryInfo()->time()->duration($this->time)->asFloat(), 3);
    }

    private function registerSubscribers(Facade $facade): void
    {
        $facade->registerSubscribers(
            new TestSuiteStartedSubscriber($this),
            new TestSuiteFinishedSubscriber($this),
            new TestPreparedSubscriber($this),
            new TestMarkedIncompleteSubscriber($this),
            new TestConsideredRiskySubscriber($this),
            new TestErroredSubscriber($this),
            new TestFailedSubscriber($this),
            new TestPassedSubscriber($this),
            new TestSkippedSubscriber($this),
            new TestFinishedSubscriber($this),
            new TestRunnerExecutionAbortedSubscriber($this),
        );
    }
}
