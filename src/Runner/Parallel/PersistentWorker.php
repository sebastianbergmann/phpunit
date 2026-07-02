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
use function sys_get_temp_dir;
use function tempnam;
use function unlink;
use function var_export;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\RepeatTestSuite;
use PHPUnit\Framework\RetryTestSuite;
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

    /**
     * @var non-negative-int
     */
    private readonly int $id;

    /**
     * @var non-empty-string
     */
    private readonly string $token;

    /**
     * @param non-negative-int $id
     */
    public function __construct(JobRunner $jobRunner, int $id = 0)
    {
        $this->jobRunner = $jobRunner;
        $this->id        = $id;
        $this->token     = $id . '_' . bin2hex(random_bytes(16));
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

        $this->job = $this->jobRunner->start(
            new Job($this->buildWorkerCode(), [], $environmentVariables),
        );
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

        $doneFile = $resultFile . '.done';

        $command = $this->testClassCommand($unit, $offset, $resultFile, $doneFile, $nonce);

        $encodedCommand = json_encode($command);

        assert($encodedCommand !== false);

        $this->currentUnit       = $unit;
        $this->currentNonce      = $nonce;
        $this->currentResultFile = $resultFile;
        $this->currentDoneFile   = $doneFile;

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
     * Returns null while the unit is still running. The caller is expected to
     * call this repeatedly, sleeping briefly between rounds, until it returns a
     * completed unit.
     */
    public function poll(): ?CompletedWorkUnit
    {
        assert($this->currentUnit !== null);
        assert($this->currentDoneFile !== null);

        if (is_file($this->currentDoneFile)) {
            return $this->finished();
        }

        if ($this->job === null || !$this->job->isRunning()) {
            return $this->crashed();
        }

        return null;
    }

    public function stop(): void
    {
        if ($this->job !== null) {
            $encodedCommand = json_encode(['command' => 'stop']);

            assert($encodedCommand !== false);

            $this->job->write($encodedCommand . "\n");
            $this->job->closeStdin();

            $result = $this->job->wait();

            EventFacade::emitter()->childProcessFinished($result->stdout(), $result->stderr());

            $this->job = null;
        }

        foreach ($this->temporaryFiles as $temporaryFile) {
            @unlink($temporaryFile);
        }

        $this->temporaryFiles = [];
    }

    /**
     * @param array{0: int, 1: int} $offset
     * @param non-empty-string      $resultFile
     * @param non-empty-string      $doneFile
     * @param non-empty-string      $nonce
     *
     * @throws WorkerException
     *
     * @return array<string, mixed>
     */
    private function testClassCommand(TestClassWorkUnit $unit, array $offset, string $resultFile, string $doneFile, string $nonce): array
    {
        $class = new ReflectionClass($unit->className());
        $file  = $class->getFileName();

        assert($file !== false);

        $tests = [];

        foreach ($unit->tests() as $test) {
            if ($test instanceof RetryTestSuite) {
                $aggregated = $test->tests();

                assert(count($aggregated) === 1 && $aggregated[0] instanceof TestCase);

                $tests[] = [
                    'type'        => 'retry',
                    'name'        => $test->name(),
                    'maxAttempts' => $test->maxAttempts(),
                    'test'        => $this->testDescriptor($aggregated[0], $unit->className(), $resultFile),
                ];

                continue;
            }

            if ($test instanceof RepeatTestSuite) {
                $repetitions = [];

                foreach ($test->tests() as $repetition) {
                    assert($repetition instanceof TestCase);

                    $repetitions[] = $this->testDescriptor($repetition, $unit->className(), $resultFile);
                }

                $tests[] = [
                    'type'             => 'repeat',
                    'name'             => $test->name(),
                    'failureThreshold' => $test->failureThreshold(),
                    'tests'            => $repetitions,
                ];

                continue;
            }

            assert($test instanceof TestCase);

            $tests[] = $this->testDescriptor($test, $unit->className(), $resultFile);
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
            'nonce'             => $nonce,
        ];
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

        $serializedResult = file_get_contents($this->currentResultFile);

        @unlink($this->currentResultFile);
        @unlink($this->currentDoneFile);

        if ($serializedResult === false) {
            // @codeCoverageIgnoreStart
            $serializedResult = '';
            // @codeCoverageIgnoreEnd
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

        $completed = new CompletedWorkUnit($this->currentUnit, '', null, true);

        if ($this->job !== null) {
            $this->job->wait();

            $this->job = null;
        }

        $this->clearCurrentUnit();

        return $completed;
    }

    private function clearCurrentUnit(): void
    {
        $this->currentUnit       = null;
        $this->currentNonce      = null;
        $this->currentResultFile = null;
        $this->currentDoneFile   = null;
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
