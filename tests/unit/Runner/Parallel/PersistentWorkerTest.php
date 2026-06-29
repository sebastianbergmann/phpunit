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

use function strlen;
use function substr;
use function unserialize;
use function usleep;
use PHPUnit\Event\Emitter;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Failed;
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

#[CoversClass(PersistentWorker::class)]
#[UsesClass(TestClassWorkUnit::class)]
#[UsesClass(CompletedWorkUnit::class)]
#[UsesClass(JobRunner::class)]
#[UsesClass(Job::class)]
#[Large]
final class PersistentWorkerTest extends TestCase
{
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

    private function runToCompletion(PersistentWorker $worker, TestClassWorkUnit $unit): CompletedWorkUnit
    {
        $worker->dispatch($unit);

        while (true) {
            $completed = $worker->poll();

            if ($completed !== null) {
                return $completed;
            }

            usleep(1000);
        }
    }

    private function failedOrErrored(CompletedWorkUnit $completed): bool
    {
        foreach ($this->eventsOf($completed) as $event) {
            if ($event instanceof Failed || $event instanceof Errored) {
                return true;
            }
        }

        return false;
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
