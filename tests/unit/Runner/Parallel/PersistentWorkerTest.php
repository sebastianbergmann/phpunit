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

use const FILE_APPEND;
use function assert;
use function file_put_contents;
use function is_file;
use function is_string;
use function pack;
use function strlen;
use function substr;
use function unserialize;
use function usleep;
use PHPUnit\Event\Emitter;
use PHPUnit\Event\Event;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestRunner\ChildProcessResultProcessor;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\TestFixture\ParallelWorker\WorkerFirstTest;
use PHPUnit\TestFixture\ParallelWorker\WorkerSecondTest;
use PHPUnit\TestRunner\TestResult\PassedTests;
use PHPUnit\Util\PHP\Job;
use PHPUnit\Util\PHP\JobRunner;
use ReflectionProperty;

#[CoversClass(PersistentWorker::class)]
#[UsesClass(TestClassWorkUnit::class)]
#[UsesClass(CompletedWorkUnit::class)]
#[UsesClass(JobRunner::class)]
#[UsesClass(Job::class)]
#[Large]
final class PersistentWorkerTest extends TestCase
{
    /**
     * The events streamed by the worker while the most recently run unit was
     * still running, one collection per frame, in arrival order.
     *
     * @var list<EventCollection>
     */
    private array $streamedEvents = [];

