<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Parallel;

use function assert;
use function clearstatcache;
use function hrtime;
use function is_file;
use function is_string;
use function putenv;
use function sort;
use function strlen;
use function substr;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;
use function unserialize;
use function usleep;
use PHPUnit\Event\Emitter;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Facade;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestRunner\ChildProcessResultProcessor;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\TestFixture\ParallelWorker\WorkerCrashesOnceTest;
use PHPUnit\TestFixture\ParallelWorker\WorkerDataProvidedTest;
use PHPUnit\TestFixture\ParallelWorker\WorkerFirstTest;
use PHPUnit\TestFixture\ParallelWorker\WorkerHaltedTest;
use PHPUnit\TestFixture\ParallelWorker\WorkerSecondTest;
use PHPUnit\TestFixture\ParallelWorker\WorkerSleepingTest;
use PHPUnit\TestRunner\TestResult\PassedTests;
use PHPUnit\Util\PHP\Job;
use PHPUnit\Util\PHP\JobRunner;
use ReflectionProperty;

#[CoversClass(WorkerPool::class)]
#[CoversClass(TestClassWorkUnit::class)]
#[CoversClass(CompletedWorkUnit::class)]
#[CoversClass(PersistentWorker::class)]
#[UsesClass(JobRunner::class)]
#[UsesClass(Job::class)]
#[Large]
final class WorkerPoolTest extends TestCase
{
    public function testRunsUnitsAcrossWorkersAndReportsEachAsCompleted(): void
    {
        $units = [
            new TestClassWorkUnit(0, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')]),
            new TestClassWorkUnit(1, WorkerSecondTest::class, [new WorkerSecondTest('testThatFails')]),
        ];

        $streamed = [];

        $completed = $this->execute($this->pool(2), $units, $streamed);

        $this->assertCount(2, $completed);

        foreach ($completed as $unit) {
            $this->assertFalse($unit->crashed());
            $this->assertNotSame('', $unit->serializedResult());
            $this->assertNotNull($unit->nonce());
        }

        $this->assertSame([0, 1], $this->indexesOf($completed));

        // Both units streamed the events of their finished tests while they
        // were still running.
        $this->assertArrayHasKey(0, $streamed);
        $this->assertArrayHasKey(1, $streamed);
    }

    public function testDispatchesTheUnitTheOrderedOutputWaitsForBeforeTheLongerOnesQueuedAheadOfIt(): void
    {
        // The scheduler queued the unit at index 1 first, because it is the
        // longer one; the unit at index 0 is what the ordered output waits
        // for, though, so it is dispatched first and its results are reported
        // while the other unit is still executing. With one worker, the
        // completion order is the dispatch order.
        $units = [
            new TestClassWorkUnit(1, WorkerSecondTest::class, [new WorkerSecondTest('testThatFails')]),
            new TestClassWorkUnit(0, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')]),
        ];

        $completed = $this->execute($this->pool(1), $units);

        $this->assertCount(2, $completed);
        $this->assertSame(0, $completed[0]->unit()->index());
        $this->assertSame(1, $completed[1]->unit()->index());
    }

    public function testReportsACrashedUnitWhenItsWorkerDies(): void
    {
        $units = [
            new TestClassWorkUnit(0, WorkerSecondTest::class, [new WorkerSecondTest('testThatKillsTheWorkerProcess')]),
        ];

        $completed = $this->execute($this->pool(1), $units);

        $this->assertCount(1, $completed);
        $this->assertTrue($completed[0]->crashed());
    }

    public function testBootsAFreshProcessForTheNextUnitOfAWorkerWhoseProcessDiedWhileRunningAUnitThatWasNotRetried(): void
    {
        // The only worker dies on the first unit, whose retry is vetoed; the
        // second unit runs on a fresh process of the same worker.
        $units = [
            new TestClassWorkUnit(0, WorkerSecondTest::class, [new WorkerSecondTest('testThatKillsTheWorkerProcess')]),
            new TestClassWorkUnit(1, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')]),
        ];

        $streamed = [];

        $completed = $this->execute(
            $this->pool(1),
            $units,
            $streamed,
            static function (WorkUnit $unit): bool
            {
                return false;
            },
        );

        $this->assertCount(2, $completed);
        $this->assertSame([0, 1], $this->indexesOf($completed));
        $this->assertTrue($completed[0]->crashed());
        $this->assertFalse($completed[1]->crashed());
    }

    public function testBootsAFreshProcessForTheUnitsOfALaterRunOfAWorkerWhoseProcessDiedInAnEarlierOne(): void
    {
        $pool = $this->pool(1);

        $pool->start();

        $completed = [];

        $onCompleted = static function (CompletedWorkUnit $unit) use (&$completed): void
        {
            $completed[] = $unit;
        };

        $onStreamedEvents = static function (WorkUnit $unit, EventCollection $events): void
        {
        };

        $onCrashedUnitRetry = static function (WorkUnit $unit): bool
        {
            return true;
        };

        try {
            // The units of the test suites of a run that is partitioned into
            // test suites are run one test suite after another, on the same
            // workers: the only worker dies in the first one, and its unit is
            // reported as crashed once its retry has crashed as well.
            $pool->run(
                [new TestClassWorkUnit(0, WorkerSecondTest::class, [new WorkerSecondTest('testThatKillsTheWorkerProcess')])],
                $onCompleted,
                $onStreamedEvents,
                $onCrashedUnitRetry,
            );

            $pool->run(
                [new TestClassWorkUnit(1, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')])],
                $onCompleted,
                $onStreamedEvents,
                $onCrashedUnitRetry,
            );
        } finally {
            $pool->stop();
        }

        $this->assertCount(2, $completed);
        $this->assertTrue($completed[0]->crashed());
        $this->assertFalse($completed[1]->crashed());
    }

    public function testReportsAUnitWhoseDataSetTheWorkerCannotProvideThroughItsEnvelopeAndKeepsRunning(): void
    {
        // The worker invokes the data provider again and does not find the
        // data set the parent process selected; it reports that through the
        // unit's result envelope, and stays alive for the units that follow.
        $test = new WorkerDataProvidedTest('testWithNamedDataSets');
        $test->setData('data set the worker does not know', [1]);

        $units = [
            new TestClassWorkUnit(0, WorkerDataProvidedTest::class, [$test]),
            new TestClassWorkUnit(1, WorkerSecondTest::class, [new WorkerSecondTest('testThatFails')]),
        ];

        $completed = $this->execute($this->pool(1), $units);

        $this->assertCount(2, $completed);

        $byIndex = [];

        foreach ($completed as $unit) {
            $byIndex[$unit->unit()->index()] = $unit;
        }

        $this->assertFalse($byIndex[0]->crashed());

        $envelope = unserialize(substr($byIndex[0]->serializedResult(), strlen((string) $byIndex[0]->nonce())));

        $this->assertIsObject($envelope);
        $this->assertStringContainsString('did not provide data set "data set the worker does not know"', $envelope->failure);

        // The unit that followed still ran on the same worker.
        $this->assertFalse($byIndex[1]->crashed());
    }

    public function testReportsAUnitThatCannotBeDispatchedAsCrashedAndRunsTheRemainingOnes(): void
    {
        $undescribable = new WorkerFirstTest('testStartsTheProcessLocalCounter');

        // A test case whose dependency input cannot be serialized cannot be
        // described for transport, so its unit cannot be dispatched to a
        // worker at all.
        $undescribable->setDependencyInput(
            [
                'WorkerFirstTest::testThatIsDependedUpon' => static function (): void
                {
                },
            ],
        );

        $units = [
            new TestClassWorkUnit(0, WorkerFirstTest::class, [$undescribable]),
            new TestClassWorkUnit(1, WorkerSecondTest::class, [new WorkerSecondTest('testThatFails')]),
        ];

        $completed = $this->execute($this->pool(1), $units);

        $this->assertSame([0, 1], $this->indexesOf($completed));

        $byIndex = [];

        foreach ($completed as $unit) {
            $byIndex[$unit->unit()->index()] = $unit;
        }

        $this->assertTrue($byIndex[0]->crashed());
        $this->assertStringContainsString('cannot be run in parallel', (string) $byIndex[0]->message());

        // One unit that cannot be dispatched must neither abort the run nor
        // starve the worker of the units that follow it.
        $this->assertFalse($byIndex[1]->crashed());
    }

    public function testReplacesAWorkerWithAFreshProcessAfterItHasCompletedTheConfiguredNumberOfUnits(): void
    {
        // The fixture test passes only in a process in which no test has run
        // before it: its process-local counter must start at 1. With one
        // worker that is replaced after every unit, all three units pass.
        $units = [
            new TestClassWorkUnit(0, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')]),
            new TestClassWorkUnit(1, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')]),
            new TestClassWorkUnit(2, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')]),
        ];

        $completed = $this->execute($this->pool(1, null, 1), $units);

        $this->assertCount(3, $completed);

        foreach ($completed as $unit) {
            $this->assertFalse($unit->crashed());
            $this->assertTrue($this->passedTestsOf($unit)->hasTestMethodPassed(WorkerFirstTest::class . '::testStartsTheProcessLocalCounter'));
        }
    }

    public function testReplacesAWorkerThatHasCompletedTheConfiguredNumberOfUnitsBeforeItRunsAUnitOfALaterRun(): void
    {
        $pool = $this->pool(1, null, 1);

        $pool->start();

        $completed = [];

        $onCompleted = static function (CompletedWorkUnit $unit) use (&$completed): void
        {
            $completed[] = $unit;
        };

        $onStreamedEvents = static function (WorkUnit $unit, EventCollection $events): void
        {
        };

        $onCrashedUnitRetry = static function (WorkUnit $unit): bool
        {
            return true;
        };

        try {
            // The units of the test suites of a run that is partitioned into
            // test suites are run one test suite after another, on the same
            // workers: the only worker has completed as many units as it may
            // when the first test suite ends, with no unit left to run in it.
            $pool->run(
                [new TestClassWorkUnit(0, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')])],
                $onCompleted,
                $onStreamedEvents,
                $onCrashedUnitRetry,
            );

            $pool->run(
                [new TestClassWorkUnit(1, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')])],
                $onCompleted,
                $onStreamedEvents,
                $onCrashedUnitRetry,
            );
        } finally {
            $pool->stop();
        }

        $this->assertCount(2, $completed);

        foreach ($completed as $unit) {
            $this->assertTrue($this->passedTestsOf($unit)->hasTestMethodPassed(WorkerFirstTest::class . '::testStartsTheProcessLocalCounter'));
        }
    }

    public function testKeepsAWorkerForTheWholeRunWhenRecyclingIsOff(): void
    {
        // The same units on a worker that is never replaced: the second unit
        // finds the counter left behind by the first, and its test fails.
        $units = [
            new TestClassWorkUnit(0, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')]),
            new TestClassWorkUnit(1, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')]),
        ];

        $completed = $this->execute($this->pool(1), $units);

        $this->assertCount(2, $completed);

        $byIndex = [];

        foreach ($completed as $unit) {
            $byIndex[$unit->unit()->index()] = $unit;
        }

        $this->assertTrue($this->passedTestsOf($byIndex[0])->hasTestMethodPassed(WorkerFirstTest::class . '::testStartsTheProcessLocalCounter'));
        $this->assertFalse($this->passedTestsOf($byIndex[1])->hasTestMethodPassed(WorkerFirstTest::class . '::testStartsTheProcessLocalCounter'));
    }

    public function testDoesNotReplaceAWorkerThatHasNoQueuedUnitLeftToRun(): void
    {
        // One worker, replaced after every unit, three units: the process is
        // started once for the pool and once after each of the first two
        // units. After the third unit nothing is queued, so no fresh process
        // is started for it to run.
        $emitter = $this->createMock(Emitter::class);

        $emitter->expects($this->exactly(3))->method('childProcessStarted');

        $units = [
            new TestClassWorkUnit(0, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')]),
            new TestClassWorkUnit(1, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')]),
            new TestClassWorkUnit(2, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')]),
        ];

        $completed = $this->execute($this->pool(1, null, 1, $emitter), $units);

        $this->assertCount(3, $completed);
    }

    public function testRedistributesRemainingUnitsAcrossSurvivingWorkersWhenAWorkerDies(): void
    {
        $units = [
            new TestClassWorkUnit(0, WorkerSecondTest::class, [new WorkerSecondTest('testThatKillsTheWorkerProcess')]),
            new TestClassWorkUnit(1, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')]),
            new TestClassWorkUnit(2, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')]),
        ];

        $completed = $this->execute($this->pool(2), $units);

        $this->assertCount(3, $completed);
        $this->assertSame([0, 1, 2], $this->indexesOf($completed));

        $crashed = [];

        foreach ($completed as $unit) {
            $crashed[$unit->unit()->index()] = $unit->crashed();
        }

        $this->assertTrue($crashed[0]);
        $this->assertFalse($crashed[1]);
        $this->assertFalse($crashed[2]);
    }

    public function testRetriesAUnitWhoseWorkerDiedOnceOnAFreshWorker(): void
    {
        $marker = tempnam(sys_get_temp_dir(), 'phpunit_crash_once_');

        $this->assertNotFalse($marker);

        // The marker must not exist yet: its absence is what makes the
        // fixture test kill its worker on the first attempt.
        unlink($marker);

        $test = new WorkerCrashesOnceTest('testThatCrashesOnTheFirstAttempt');
        $test->setData('marker', [$marker]);

        $units = [
            new TestClassWorkUnit(0, WorkerCrashesOnceTest::class, [$test]),
        ];

        // The worker invokes the fixture's data provider again, which reads
        // the marker from the environment the worker inherits.
        putenv('PHPUNIT_TEST_CRASH_ONCE_MARKER=' . $marker);

        try {
            $completed = $this->execute($this->pool(1), $units);
        } finally {
            putenv('PHPUNIT_TEST_CRASH_ONCE_MARKER');

            @unlink($marker);
        }

        // The first attempt killed the worker; the retry, on a freshly booted
        // worker, passed — so the unit is reported as completed, not crashed.
        $this->assertCount(1, $completed);
        $this->assertFalse($completed[0]->crashed());
    }

    public function testDoesNotRetryAUnitWhoseRetryWasVetoed(): void
    {
        $units = [
            new TestClassWorkUnit(0, WorkerSecondTest::class, [new WorkerSecondTest('testThatKillsTheWorkerProcess')]),
        ];

        $vetoed   = [];
        $streamed = [];

        $completed = $this->execute(
            $this->pool(1),
            $units,
            $streamed,
            static function (WorkUnit $unit) use (&$vetoed): bool
            {
                $vetoed[] = $unit->index();

                return false;
            },
        );

        // The caller vetoed the retry — some of the unit's results had
        // already been reported, in a real run — so the unit is reported as
        // crashed after its first attempt.
        $this->assertSame([0], $vetoed);
        $this->assertCount(1, $completed);
        $this->assertTrue($completed[0]->crashed());
    }

    public function testDoesNotDispatchWhenTheCallerDisallowsIt(): void
    {
        $budget = new ProcessBudget(1);

        $pool = $this->pool(1, $budget);

        $pool->start();

        try {
            $completed = [];

            $pool->begin(
                [new TestClassWorkUnit(0, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')])],
                static function (CompletedWorkUnit $unit) use (&$completed): void
                {
                    $completed[] = $unit;
                },
                static function (WorkUnit $unit, EventCollection $events): void
                {
                },
                static function (WorkUnit $unit): bool
                {
                    return true;
                },
            );

            // The caller makes room for a unit that must run alone: no queued
            // unit is dispatched, so the pool drains instead of topping up.
            $this->assertFalse($pool->tick(false));
            $this->assertFalse($pool->hasExecutingUnits());
            $this->assertFalse($pool->isFinished());
            $this->assertSame([], $completed);

            // Dispatching is allowed again; the queued unit is dispatched and
            // runs to completion.
            $pool->tick();

            $this->assertTrue($pool->hasExecutingUnits());

            while (!$pool->isFinished()) {
                if (!$pool->tick()) {
                    usleep(1000);
                }
            }

            $this->assertFalse($pool->hasExecutingUnits());
            $this->assertCount(1, $completed);
        } finally {
            $pool->stop();
        }
    }

    public function testWaitsForASlotOfTheSharedProcessBudgetBeforeDispatching(): void
    {
        $budget = new ProcessBudget(1);

        $pool = $this->pool(1, $budget);

        $pool->start();

        try {
            $completed = [];

            $pool->begin(
                [new TestClassWorkUnit(0, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')])],
                static function (CompletedWorkUnit $unit) use (&$completed): void
                {
                    $completed[] = $unit;
                },
                static function (WorkUnit $unit, EventCollection $events): void
                {
                },
                static function (WorkUnit $unit): bool
                {
                    return true;
                },
            );

            // The budget's only slot is held by a unit executing elsewhere —
            // a PHPT test, in a real run. The pool must not dispatch, and it
            // must not mistake the starvation for a pool whose workers have
            // all died, which would report the queued unit as crashed.
            $this->assertTrue($budget->acquire());

            $this->assertFalse($pool->tick());
            $this->assertFalse($pool->isFinished());
            $this->assertSame([], $completed);

            // The slot has been given back; the pool may dispatch now.
            $budget->release();

            while (!$pool->isFinished()) {
                if (!$pool->tick()) {
                    usleep(1000);
                }
            }

            $this->assertCount(1, $completed);
            $this->assertFalse($completed[0]->crashed());
        } finally {
            $pool->stop();
        }
    }

    public function testHaltDropsTheQueuedUnitsAndWaitsForTheBusyWorkersToHaltTheirUnitsWithoutReportingTheirResults(): void
    {
        $budget = new ProcessBudget(1);

        $pool = $this->pool(1, $budget);

        $pool->start();

        try {
            $completed = [];
            $streamed  = 0;

            $pool->begin(
                [
                    new TestClassWorkUnit(
                        0,
                        WorkerHaltedTest::class,
                        [
                            new WorkerHaltedTest('testThatFinishesRightAway'),
                            new WorkerHaltedTest('testThatIsRunningWhenTheHaltIsRequested'),
                            new WorkerHaltedTest('testThatIsNotStartedOnceTheUnitHalts'),
                        ],
                    ),
                    new TestClassWorkUnit(1, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')]),
                ],
                static function (CompletedWorkUnit $unit) use (&$completed): void
                {
                    $completed[] = $unit;
                },
                static function (WorkUnit $unit, EventCollection $events) use (&$streamed): void
                {
                    $streamed++;
                },
                static function (WorkUnit $unit): bool
                {
                    return true;
                },
            );

            // The events of the first test are streamed once it has finished,
            // while the second test is running.
            while ($streamed === 0) {
                $pool->tick();

                usleep(1000);
            }

            $pool->halt();

            // The worker is still running the second test: the pool is not
            // finished until the worker has halted the unit.
            $this->assertTrue($pool->hasExecutingUnits());
            $this->assertFalse($pool->isFinished());

            while (!$pool->isFinished()) {
                if (!$pool->tick()) {
                    usleep(1000);
                }
            }

            // The queued unit was dropped, neither the events that the worker
            // streamed after the halt was requested nor the halted unit's
            // result were reported, and the slot the halted unit held has
            // been given back to the shared budget.
            $this->assertSame([], $completed);
            $this->assertSame(1, $streamed);
            $this->assertTrue($budget->acquire());
        } finally {
            $pool->stop();
        }
    }

    public function testKillDropsTheQueuedUnitsAndTerminatesTheBusyWorkers(): void
    {
        $budget = new ProcessBudget(1);

        $pool = $this->pool(1, $budget);

        $pool->start();

        try {
            $completed = [];

            $pool->begin(
                [
                    new TestClassWorkUnit(0, WorkerSleepingTest::class, [new WorkerSleepingTest('testThatSleeps')]),
                    new TestClassWorkUnit(1, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')]),
                ],
                static function (CompletedWorkUnit $unit) use (&$completed): void
                {
                    $completed[] = $unit;
                },
                static function (WorkUnit $unit, EventCollection $events): void
                {
                },
                static function (WorkUnit $unit): bool
                {
                    return true;
                },
            );

            $pool->tick();

            $this->assertFalse($pool->isFinished());

            $pool->kill();

            // The queued unit was dropped and the sleeping unit's worker was
            // terminated without being waited for: the pool is finished,
            // nothing was reported, and the slot the terminated unit held has
            // been given back to the shared budget.
            $this->assertTrue($pool->isFinished());
            $this->assertSame([], $completed);
            $this->assertTrue($budget->acquire());
        } finally {
            $pool->stop();
        }
    }

    public function testAbortDropsTheQueuedUnitsAndReportsTheBusyUnitsAsAbortedByTheTimeLimit(): void
    {
        $budget = new ProcessBudget(1);

        $pool = $this->pool(1, $budget);

        $pool->start();

        try {
            $completed = [];

            $sleeping = new TestClassWorkUnit(0, WorkerSleepingTest::class, [new WorkerSleepingTest('testThatSleeps')]);

            $pool->begin(
                [
                    $sleeping,
                    new TestClassWorkUnit(1, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')]),
                ],
                static function (CompletedWorkUnit $unit) use (&$completed): void
                {
                    $completed[] = $unit;
                },
                static function (WorkUnit $unit, EventCollection $events): void
                {
                },
                static function (WorkUnit $unit): bool
                {
                    return true;
                },
            );

            $pool->tick();

            $pool->abort('message');

            // The queued unit was dropped, and the sleeping unit was terminated
            // and handed over as aborted by the time limit, so that the test it
            // was running is reported as aborted.
            $this->assertTrue($pool->isFinished());
            $this->assertCount(1, $completed);
            $this->assertSame($sleeping, $completed[0]->unit());
            $this->assertTrue($completed[0]->abortedByTimeLimit());
            $this->assertSame('message', $completed[0]->message());
            $this->assertTrue($budget->acquire());
        } finally {
            $pool->stop();
        }
    }

    public function testKillLeavesTheWorkersThatAreNotExecutingAUnitAlone(): void
    {
        $budget = new ProcessBudget(2);

        $pool = $this->pool(2, $budget);

        $pool->start();

        try {
            $completed = [];

            // One unit for two workers: the second worker is started but
            // never becomes busy.
            $pool->begin(
                [
                    new TestClassWorkUnit(0, WorkerSleepingTest::class, [new WorkerSleepingTest('testThatSleeps')]),
                ],
                static function (CompletedWorkUnit $unit) use (&$completed): void
                {
                    $completed[] = $unit;
                },
                static function (WorkUnit $unit, EventCollection $events): void
                {
                },
                static function (WorkUnit $unit): bool
                {
                    return true;
                },
            );

            $pool->tick();

            $pool->kill();

            // Only the busy worker was terminated, and only the slot its unit
            // held went back to the budget: the idle worker holds no slot, so
            // killing must not give one back on its behalf.
            $this->assertTrue($pool->isFinished());
            $this->assertSame([], $completed);
            $this->assertTrue($budget->acquire());
            $this->assertTrue($budget->acquire());
        } finally {
            $pool->stop();
        }
    }

    public function testStopTerminatesAWorkerThatIsStillExecutingAUnitAndDeletesTheFilesOfTheUnit(): void
    {
        $pool = $this->pool(1);

        $pool->start();

        $pool->begin(
            [
                new TestClassWorkUnit(0, WorkerSleepingTest::class, [new WorkerSleepingTest('testThatSleeps')]),
            ],
            static function (CompletedWorkUnit $unit): void
            {
            },
            static function (WorkUnit $unit, EventCollection $events): void
            {
            },
            static function (WorkUnit $unit): bool
            {
                return true;
            },
        );

        $pool->tick();

        $workers = new ReflectionProperty(WorkerPool::class, 'workers')->getValue($pool);

        $this->assertIsArray($workers);
        $this->assertInstanceOf(PersistentWorker::class, $workers[0]);

        $resultFile = new ReflectionProperty(PersistentWorker::class, 'currentResultFile')->getValue($workers[0]);

        assert(is_string($resultFile));

        $this->assertFileExists($resultFile);

        // The worker deletes the command file once it has picked the command
        // up, and then runs the test that sleeps for five seconds.
        $commandFile = new ReflectionProperty(PersistentWorker::class, 'commandFile')->getValue($workers[0]);

        assert(is_string($commandFile));

        for ($i = 0; $i < 500 && is_file($commandFile); $i++) {
            usleep(10000);

            clearstatcache(true, $commandFile);
        }

        $this->assertFileDoesNotExist($commandFile);

        $start = hrtime(true);

        // An exception ended the run while the worker was still running the
        // test.
        $pool->stop();

        $this->assertLessThan(4, (hrtime(true) - $start) / 1000000000);
        $this->assertFalse($workers[0]->isAlive());
        $this->assertFileDoesNotExist($resultFile);
        $this->assertFileDoesNotExist($resultFile . '.done');
    }

    public function testATickOnAFinishedPoolReportsNoProgress(): void
    {
        $pool = $this->pool(1);

        $pool->begin(
            [],
            static function (CompletedWorkUnit $unit): void
            {
            },
            static function (WorkUnit $unit, EventCollection $events): void
            {
            },
            static function (WorkUnit $unit): bool
            {
                return true;
            },
        );

        // The parallel test runner advances the pool and the PHPT runner side
        // by side and keeps ticking both until both are finished, so a tick on
        // a pool that has nothing left to do must be a harmless no-op.
        $this->assertTrue($pool->isFinished());
        $this->assertFalse($pool->tick());
    }

    /**
     * @param list<WorkUnit>                                 $units
     * @param array<non-negative-int, list<EventCollection>> $streamed
     *
     * @return list<CompletedWorkUnit>
     */
    private function execute(WorkerPool $pool, array $units, array &$streamed = [], ?callable $onCrashedUnitRetry = null): array
    {
        if ($onCrashedUnitRetry === null) {
            $onCrashedUnitRetry = static function (WorkUnit $unit): bool
            {
                return true;
            };
        }

        $completed = [];

        $pool->start();

        try {
            $pool->run(
                $units,
                static function (CompletedWorkUnit $unit) use (&$completed): void
                {
                    $completed[] = $unit;
                },
                static function (WorkUnit $unit, EventCollection $events) use (&$streamed): void
                {
                    if (!isset($streamed[$unit->index()])) {
                        $streamed[$unit->index()] = [];
                    }

                    $streamed[$unit->index()][] = $events;
                },
                $onCrashedUnitRetry,
            );
        } finally {
            $pool->stop();
        }

        return $completed;
    }

    /**
     * @param positive-int $numberOfWorkers
     */
    private function pool(int $numberOfWorkers, ?ProcessBudget $budget = null, int $numberOfUnitsBeforeRecycling = 0, ?Emitter $jobRunnerEmitter = null): WorkerPool
    {
        $processor = new ChildProcessResultProcessor(
            new Facade,
            $this->createStub(Emitter::class),
            new PassedTests,
            new CodeCoverage($this->createStub(Emitter::class)),
        );

        if ($jobRunnerEmitter === null) {
            $jobRunnerEmitter = $this->createStub(Emitter::class);
        }

        $jobRunner = new JobRunner($processor, $jobRunnerEmitter);

        $workers = [];

        for ($id = 0; $id < $numberOfWorkers; $id++) {
            $workers[] = new PersistentWorker($jobRunner, $id);
        }

        if ($budget === null) {
            $budget = new ProcessBudget($numberOfWorkers);
        }

        return new WorkerPool($workers, $budget, $numberOfUnitsBeforeRecycling);
    }

    private function passedTestsOf(CompletedWorkUnit $completed): PassedTests
    {
        $envelope = unserialize(substr($completed->serializedResult(), strlen((string) $completed->nonce())));

        $this->assertIsObject($envelope);
        $this->assertInstanceOf(PassedTests::class, $envelope->passedTests);

        return $envelope->passedTests;
    }

    /**
     * @param list<CompletedWorkUnit> $completed
     *
     * @return list<non-negative-int>
     */
    private function indexesOf(array $completed): array
    {
        $indexes = [];

        foreach ($completed as $unit) {
            $indexes[] = $unit->unit()->index();
        }

        sort($indexes);

        return $indexes;
    }
}
