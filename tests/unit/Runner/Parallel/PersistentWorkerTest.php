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

use PHPUnit\Event\Emitter;
use PHPUnit\Event\Facade;
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
#[UsesClass(JobRunner::class)]
#[UsesClass(Job::class)]
#[Large]
final class PersistentWorkerTest extends TestCase
{
    public function testRunsMultipleTestsFromDifferentClassesInOneProcess(): void
    {
        $worker = $this->worker();

        $worker->start();

        $first  = new WorkerFirstTest('testStartsTheProcessLocalCounter');
        $second = new WorkerSecondTest('testSeesTheStateLeftBehindByTheFirstTest');

        $worker->run($first);
        $worker->run($second);

        $worker->stop();

        $this->assertTrue($first->status()->isSuccess());
        $this->assertSame(1, $first->numberOfAssertionsPerformed());

        // The second test only passes if it ran in the same process as the
        // first one, which is what reusing a single worker provides.
        $this->assertTrue($second->status()->isSuccess());
        $this->assertSame(2, $second->numberOfAssertionsPerformed());
    }

    public function testReportsFailingTestsBackToTheParentProcess(): void
    {
        $worker = $this->worker();

        $worker->start();

        $failing = new WorkerSecondTest('testThatFails');

        $worker->run($failing);

        $worker->stop();

        $this->assertTrue($failing->status()->isFailure());
        $this->assertStringContainsString(
            'intentional failure inside a persistent worker',
            $failing->status()->message(),
        );
    }

    public function testReportsAnErrorWhenTheWorkerDiesWhileRunningATest(): void
    {
        $worker = $this->worker();

        $worker->start();

        $crashing = new WorkerSecondTest('testThatKillsTheWorkerProcess');

        $worker->run($crashing);

        $worker->stop();

        $this->assertTrue($crashing->status()->isError());
    }

    private function worker(): PersistentWorker
    {
        $processor = new ChildProcessResultProcessor(
            new Facade,
            $this->createStub(Emitter::class),
            new PassedTests,
            new CodeCoverage,
        );

        return new PersistentWorker(new JobRunner($processor), $processor);
    }
}