    public function testReusesOneProcessAcrossSequentiallyDispatchedUnits(): void
    {
        $worker = $this->worker();

        $worker->start();

        $first = $this->runToCompletion(
            $worker,
            new TestClassWorkUnit(0, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')]),
        );

        // The second unit only passes if it ran in the same process as the
        // first one, which is what reusing a single worker provides.
        $second = $this->runToCompletion(
            $worker,
            new TestClassWorkUnit(1, WorkerSecondTest::class, [new WorkerSecondTest('testSeesTheStateLeftBehindByTheFirstTest')]),
        );

        $worker->stop();

        $this->assertFalse($first->crashed());
        $this->assertFalse($this->failedOrErrored($first));

        $this->assertFalse($second->crashed());
        $this->assertFalse($this->failedOrErrored($second));
    }

    public function testShipsOnlyTheUnitsOwnPassesInItsResultEnvelope(): void
    {
        $worker = $this->worker();

        $worker->start();

        $first = $this->runToCompletion(
            $worker,
            new TestClassWorkUnit(0, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')]),
        );

        $second = $this->runToCompletion(
            $worker,
            new TestClassWorkUnit(1, WorkerSecondTest::class, [new WorkerSecondTest('testSeesTheStateLeftBehindByTheFirstTest')]),
        );

        $worker->stop();

        $this->assertTrue($this->passedTestsOf($first)->hasTestMethodPassed(WorkerFirstTest::class . '::testStartsTheProcessLocalCounter'));
        $this->assertTrue($this->passedTestsOf($second)->hasTestMethodPassed(WorkerSecondTest::class . '::testSeesTheStateLeftBehindByTheFirstTest'));

        // The envelope of the second unit must not carry the passes of the
        // first one: the parent imports a unit's passes at the moment its
        // suite-order turn comes, and a worker may run units out of suite
        // order, so a pass imported ahead of its turn would let a test that
        // depends on it run where a sequential run would have skipped it.
        $this->assertFalse($this->passedTestsOf($second)->hasTestMethodPassed(WorkerFirstTest::class . '::testStartsTheProcessLocalCounter'));
    }

    public function testReportsAFailingTestThroughTheResultEnvelopeRatherThanAsACrash(): void
    {
        $worker = $this->worker();

        $worker->start();

        $completed = $this->runToCompletion(
            $worker,
            new TestClassWorkUnit(0, WorkerSecondTest::class, [new WorkerSecondTest('testThatFails')]),
        );

        $worker->stop();

        $this->assertFalse($completed->crashed());
        $this->assertTrue($this->failedOrErrored($completed));
    }

    public function testRecognizesCompletionEvenWhenATestLeavesStrayOutputOnTheControlChannel(): void
    {
        $worker = $this->worker();

        $worker->start();

        // The test writes stray bytes to file descriptor 1 without a trailing
        // newline; the worker must still report the unit as finished and ship a
        // result envelope that is not corrupted by that output.
        $completed = $this->runToCompletion(
            $worker,
            new TestClassWorkUnit(0, WorkerSecondTest::class, [new WorkerSecondTest('testThatWritesStrayOutputWithoutANewlineToTheControlChannel')]),
        );

        $worker->stop();

        $this->assertFalse($completed->crashed());
        $this->assertFalse($this->failedOrErrored($completed));
    }

    public function testReportsACrashWhenTheWorkerDiesWhileRunningAUnit(): void
    {
        $worker = $this->worker();

        $worker->start();

        $completed = $this->runToCompletion(
            $worker,
            new TestClassWorkUnit(0, WorkerSecondTest::class, [new WorkerSecondTest('testThatKillsTheWorkerProcess')]),
        );

        $worker->stop();

        $this->assertTrue($completed->crashed());
    }

    public function testReportsAUnitAsCrashedWhenAFrameOfItsEventStreamFailsVerification(): void
    {
        $completed = $this->runWithTamperedEventStream(
            // A complete frame whose payload does not start with the nonce the
            // worker was given.
            pack('J', 23) . 'nonce-that-cannot-match',
        );

        $this->assertTrue($completed->crashed());
        $this->assertStringContainsString('tampered with', (string) $completed->message());
    }

    public function testReportsAUnitAsCrashedWhenItsEventStreamEndsInBytesThatDoNotFormACompleteFrame(): void
    {
        $completed = $this->runWithTamperedEventStream(
            // Trailing bytes that are too short to be a frame, appended after
            // the worker signalled that it will not write any further frames.
            'xxxx',
        );

        $this->assertTrue($completed->crashed());
        $this->assertStringContainsString('tampered with', (string) $completed->message());
    }

    public function testStreamsTheEventsOfAFinishedTestWhileTheUnitIsStillRunning(): void
    {
        $worker = $this->worker();

        $worker->start();

        $completed = $this->runToCompletion(
            $worker,
            new TestClassWorkUnit(0, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')]),
        );

        $worker->stop();

        $this->assertFalse($completed->crashed());

        // The events of the finished test arrived through the stream, not
        // through the result envelope: the envelope carries only the events
        // that were emitted after the last test finished.
        $this->assertTrue($this->streamedEventsContainAFinishedTest());
        $this->assertFalse($this->eventsContainAFinishedTest($this->eventsOf($completed)->asArray()));
    }

    /**
     * Run one unit to the point where the worker has finished it and signalled
     * completion, then append the given bytes to its event stream file before
     * the worker is polled. The stream is complete when the completion signal
     * is given, so whatever is appended afterwards cannot have been written by
     * the worker — the poll must recognize the interference.
     */
    private function runWithTamperedEventStream(string $appendedBytes): CompletedWorkUnit
    {
        $worker = $this->worker();

        $worker->start();

        $worker->dispatch(
            new TestClassWorkUnit(0, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')]),
        );

        $doneFile   = $this->privateString($worker, 'currentDoneFile');
        $streamFile = $this->privateString($worker, 'currentStreamFile');

        while (!is_file($doneFile)) {
            usleep(1000);
        }

        file_put_contents($streamFile, $appendedBytes, FILE_APPEND);

        $completed = $this->runToCompletion($worker, null);

        $worker->stop();

        return $completed;
    }

    private function privateString(PersistentWorker $worker, string $property): string
    {
        $value = new ReflectionProperty(PersistentWorker::class, $property)->getValue($worker);

        assert(is_string($value));

        return $value;
    }

    /**
     * Dispatch the given unit — the tamper tests dispatch theirs themselves
     * and pass null — and poll the worker until it reports completion.
     */
    private function runToCompletion(PersistentWorker $worker, ?TestClassWorkUnit $unit): CompletedWorkUnit
    {
        $this->streamedEvents = [];

        if ($unit !== null) {
            $worker->dispatch($unit);
        }

        while (true) {
            $completed = $worker->poll(
                function (WorkUnit $unit, EventCollection $events): void
                {
                    $this->streamedEvents[] = $events;
                },
            );

            if ($completed !== null) {
                return $completed;
            }

            usleep(1000);
        }
    }

    private function failedOrErrored(CompletedWorkUnit $completed): bool
    {
        foreach ($this->allEventsOf($completed) as $event) {
            if ($event instanceof Failed || $event instanceof Errored) {
                return true;
            }
        }

        return false;
    }

    /**
     * All of the events the unit produced, in order: those streamed while it
     * was still running, followed by those carried by the result envelope.
     *
     * @return list<Event>
     */
    private function allEventsOf(CompletedWorkUnit $completed): array
    {
        $events = [];

        foreach ($this->streamedEvents as $collection) {
            foreach ($collection as $event) {
                $events[] = $event;
            }
        }

        foreach ($this->eventsOf($completed) as $event) {
            $events[] = $event;
        }

        return $events;
    }

    private function streamedEventsContainAFinishedTest(): bool
    {
        foreach ($this->streamedEvents as $collection) {
            if ($this->eventsContainAFinishedTest($collection->asArray())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<Event> $events
     */
    private function eventsContainAFinishedTest(array $events): bool
    {
        foreach ($events as $event) {
            if ($event instanceof Finished) {
                return true;
            }
        }

        return false;
    }

    private function passedTestsOf(CompletedWorkUnit $completed): PassedTests
    {
        $nonce      = (string) $completed->nonce();
        $serialized = substr($completed->serializedResult(), strlen($nonce));
        $envelope   = unserialize($serialized);

        $this->assertIsObject($envelope);
        $this->assertInstanceOf(PassedTests::class, $envelope->passedTests);

        return $envelope->passedTests;
    }

    private function eventsOf(CompletedWorkUnit $completed): EventCollection
    {
        $nonce      = (string) $completed->nonce();
        $serialized = substr($completed->serializedResult(), strlen($nonce));
        $envelope   = unserialize($serialized);

        $this->assertIsObject($envelope);
        $this->assertInstanceOf(EventCollection::class, $envelope->events);

        return $envelope->events;
    }

    private function worker(): PersistentWorker
    {
        $processor = new ChildProcessResultProcessor(
            new Facade,
            $this->createStub(Emitter::class),
            new PassedTests,
            new CodeCoverage,
        );

        return new PersistentWorker(new JobRunner($processor));
    }
}
