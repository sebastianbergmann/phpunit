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

use function array_keys;
use function ksort;
use function usleep;
use PHPUnit\Event\Emitter;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\Passed;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestRunner\ChildProcessResultProcessor;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\TestRunner\TestResult\PassedTests;
use PHPUnit\Util\PHP\Job;
use PHPUnit\Util\PHP\JobRunner;

#[CoversClass(PhptRunner::class)]
#[CoversClass(PhptWorkUnit::class)]
#[UsesClass(JobRunner::class)]
#[UsesClass(Job::class)]
#[Large]
final class PhptRunnerTest extends TestCase
{
    public function testRunsPhptTestsConcurrentlyAndReportsEachWithItsCollectedEvents(): void
    {
        $file = __DIR__ . '/../../../_files/parallel-worker/worker.phpt';

        $units = [
            new PhptWorkUnit(0, $file),
            new PhptWorkUnit(1, $file),
        ];

        $collected = $this->execute($units, 2);

        $this->assertSame([0, 1], array_keys($collected));

        foreach ($collected as $events) {
            $this->assertTrue($this->contains($events, Passed::class));
        }
    }

    public function testRunsAPhptTestWhoseSectionsEachNeedTheirOwnChildProcess(): void
    {
        // The --INI-- section forces the --CLEAN-- section to run in a child
        // process of its own, so this test's generator yields a second job
        // after the --FILE-- job and exercises the runner's handling of a unit
        // that is not finished by its first child process.
        $units = [
            new PhptWorkUnit(0, __DIR__ . '/../../../_files/parallel-worker/worker-with-clean.phpt'),
        ];

        $collected = $this->execute($units, 2);

        $this->assertSame([0], array_keys($collected));
        $this->assertTrue($this->contains($collected[0], Passed::class));
    }

    public function testReportsAPhptTestThatNeedsNoChildProcessAtAll(): void
    {
        // This test is skipped by a --SKIPIF-- section that runs in-process, so
        // its generator produces its events without ever yielding a job; the
        // runner must report it just the same.
        $units = [
            new PhptWorkUnit(0, __DIR__ . '/../../../_files/parallel-worker/worker-skipped.phpt'),
        ];

        $collected = $this->execute($units, 2);

        $this->assertSame([0], array_keys($collected));
        $this->assertTrue($this->contains($collected[0], Skipped::class));
    }

    public function testRunsPhptTestsOneAtATimeWhenConcurrencyIsOne(): void
    {
        $file = __DIR__ . '/../../../_files/parallel-worker/worker.phpt';

        $units = [
            new PhptWorkUnit(0, $file),
            new PhptWorkUnit(1, $file),
            new PhptWorkUnit(2, $file),
        ];

        $collected = $this->execute($units, 1);

        $this->assertSame([0, 1, 2], array_keys($collected));

        foreach ($collected as $events) {
            $this->assertTrue($this->contains($events, Passed::class));
        }
    }

    public function testWaitsForASlotOfTheSharedProcessBudgetBeforeStartingATest(): void
    {
        $budget = new ProcessBudget(1);

        $runner = $this->runner(2, $budget);

        $collected = [];

        $runner->begin(
            [new PhptWorkUnit(0, __DIR__ . '/../../../_files/parallel-worker/worker.phpt')],
            static function (int $index, EventCollection $events) use (&$collected): void
            {
                $collected[$index] = $events;
            },
        );

        // The budget's only slot is held by a unit executing elsewhere — a
        // test class running in a worker, in a real run. The runner must not
        // start the test until the slot has been given back.
        $this->assertTrue($budget->acquire());

        $this->assertFalse($runner->tick());
        $this->assertFalse($runner->isFinished());
        $this->assertSame([], $collected);

        $budget->release();

        while (!$runner->isFinished()) {
            if (!$runner->tick()) {
                usleep(1000);
            }
        }

        $this->assertSame([0], array_keys($collected));
        $this->assertTrue($this->contains($collected[0], Passed::class));
    }

    /**
     * @param list<PhptWorkUnit> $units
     * @param positive-int       $concurrency
     *
     * @return array<non-negative-int, EventCollection>
     */
    private function execute(array $units, int $concurrency): array
    {
        $collected = [];

        $this->runner($concurrency, new ProcessBudget($concurrency))->run(
            $units,
            static function (int $index, EventCollection $events) use (&$collected): void
            {
                $collected[$index] = $events;
            },
        );

        ksort($collected);

        return $collected;
    }

    /**
     * @param positive-int $concurrency
     */
    private function runner(int $concurrency, ProcessBudget $budget): PhptRunner
    {
        $processor = new ChildProcessResultProcessor(
            Facade::instance(),
            $this->createStub(Emitter::class),
            new PassedTests,
            new CodeCoverage,
        );

        return new PhptRunner(new JobRunner($processor), $concurrency, $budget);
    }

    /**
     * @param class-string $eventClass
     */
    private function contains(EventCollection $events, string $eventClass): bool
    {
        foreach ($events as $event) {
            if ($event instanceof $eventClass) {
                return true;
            }
        }

        return false;
    }
}
