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
use function fgets;
use function file_get_contents;
use function get_include_path;
use function hrtime;
use function is_resource;
use function json_encode;
use function random_bytes;
use function serialize;
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

    public function __construct(JobRunner $jobRunner, ChildProcessResultProcessor $processor)
    {
        $this->jobRunner = $jobRunner;
        $this->processor = $processor;
    }

    /**
     * @throws WorkerException
     */
    public function start(): void
    {
        $this->job = $this->jobRunner->start(new Job($this->buildWorkerCode()));
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
     * Read the worker's control channel until it reports that the test
     * identified by the given nonce has finished. Any unrelated output the
     * worker may have written to the channel is skipped. Returns false if the
     * worker terminated before reporting completion.
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
            if (trim($line) === $expected) {
                return true;
            }
        }

        return false;
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
