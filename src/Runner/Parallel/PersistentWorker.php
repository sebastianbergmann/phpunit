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
use function defined;
use function feof;
use function fgets;
use function file_get_contents;
use function get_include_path;
use function hrtime;
use function is_resource;
use function json_encode;
use function random_bytes;
use function serialize;
use function sprintf;
use function str_contains;
use function str_ends_with;
use function stream_get_contents;
use function stream_set_blocking;
use function sys_get_temp_dir;
use function tempnam;
use function trim;
use function unlink;
use function var_export;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestRunner\ChildProcessResultProcessor;
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
 * channel (its standard input).
 *
 * Unlike the SeparateProcessTestRunner, which spawns one process per test for
 * isolation, a PersistentWorker amortizes the cost of bootstrapping PHPUnit
 * across all of the tests it runs. The tests executed by a single worker share
 * one process and therefore do not get the per-test global-state isolation that
 * process isolation provides; this is the trade-off that makes the worker
 * suitable as a building block for parallel test execution.
 *
 * The result of each test is transported back to the parent process using the
 * very same serialized envelope that process isolation uses, so that the
 * ChildProcessResultProcessor can reconstitute it unchanged.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class PersistentWorker
{
    /**
     * Marker written by the worker to its control channel to signal that a
     * test has finished and its result has been written to the result file.
     * Must be kept in sync with templates/worker.tpl.
     */
    private const string DONE_PREFIX = 'PHPUNIT_WORKER_DONE:';
    private readonly JobRunner $jobRunner;
    private readonly ChildProcessResultProcessor $processor;
    private ?RunningJob $job = null;

    /**
     * @var list<string>
     */
    private array $temporaryFiles = [];

    /**
     * The unit currently being executed by this worker, together with the
     * bookkeeping needed to harvest its result once the worker reports that it
     * has finished. These are set by dispatch() and cleared by tick() (or by a
     * detected crash); a null currentUnit means the worker is idle.
     */
    private ?WorkUnit $currentUnit = null;

    /**
     * @var ?non-empty-string
     */
    private ?string $currentNonce        = null;
    private ?string $currentResultFile   = null;
    private string $controlChannelBuffer = '';

    /**
     * @var non-negative-int
     */
    private readonly int $id;

    /**
     * @param non-negative-int $id
     */
    public function __construct(JobRunner $jobRunner, ChildProcessResultProcessor $processor, int $id = 0)
    {
        $this->jobRunner = $jobRunner;
        $this->processor = $processor;
        $this->id        = $id;
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
        $environmentVariables = [
            'PHPUNIT_WORKER_ID' => (string) $this->id,
        ];

        $this->job = $this->jobRunner->start(
            new Job($this->buildWorkerCode(), [], $environmentVariables),
        );
    }

    /**
     * @throws WorkerException
     */
    public function run(TestCase $test): void
    {
        assert($this->job !== null);

        $class = new ReflectionClass($test);
        $file  = $class->getFileName();

        assert($file !== false);

        $offset     = hrtime();
        $nonce      = bin2hex(random_bytes(16));
        $resultFile = tempnam(sys_get_temp_dir(), 'phpunit_');

        if ($resultFile === false) {
            // @codeCoverageIgnoreStart
            throw new WorkerException('Unable to create temporary file for the worker result');
            // @codeCoverageIgnoreEnd
        }

        $command = [
            'command'           => 'run',
            'file'              => $file,
            'className'         => $class->getName(),
            'methodName'        => $test->name(),
            'data'              => base64_encode(serialize($test->providedData())),
            'dataName'          => $test->dataName(),
            'dependencyInput'   => base64_encode(serialize($test->dependencyInput())),
            'repetition'        => $test->repetition(),
            'totalRepetitions'  => $test->totalRepetitions(),
            'attempt'           => $test->attempt(),
            'maxAttempts'       => $test->maxAttempts(),
            'offsetSeconds'     => $offset[0],
            'offsetNanoseconds' => $offset[1],
            'resultFile'        => $resultFile,
            'nonce'             => $nonce,
        ];

        $encodedCommand = json_encode($command);

        assert($encodedCommand !== false);

        $this->job->write($encodedCommand . "\n");

        if (!$this->awaitResult($nonce)) {
            $result    = $this->job->wait();
            $this->job = null;

            @unlink($resultFile);

            $this->processor->process($test, '', $result->stderr(), $nonce);

            return;
        }

        $serializedResult = file_get_contents($resultFile);

        @unlink($resultFile);

        if ($serializedResult === false) {
            // @codeCoverageIgnoreStart
            $serializedResult = '';
            // @codeCoverageIgnoreEnd
        }

        $this->processor->process($test, $serializedResult, '', $nonce);
    }

    /**
     * Send a unit of work to the worker without waiting for it to finish.
     *
     * The command is written to the worker's control channel and dispatch()
     * returns immediately; the caller is expected to multiplex this worker's
     * standard output with stream_select() and to call tick() once output
     * becomes available, so that a single thread of control can keep several
     * workers busy at the same time.
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

        $command = $this->testClassCommand($unit, $offset, $resultFile, $nonce);

        $encodedCommand = json_encode($command);

        assert($encodedCommand !== false);

        $this->currentUnit          = $unit;
        $this->currentNonce         = $nonce;
        $this->currentResultFile    = $resultFile;
        $this->controlChannelBuffer = '';

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
     * The worker's standard output, to be passed to stream_select() so that
     * the caller can wait for the worker to report progress without blocking.
     *
     * @return ?resource
     */
    public function controlChannel(): mixed
    {
        if ($this->job === null) {
            return null;
        }

        return $this->job->stdout();
    }

    /**
     * Consume whatever the worker has written to its control channel since the
     * last call and, if the dispatched unit has finished, harvest its result.
     *
     * Returns null while the unit is still running. Intended to be called after
     * stream_select() has reported the stream returned by controlChannel() as
     * ready.
     */
    public function tick(): ?CompletedWorkUnit
    {
        assert($this->currentUnit !== null);
        assert($this->currentNonce !== null);

        if ($this->job === null) {
            // @codeCoverageIgnoreStart
            return $this->crashed();
            // @codeCoverageIgnoreEnd
        }

        $stdout = $this->job->stdout();

        if (!is_resource($stdout)) {
            // @codeCoverageIgnoreStart
            return $this->crashed();
            // @codeCoverageIgnoreEnd
        }

        stream_set_blocking($stdout, false);

        $chunk = stream_get_contents($stdout);

        if ($chunk !== false && $chunk !== '') {
            $this->controlChannelBuffer .= $chunk;
        }

        if (str_contains($this->controlChannelBuffer, self::DONE_PREFIX . $this->currentNonce)) {
            return $this->finished();
        }

        if (feof($stdout)) {
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
     * @param non-empty-string      $nonce
     *
     * @throws WorkerException
     *
     * @return array<string, mixed>
     */
    private function testClassCommand(TestClassWorkUnit $unit, array $offset, string $resultFile, string $nonce): array
    {
        $class = new ReflectionClass($unit->className());
        $file  = $class->getFileName();

        assert($file !== false);

        $tests = [];

        foreach ($unit->tests() as $test) {
            try {
                $data            = base64_encode(serialize($test->providedData()));
                $dependencyInput = base64_encode(serialize($test->dependencyInput()));
            } catch (Throwable $t) {
                @unlink($resultFile);

                throw new WorkerException(
                    sprintf(
                        'The tests of class %s cannot be run in parallel because their data cannot be serialized: %s',
                        $unit->className(),
                        $t->getMessage(),
                    ),
                );
            }

            $tests[] = [
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

        return [
            'command'           => 'runUnit',
            'file'              => $file,
            'className'         => $unit->className(),
            'tests'             => $tests,
            'offsetSeconds'     => $offset[0],
            'offsetNanoseconds' => $offset[1],
            'resultFile'        => $resultFile,
            'nonce'             => $nonce,
        ];
    }

    /**
     * Read the worker's control channel until it reports that the test
     * identified by the given nonce has finished. Any unrelated output the
     * worker may have written to the channel is skipped. Returns false if the
     * worker terminated before reporting completion.
     *
     * The marker is the last thing the worker writes on its line, followed by
     * a newline. Stray output that the test produced on the control channel
     * without a trailing newline therefore fuses with the marker as a prefix
     * of the same line; matching the marker as a suffix tolerates this instead
     * of being defeated by it.
     */
    private function awaitResult(string $nonce): bool
    {
        assert($this->job !== null);

        $stdout = $this->job->stdout();

        if (!is_resource($stdout)) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        $expected = self::DONE_PREFIX . $nonce;

        while (($line = fgets($stdout)) !== false) {
            if (str_ends_with(trim($line), $expected)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Harvest the result of the unit the worker has just reported as finished.
     */
    private function finished(): CompletedWorkUnit
    {
        assert($this->currentUnit !== null);
        assert($this->currentResultFile !== null);

        $serializedResult = file_get_contents($this->currentResultFile);

        @unlink($this->currentResultFile);

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
        $this->currentUnit          = null;
        $this->currentNonce         = null;
        $this->currentResultFile    = null;
        $this->controlChannelBuffer = '';
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
