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
use function base64_encode;
use function bin2hex;
use function count;
use function defined;
use function file_get_contents;
use function get_include_path;
use function hrtime;
use function is_file;
use function json_encode;
use function random_bytes;
use function serialize;
use function sprintf;
use function strlen;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;
use function var_export;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Event\TestRunner\ChildProcessReason;
use PHPUnit\Framework\DataProviderTestSuite;
use PHPUnit\Framework\RepeatTestSuite;
use PHPUnit\Framework\RetryTestSuite;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;
use PHPUnit\TextUI\Configuration\SourceMapper;
use PHPUnit\Util\PHP\Job;
use PHPUnit\Util\PHP\JobRunner;
use PHPUnit\Util\PHP\RunningJob;
use ReflectionClass;
use SebastianBergmann\Template\Template;
use Throwable;

/**
 * A worker process that boots PHPUnit once and then executes an arbitrary
 * number of tests, each in response to a command received on its control
 * channel (its standard input). A unit of work is a whole test class; the
 * worker reconstructs it from the dispatched command, runs it, and reports the
 * result back through the filesystem (see dispatch() and poll()).
 *
 * Unlike the SeparateProcessTestRunner, which spawns one process per test for
 * isolation, a PersistentWorker amortizes the cost of bootstrapping PHPUnit
 * across all of the units it runs. The tests executed by a single worker share
 * one process and therefore do not get the per-test global-state isolation that
 * process isolation provides; this is the trade-off that makes the worker
 * suitable as a building block for parallel test execution.
 *
 * The result of each unit is transported back to the parent process as the same
 * kind of serialized envelope that process isolation uses, which the
 * ResultAggregator decodes and replays into the parent's event subsystem.
 *
 * While a unit is still running, the worker additionally streams the events of
 * every test that has finished so far: it appends them, as length-prefixed
 * frames, to a stream file that the parent reads incrementally on each poll.
 * This is what lets the parent report progress per finished test instead of
 * per finished unit. Events shipped in a frame are drained from the worker's
 * event collection, so the end-of-unit envelope carries only the events that
 * were emitted after the last test finished.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class PersistentWorker
{
    private readonly JobRunner $jobRunner;
    private ?RunningJob $job = null;

    /**
     * @var list<string>
     */
    private array $temporaryFiles = [];

    /**
     * The unit currently being executed by this worker, together with the
     * bookkeeping needed to harvest its result once the worker reports that it
     * has finished. These are set by dispatch() and cleared by poll() (or by a
     * detected crash); a null currentUnit means the worker is idle.
     *
     * Completion is signalled through the filesystem rather than the worker's
     * standard output: once the worker has written the result file it creates a
     * sibling "done" file, whose appearance the parent detects by polling. This
     * avoids stream_select() and non-blocking reads on the worker's output pipe,
     * neither of which works on Windows.
     */
    private ?WorkUnit $currentUnit = null;

    /**
     * @var ?non-empty-string
     */
    private ?string $currentNonce      = null;
    private ?string $currentResultFile = null;
    private ?string $currentDoneFile   = null;
    private ?string $currentStreamFile = null;

    /**
     * How many bytes of the current unit's event stream file have been
     * consumed by drainStreamedEvents() so far.
     *
     * @var non-negative-int
     */
    private int $currentStreamOffset = 0;

    /**
     * Whether the current unit's event stream failed verification: a frame
     * whose nonce does not match, a frame whose payload does not decode to a
     * collection of events, or trailing bytes that do not form a complete
     * frame even though the worker has signalled completion. A tainted stream
     * is not read any further, and the unit it belongs to is not trusted.
     */
    private bool $currentStreamTainted = false;

    /**
     * @var non-negative-int
     */
    private readonly int $id;

    /**
     * @var non-empty-string
     */
    private string $token;

    /**
     * @param non-negative-int $id
     */
    public function __construct(JobRunner $jobRunner, int $id = 0)
    {
        $this->jobRunner = $jobRunner;
        $this->id        = $id;
        $this->token     = $this->newToken();
    }

    /**
     * @throws WorkerException
     */
    public function start(): void
    {
        // The worker's identity is exposed to the tests it runs so that test
        // fixtures can partition shared resources (a database, a port, a
        // temporary directory, ...) per worker and thereby avoid colliding with
        // the tests running concurrently in the other workers.
        //
        // Two identifiers are provided: PHPUNIT_WORKER_ID is the small, stable
        // ordinal (0, 1, 2, ...) that is ideal for indexing a fixed set of
        // pre-provisioned resources, while PHPUNIT_WORKER_TOKEN adds a value
        // that is unique across workers and across runs, for resources that must
        // not collide with those left behind by a previous run.
        $environmentVariables = [
            'PHPUNIT_WORKER_ID'    => (string) $this->id,
            'PHPUNIT_WORKER_TOKEN' => $this->token,
        ];

        // The worker's standard output and standard error are redirected to a
        // temporary file rather than pipes: the parent does not read a
        // worker's output while units are executing (results travel through
        // the filesystem, see dispatch() and poll()), and a pipe that is
        // never read would fill its buffer and block the worker forever once
        // a test writes enough to it — for instance with fwrite(STDERR, ...),
        // which bypasses PHPUnit's output buffering. The accumulated output
        // is harvested when the worker is stopped.
        $this->job = $this->jobRunner->start(
            new Job(
                $this->buildWorkerCode(),
                ChildProcessReason::ParallelWorker,
                [],
                $environmentVariables,
                [],
                null,
                true,
            ),
        );
    }

    /**
     * Boot a fresh worker process in place of one that has died. The new
     * process keeps the worker's ordinal identity (PHPUNIT_WORKER_ID), so
     * that resources partitioned per worker stay partitioned, but receives a
     * new PHPUNIT_WORKER_TOKEN, so that it cannot collide with resources that
     * the dead process left behind.
     *
     * @throws WorkerException
     */
    public function restart(): void
    {
        assert($this->job === null);

        $this->token = $this->newToken();

        $this->start();
    }

    /**
     * Send a unit of work to the worker without waiting for it to finish.
     *
     * The command is written to the worker's standard input and dispatch()
     * returns immediately; the caller is expected to poll this worker with
     * poll() until it reports completion, so that a single thread of control can
     * keep several workers busy at the same time.
     *
     * @throws WorkerException
     */
    public function dispatch(WorkUnit $unit): void
    {
        assert($this->job !== null);
        assert($this->currentUnit === null);

        $offset     = hrtime();
        $nonce      = bin2hex(random_bytes(16));
        $resultFile = tempnam(sys_get_temp_dir(), 'phpunit_');

        if ($resultFile === false) {
            // @codeCoverageIgnoreStart
            throw new WorkerException('Unable to create temporary file for the worker result');
            // @codeCoverageIgnoreEnd
        }

        assert($unit instanceof TestClassWorkUnit);

        $doneFile   = $resultFile . '.done';
        $streamFile = $resultFile . '.stream';

        $command = $this->testClassCommand($unit, $offset, $resultFile, $doneFile, $streamFile, $nonce);

        $encodedCommand = json_encode($command);

        assert($encodedCommand !== false);

        $this->currentUnit          = $unit;
        $this->currentNonce         = $nonce;
        $this->currentResultFile    = $resultFile;
        $this->currentDoneFile      = $doneFile;
        $this->currentStreamFile    = $streamFile;
        $this->currentStreamOffset  = 0;
        $this->currentStreamTainted = false;

        $this->job->write($encodedCommand . "\n");
    }

    /**
     * Whether the worker is currently executing a dispatched unit.
     */
    public function isBusy(): bool
    {
        return $this->currentUnit !== null;
    }

    /**
     * Whether the worker process is still usable. A worker that died while
     * running a unit is no longer usable and must not be dispatched to again.
     */
    public function isAlive(): bool
    {
        return $this->job !== null;
    }

    /**
     * Check, without blocking, whether the dispatched unit has finished and, if
     * so, harvest its result; if the worker has died without finishing it,
     * report the unit as crashed instead.
     *
     * Either way, the events that the worker has streamed since the previous
     * poll are drained first and handed, one frame at a time, to the given
     * callback, so that the caller can report progress while the unit is still
     * running.
     *
     * Returns null while the unit is still running. The caller is expected to
     * call this repeatedly, sleeping briefly between rounds, until it returns a
     * completed unit.
     *
     * @param callable(WorkUnit, EventCollection):void $onStreamedEvents
     */
    public function poll(callable $onStreamedEvents): ?CompletedWorkUnit
    {
        assert($this->currentUnit !== null);
        assert($this->currentDoneFile !== null);

        // Completion is snapshotted before the stream is drained: the done
        // file is created only after the last frame has been written, so a
        // stream that is drained after the done file was seen is known to have
        // been read in its entirety.
        $done = is_file($this->currentDoneFile);

        foreach ($this->drainStreamedEvents($done) as $events) {
            $onStreamedEvents($this->currentUnit, $events);
        }

        if ($done) {
            return $this->finished();
        }

        if ($this->job === null || !$this->job->isRunning()) {
            return $this->crashed();
        }

        return null;
    }

    /**
     * Terminate the worker process immediately, abandoning the unit it is
     * executing: the unit's result is neither awaited nor harvested. Used
     * when the test runner stops early, because the results collected so far
     * call for it (--stop-on-*).
     */
    public function kill(): void
    {
        if ($this->job !== null) {
            $this->job->terminate();

            $this->job = null;
        }

        if ($this->currentResultFile !== null) {
            @unlink($this->currentResultFile);
        }

        if ($this->currentDoneFile !== null) {
            @unlink($this->currentDoneFile);
        }

        if ($this->currentStreamFile !== null) {
            @unlink($this->currentStreamFile);
        }

        $this->clearCurrentUnit();
    }

    public function stop(): void
    {
        if ($this->job !== null) {
            $encodedCommand = json_encode(['command' => 'stop']);

            assert($encodedCommand !== false);

            $this->job->write($encodedCommand . "\n");
            $this->job->closeStdin();

            $result = $this->job->wait();

            EventFacade::emitter()->childProcessFinished(ChildProcessReason::ParallelWorker, $result->stdout(), $result->stderr());

            $this->job = null;
        }

        foreach ($this->temporaryFiles as $temporaryFile) {
            @unlink($temporaryFile);
        }

        $this->temporaryFiles = [];
    }

    /**
     * Read the frames that the worker has appended to its event stream file
     * since the previous read (see EventStream for the format) and decode each
     * one into the collection of events it carries.
     *
     * When $streamIsComplete is true, the worker has signalled completion and
     * no further writes can happen; trailing bytes that do not form a complete
     * frame then taint the stream, in addition to the conditions under which
     * EventStream itself reports taint. A tainted stream is not read any
     * further, and finished() reports the unit as compromised instead of
     * trusting its result.
     *
     * @return list<EventCollection>
     */
    private function drainStreamedEvents(bool $streamIsComplete): array
    {
        assert($this->currentStreamFile !== null);

        if ($this->currentStreamTainted || !is_file($this->currentStreamFile)) {
            return [];
        }

        $data = @file_get_contents($this->currentStreamFile, false, null, $this->currentStreamOffset);

        if ($data === false || $data === '') {
            return [];
        }

        $result = EventStream::readFrames($data, $this->currentNonce);

        $this->currentStreamOffset += $result['bytesConsumed'];

        if ($result['tainted']) {
            $this->currentStreamTainted = true;
        }

        if ($streamIsComplete && $result['bytesConsumed'] < strlen($data)) {
            $this->currentStreamTainted = true;
        }

        return $result['frames'];
    }

    /**
     * @param array{0: int, 1: int} $offset
     * @param non-empty-string      $resultFile
     * @param non-empty-string      $doneFile
     * @param non-empty-string      $streamFile
     * @param non-empty-string      $nonce
     *
     * @throws WorkerException
     *
     * @return array<string, mixed>
     */
    private function testClassCommand(TestClassWorkUnit $unit, array $offset, string $resultFile, string $doneFile, string $streamFile, string $nonce): array
    {
        $class = new ReflectionClass($unit->className());
        $file  = $class->getFileName();

        assert($file !== false);

        $tests = [];

        foreach ($unit->tests() as $test) {
            $tests[] = $this->memberDescriptor($test, $unit->className(), $resultFile);
        }

        return [
            'command'           => 'runUnit',
            'file'              => $file,
            'className'         => $unit->className(),
            'tests'             => $tests,
            'offsetSeconds'     => $offset[0],
            'offsetNanoseconds' => $offset[1],
            'resultFile'        => $resultFile,
            'doneFile'          => $doneFile,
            'streamFile'        => $streamFile,
            'nonce'             => $nonce,
        ];
    }

    /**
     * The transportable description of one member of a unit: a single test
     * case, the RetryTestSuite of a retried test method, the RepeatTestSuite
     * of a repeated test method, or the DataProviderTestSuite of a data
     * provider method, whose members are in turn described recursively.
     *
     * @param class-string     $className
     * @param non-empty-string $resultFile
     *
     * @throws WorkerException
     *
     * @return array<string, mixed>
     */
    private function memberDescriptor(Test $test, string $className, string $resultFile): array
    {
        // The tests of a data provider method travel as their
        // DataProviderTestSuite so that the suite's event envelope, which
        // nests the tests in the logger output of a sequential run, is
        // emitted inside the worker in a parallel run, too.
        if ($test instanceof DataProviderTestSuite) {
            $members = [];

            foreach ($test->tests() as $member) {
                $members[] = $this->memberDescriptor($member, $className, $resultFile);
            }

            return [
                'type'  => 'dataProvider',
                'name'  => $test->name(),
                'tests' => $members,
            ];
        }

        if ($test instanceof RetryTestSuite) {
            $aggregated = $test->tests();

            assert(count($aggregated) === 1 && $aggregated[0] instanceof TestCase);

            return [
                'type'        => 'retry',
                'name'        => $test->name(),
                'maxAttempts' => $test->maxAttempts(),
                'test'        => $this->testDescriptor($aggregated[0], $className, $resultFile),
            ];
        }

        if ($test instanceof RepeatTestSuite) {
            $repetitions = [];

            foreach ($test->tests() as $repetition) {
                assert($repetition instanceof TestCase);

                $repetitions[] = $this->testDescriptor($repetition, $className, $resultFile);
            }

            return [
                'type'             => 'repeat',
                'name'             => $test->name(),
                'failureThreshold' => $test->failureThreshold(),
                'tests'            => $repetitions,
            ];
        }

        assert($test instanceof TestCase);

        return $this->testDescriptor($test, $className, $resultFile);
    }

    /**
     * The transportable description of a single test case, from which the
     * worker reconstructs it.
     *
     * @param class-string     $className
     * @param non-empty-string $resultFile
     *
     * @throws WorkerException
     *
     * @return array<string, mixed>
     */
    private function testDescriptor(TestCase $test, string $className, string $resultFile): array
    {
        try {
            $data            = base64_encode(serialize($test->providedData()));
            $dependencyInput = base64_encode(serialize($test->dependencyInput()));
        } catch (Throwable $t) {
            @unlink($resultFile);

            throw new WorkerException(
                sprintf(
                    'The tests of class %s cannot be run in parallel because their data cannot be serialized: %s',
                    $className,
                    $t->getMessage(),
                ),
            );
        }

        return [
            'type'             => 'test',
            'methodName'       => $test->name(),
            'data'             => $data,
            'dataName'         => $test->dataName(),
            'dependencyInput'  => $dependencyInput,
            'repetition'       => $test->repetition(),
            'totalRepetitions' => $test->totalRepetitions(),
            'attempt'          => $test->attempt(),
            'maxAttempts'      => $test->maxAttempts(),
        ];
    }

    /**
     * Harvest the result of the unit the worker has just reported as finished.
     */
    private function finished(): CompletedWorkUnit
    {
        assert($this->currentUnit !== null);
        assert($this->currentResultFile !== null);
        assert($this->currentDoneFile !== null);
        assert($this->currentStreamFile !== null);

        $serializedResult = file_get_contents($this->currentResultFile);

        @unlink($this->currentResultFile);
        @unlink($this->currentDoneFile);
        @unlink($this->currentStreamFile);

        if ($serializedResult === false) {
            // @codeCoverageIgnoreStart
            $serializedResult = '';
            // @codeCoverageIgnoreEnd
        }

        // A unit whose event stream failed verification is not trusted, even
        // though its result envelope may verify: the events already forwarded
        // from the stream and the events in the envelope are two parts of one
        // result, and part of it was interfered with.
        if ($this->currentStreamTainted) {
            // The worker process itself is not reusable either: something
            // interfered with its communication channel, so nothing further
            // that arrives through it can be trusted. Terminating and reaping
            // it here makes the pool treat this worker like one that died —
            // a retry of the unit, if one is allowed, boots a fresh worker
            // process instead of asserting that this one has no job.
            if ($this->job !== null) {
                $this->job->terminate();

                $this->job = null;
            }

            $completed = new CompletedWorkUnit(
                $this->currentUnit,
                '',
                null,
                true,
                sprintf(
                    'The event stream of the worker process running %s was tampered with or written by an unexpected process',
                    $this->currentUnit->name(),
                ),
            );

            $this->clearCurrentUnit();

            return $completed;
        }

        $completed = new CompletedWorkUnit(
            $this->currentUnit,
            $serializedResult,
            $this->currentNonce,
            false,
        );

        $this->clearCurrentUnit();

        return $completed;
    }

    /**
     * Record that the worker died while running the dispatched unit and reap
     * the dead process so that it is not used again.
     */
    private function crashed(): CompletedWorkUnit
    {
        assert($this->currentUnit !== null);

        if ($this->currentResultFile !== null) {
            @unlink($this->currentResultFile);
        }

        if ($this->currentDoneFile !== null) {
            @unlink($this->currentDoneFile);
        }

        if ($this->currentStreamFile !== null) {
            @unlink($this->currentStreamFile);
        }

        $completed = new CompletedWorkUnit($this->currentUnit, '', null, true);

        if ($this->job !== null) {
            $this->job->wait();

            $this->job = null;
        }

        $this->clearCurrentUnit();

        return $completed;
    }

    /**
     * @return non-empty-string
     */
    private function newToken(): string
    {
        return $this->id . '_' . bin2hex(random_bytes(16));
    }

    private function clearCurrentUnit(): void
    {
        $this->currentUnit          = null;
        $this->currentNonce         = null;
        $this->currentResultFile    = null;
        $this->currentDoneFile      = null;
        $this->currentStreamFile    = null;
        $this->currentStreamOffset  = 0;
        $this->currentStreamTainted = false;
    }

    /**
     * @throws WorkerException
     *
     * @return non-empty-string
     */
    private function buildWorkerCode(): string
    {
        $configuration = ConfigurationRegistry::get();

        $bootstrap = '';

        if ($configuration->hasBootstrap()) {
            $bootstrap = $configuration->bootstrap();
        }

        if (defined('PHPUNIT_COMPOSER_INSTALL')) {
            $composerAutoload = var_export(PHPUNIT_COMPOSER_INSTALL, true);
        } else {
            // @codeCoverageIgnoreStart
            $composerAutoload = '\'\'';
            // @codeCoverageIgnoreEnd
        }

        if (defined('__PHPUNIT_PHAR__')) {
            // @codeCoverageIgnoreStart
            $phar = var_export(__PHPUNIT_PHAR__, true);
            // @codeCoverageIgnoreEnd
        } else {
            $phar = '\'\'';
        }

        if (CodeCoverage::instance()->isActive()) {
            $coverage = 'true';
        } else {
            // @codeCoverageIgnoreStart
            $coverage = 'false';
            // @codeCoverageIgnoreEnd
        }

        $includePath = var_export(get_include_path(), true);
        $includePath = "'." . $includePath . ".'";

        $template = new Template(__DIR__ . '/templates/worker.tpl');

        $template->setVar(
            [
                'composerAutoload'               => $composerAutoload,
                'phar'                           => $phar,
                'collectCodeCoverageInformation' => $coverage,
                'iniSettings'                    => '',
                'include_path'                   => $includePath,
                'serializedConfiguration'        => $this->saveConfigurationForChildProcess(),
                'sourceMapFile'                  => $this->sourceMapFileForChildProcess(),
                'bootstrap'                      => $bootstrap,
            ],
        );

        $code = $template->render();

        assert($code !== '');

        return $code;
    }

    /**
     * @throws WorkerException
     */
    private function saveConfigurationForChildProcess(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'phpunit_');

        if ($path === false) {
            // @codeCoverageIgnoreStart
            throw new WorkerException('Unable to create temporary file for the worker configuration');
            // @codeCoverageIgnoreEnd
        }

        if (!ConfigurationRegistry::saveTo($path)) {
            // @codeCoverageIgnoreStart
            throw new WorkerException('Unable to write the worker configuration to a temporary file');
            // @codeCoverageIgnoreEnd
        }

        $this->temporaryFiles[] = $path;

        return $path;
    }

    private function sourceMapFileForChildProcess(): string
    {
        if (!ConfigurationRegistry::get()->source()->notEmpty()) {
            // @codeCoverageIgnoreStart
            return '';
            // @codeCoverageIgnoreEnd
        }

        $path = tempnam(sys_get_temp_dir(), 'phpunit_');

        if ($path === false) {
            // @codeCoverageIgnoreStart
            return '';
            // @codeCoverageIgnoreEnd
        }

        if (!SourceMapper::saveTo($path, ConfigurationRegistry::get()->source())) {
            // @codeCoverageIgnoreStart
            return '';
            // @codeCoverageIgnoreEnd
        }

        $this->temporaryFiles[] = $path;

        return $path;
    }
}
