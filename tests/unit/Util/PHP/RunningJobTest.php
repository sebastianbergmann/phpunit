<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\PHP;

use function array_merge;
use function file_get_contents;
use function hrtime;
use function sprintf;
use function stream_select;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;
use function usleep;
use function var_export;
use PHPUnit\Event\Emitter;
use PHPUnit\Event\Facade;
use PHPUnit\Event\TestRunner\ChildProcessReason;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\RequiresOperatingSystem;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestRunner\ChildProcessResultProcessor;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\TestRunner\TestResult\PassedTests;

#[CoversClass(RunningJob::class)]
#[UsesClass(JobRunner::class)]
#[UsesClass(Job::class)]
#[UsesClass(Result::class)]
#[Large]
final class RunningJobTest extends TestCase
{
    public function testCollectsOutputOfASingleStartedJob(): void
    {
        $job = $this->jobRunner()->start(
            new Job(
                <<<'EOT'
<?php declare(strict_types=1);
fwrite(STDOUT, 'out');
fwrite(STDERR, 'err');

EOT,
                ChildProcessReason::ParallelWorker,
            ),
        );

        $job->closeStdin();

        $result = $job->wait();

        $this->assertSame('out', $result->stdout());
        $this->assertSame('err', $result->stderr());

        // Reaping the same job again returns the memoized result instead of
        // closing the already-closed process a second time.
        $this->assertSame($result, $job->wait());
    }

    public function testTerminatesAJobWithoutWaitingForItToFinish(): void
    {
        $job = $this->jobRunner()->start(
            new Job(
                <<<'EOT'
<?php declare(strict_types=1);
sleep(10);

EOT,
                ChildProcessReason::ParallelWorker,
            ),
        );

        $job->terminate();

        $this->assertFalse($job->isRunning());

        // Terminating a job that has already been reaped is a no-op.
        $job->terminate();
    }

    #[RequiresPhpExtension('pcntl')]
    public function testForcesTerminationOfAJobThatIgnoresTheTerminationSignal(): void
    {
        $readyFile = tempnam(sys_get_temp_dir(), 'phpunit_');

        $this->assertNotFalse($readyFile);

        $job = $this->jobRunner()->start(
            new Job(
                sprintf(
                    <<<'EOT'
<?php declare(strict_types=1);
pcntl_signal(SIGTERM, SIG_IGN);
file_put_contents(%s, 'ready');
sleep(60);

EOT,
                    var_export($readyFile, true),
                ),
                ChildProcessReason::ParallelWorker,
            ),
        );

        // The job is terminated only once it has reported that it ignores the
        // termination signal, so that the signal is guaranteed to be without
        // effect and termination has to be forced.
        while (file_get_contents($readyFile) !== 'ready') {
            usleep(1000);
        }

        $job->terminate();

        $this->assertFalse($job->isRunning());

        @unlink($readyFile);
    }

    #[RequiresOperatingSystem('Linux|Darwin')]
    public function testDoesNotWaitForAProcessThatInheritedTheOutputPipeWhenTerminatingAJob(): void
    {
        $job = $this->jobRunner()->start(
            new Job(
                <<<'EOT'
<?php declare(strict_types=1);
// The background process inherits this process' standard output and keeps
// its write end open long after this process is gone; draining the pipe
// until end-of-file would block until the background process exits.
system('sleep 30 &');
sleep(30);

EOT,
                ChildProcessReason::ParallelWorker,
            ),
        );

        $start = hrtime(true);

        $job->terminate();

        $this->assertFalse($job->isRunning());
        $this->assertLessThan(30, (hrtime(true) - $start) / 1000000000);
    }

    public function testReportsWhetherTheProcessIsStillRunning(): void
    {
        $job = $this->jobRunner()->start(
            new Job(
                <<<'EOT'
<?php declare(strict_types=1);
fgets(STDIN);

EOT,
                ChildProcessReason::ParallelWorker,
            ),
        );

        // The job blocks reading from its standard input, so it is still
        // running until input arrives.
        $this->assertTrue($job->isRunning());

        $job->write("go\n");
        $job->closeStdin();
        $job->wait();

        // Once the job has been reaped, its memoized result short-circuits the
        // liveness poll.
        $this->assertFalse($job->isRunning());
    }

    public function testWritesToTheStandardInputOfTheJob(): void
    {
        $job = $this->jobRunner()->start(
            new Job(
                <<<'EOT'
<?php declare(strict_types=1);
fwrite(STDOUT, fgets(STDIN));

EOT,
                ChildProcessReason::ParallelWorker,
            ),
        );

        $this->assertNotNull($job->stdout());

        $job->write("echoed\n");
        $job->closeStdin();

        $this->assertSame("echoed\n", $job->wait()->stdout());
    }

    public function testMergesTheErrorStreamIntoTheOutputStreamWhenRequested(): void
    {
        $result = $this->jobRunner()->run(
            new Job(
                <<<'EOT'
<?php declare(strict_types=1);
fwrite(STDOUT, 'out-');
fwrite(STDERR, 'err');

EOT,
                ChildProcessReason::TestRequiringProcessIsolation,
                redirectErrors: true,
            ),
        );

        $this->assertSame('out-err', $result->stdout());
        $this->assertSame('', $result->stderr());
    }

    public function testCanDriveSeveralJobsThroughASingleSelectLoop(): void
    {
        $runner = $this->jobRunner();

        $jobs = [
            $runner->start($this->jobPrinting('first')),
            $runner->start($this->jobPrinting('second')),
            $runner->start($this->jobPrinting('third')),
        ];

        foreach ($jobs as $job) {
            $job->closeStdin();
        }

        do {
            $readable = [];

            foreach ($jobs as $job) {
                $readable = array_merge($readable, $job->readableStreams());
            }

            if ($readable === []) {
                break;
            }

            $write  = [];
            $except = [];

            if (@stream_select($readable, $write, $except, 2) === false) {
                break;
            }

            foreach ($jobs as $job) {
                $job->consume();
            }
        } while (true);

        $output = [];

        foreach ($jobs as $job) {
            $output[] = $job->wait()->stdout();
        }

        $this->assertSame(['first', 'second', 'third'], $output);
    }

    private function jobPrinting(string $token): Job
    {
        return new Job(
            <<<EOT
<?php declare(strict_types=1);
usleep(50000);
fwrite(STDOUT, '{$token}');

EOT,
            ChildProcessReason::ParallelWorker,
        );
    }

    private function jobRunner(): JobRunner
    {
        return new JobRunner(
            new ChildProcessResultProcessor(
                new Facade,
                $this->createStub(Emitter::class),
                new PassedTests,
                new CodeCoverage,
            ),
        );
    }
}
