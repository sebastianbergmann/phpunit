<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use PHPUnit\TestRunner\TestResult\PassedTests;
use const PHP_EOL;
use function assert;
use function class_exists;
use function defined;
use function extension_loaded;
use function get_include_path;
use function hrtime;
use function serialize;
use function sprintf;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;
use function var_export;
use AssertionError;
use PHPUnit\Event;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Metadata\Api\CodeCoverage as CodeCoverageMetadataApi;
use PHPUnit\Metadata\Parser\Registry as MetadataRegistry;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\Runner\ErrorHandler;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;
use PHPUnit\Util\GlobalState;
use PHPUnit\Util\PHP\AbstractPhpProcess;
use ReflectionClass;
use SebastianBergmann\CodeCoverage\Exception as OriginalCodeCoverageException;
use SebastianBergmann\CodeCoverage\InvalidArgumentException;
use SebastianBergmann\CodeCoverage\StaticAnalysisCacheNotConfiguredException;
use SebastianBergmann\CodeCoverage\UnintentionallyCoveredCodeException;
use SebastianBergmann\Invoker\Invoker;
use SebastianBergmann\Invoker\TimeoutException;
use SebastianBergmann\Template\Template;
use Throwable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestRunner
{
    private ?bool $timeLimitCanBeEnforced = null;
    private readonly Configuration $configuration;

    public function __construct()
    {
        $this->configuration = ConfigurationRegistry::get();
    }

    /**
     * @throws \PHPUnit\Runner\Exception
     * @throws CodeCoverageException
     * @throws InvalidArgumentException
     * @throws UnintentionallyCoveredCodeException
     */
    public function run(TestCase $test): void
    {
        Assert::resetCount();

        $codeCoverageMetadataApi = new CodeCoverageMetadataApi;

        $shouldCodeCoverageBeCollected = $codeCoverageMetadataApi->shouldCodeCoverageBeCollectedFor(
            $test::class,
            $test->name(),
        );

        $error      = false;
        $failure    = false;
        $incomplete = false;
        $risky      = false;
        $skipped    = false;

        if ($this->shouldErrorHandlerBeUsed($test)) {
            ErrorHandler::instance()->enable();
        }

        $collectCodeCoverage = CodeCoverage::instance()->isActive() &&
                               $shouldCodeCoverageBeCollected;

        if ($collectCodeCoverage) {
            CodeCoverage::instance()->start($test);
        }

        try {
            if ($this->canTimeLimitBeEnforced() &&
                $this->shouldTimeLimitBeEnforced($test)) {
                $risky = $this->runTestWithTimeout($test);
            } else {
                $test->runBare();
            }
        } catch (AssertionFailedError $e) {
            $failure = true;

            if ($e instanceof IncompleteTestError) {
                $incomplete = true;
            } elseif ($e instanceof SkippedTest) {
                $skipped = true;
            }
        } catch (AssertionError $e) {
            $test->addToAssertionCount(1);

            $failure = true;
            $frame   = $e->getTrace()[0];

            assert(isset($frame['file']));
            assert(isset($frame['line']));

            $e = new AssertionFailedError(
                sprintf(
                    '%s in %s:%s',
                    $e->getMessage(),
                    $frame['file'],
                    $frame['line'],
                ),
            );
        } catch (Throwable $e) {
            $error = true;
        }

        $test->addToAssertionCount(Assert::getCount());

        if ($this->configuration->reportUselessTests() &&
            !$test->doesNotPerformAssertions() &&
            $test->numberOfAssertionsPerformed() === 0) {
            $risky = true;
        }

        if (!$error && !$failure && !$incomplete && !$skipped && !$risky &&
            $this->configuration->requireCoverageMetadata() &&
            !$this->hasCoverageMetadata($test::class, $test->name())) {
            Event\Facade::emitter()->testConsideredRisky(
                $test->valueObjectForEvents(),
                'This test does not define a code coverage target but is expected to do so',
            );

            $risky = true;
        }

        if ($collectCodeCoverage) {
            $append           = !$risky && !$incomplete && !$skipped;
            $linesToBeCovered = [];
            $linesToBeUsed    = [];

            if ($append) {
                try {
                    $linesToBeCovered = $codeCoverageMetadataApi->linesToBeCovered(
                        $test::class,
                        $test->name(),
                    );

                    $linesToBeUsed = $codeCoverageMetadataApi->linesToBeUsed(
                        $test::class,
                        $test->name(),
                    );
                } catch (InvalidCoversTargetException $cce) {
                    Event\Facade::emitter()->testTriggeredPhpunitWarning(
                        $test->valueObjectForEvents(),
                        $cce->getMessage(),
                    );

                    $append = false;
                }
            }

            try {
                CodeCoverage::instance()->stop(
                    $append,
                    $linesToBeCovered,
                    $linesToBeUsed,
                );
            } catch (UnintentionallyCoveredCodeException $cce) {
                Event\Facade::emitter()->testConsideredRisky(
                    $test->valueObjectForEvents(),
                    'This test executed code that is not listed as code to be covered or used:' .
                    PHP_EOL .
                    $cce->getMessage(),
                );
            } catch (OriginalCodeCoverageException $cce) {
                $error = true;

                $e = $e ?? $cce;
            }
        }

        ErrorHandler::instance()->disable();

        if (!$error &&
            !$incomplete &&
            !$skipped &&
            $this->configuration->reportUselessTests() &&
            !$test->doesNotPerformAssertions() &&
            $test->numberOfAssertionsPerformed() === 0) {
            Event\Facade::emitter()->testConsideredRisky(
                $test->valueObjectForEvents(),
                'This test did not perform any assertions',
            );
        }

        if ($test->doesNotPerformAssertions() &&
            $test->numberOfAssertionsPerformed() > 0) {
            Event\Facade::emitter()->testConsideredRisky(
                $test->valueObjectForEvents(),
                sprintf(
                    'This test is not expected to perform assertions but performed %d assertion%s',
                    $test->numberOfAssertionsPerformed(),
                    $test->numberOfAssertionsPerformed() > 1 ? 's' : '',
                ),
            );
        }

        if ($test->hasUnexpectedOutput()) {
            Event\Facade::emitter()->testPrintedUnexpectedOutput($test->output());
        }

        if ($this->configuration->disallowTestOutput() && $test->hasUnexpectedOutput()) {
            Event\Facade::emitter()->testConsideredRisky(
                $test->valueObjectForEvents(),
                sprintf(
                    'This test printed output: %s',
                    $test->output(),
                ),
            );
        }

        if ($test->wasPrepared()) {
            Event\Facade::emitter()->testFinished(
                $test->valueObjectForEvents(),
                $test->numberOfAssertionsPerformed(),
            );
        }
    }

    /**
     * @throws \PHPUnit\Runner\Exception
     * @throws \PHPUnit\Util\Exception
     * @throws \SebastianBergmann\Template\InvalidArgumentException
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws ProcessIsolationException
     * @throws StaticAnalysisCacheNotConfiguredException
     */
    public function runInSeparateProcess(TestCase $test, bool $runEntireClass, bool $preserveGlobalState): void
    {
        if ($this->isPcntlForkAvailable()) {
            // forking the parent process is a more lightweight way to run a test in isolation.
            // it requires the pcntl extension though.
            $this->runInFork($test);
            return;
        }

        // running in a separate process is slow, but works in most situations.
        $this->runInWorkerProcess($test, $runEntireClass, $preserveGlobalState);
    }

    private function isPcntlForkAvailable(): bool {
        $disabledFunctions = ini_get('disable_functions');

        return
            function_exists('pcntl_fork')
            && !str_contains($disabledFunctions, 'pcntl')
            && function_exists('socket_create_pair')
            && !str_contains($disabledFunctions, 'socket')
        ;
    }

    // IPC inspired from https://github.com/barracudanetworks/forkdaemon-php
    private const SOCKET_HEADER_SIZE = 4;

    private function ipc_init(): array
    {
        // windows needs AF_INET
        $domain = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ? AF_INET : AF_UNIX;

        // create a socket pair for IPC
        $sockets = array();
        if (socket_create_pair($domain, SOCK_STREAM, 0, $sockets) === false)
        {
            throw new \RuntimeException('socket_create_pair failed: ' . socket_strerror(socket_last_error()));
        }

        return $sockets;
    }

    /**
     * @param resource $socket
     */
    private function socket_receive($socket): mixed
    {
        // initially read to the length of the header size, then
        // expand to read more
        $bytes_total = self::SOCKET_HEADER_SIZE;
        $bytes_read = 0;
        $have_header = false;
        $socket_message = '';
        while ($bytes_read < $bytes_total)
        {
            $read = @socket_read($socket, $bytes_total - $bytes_read);
            if ($read === false)
            {
                throw new \RuntimeException('socket_receive error: ' . socket_strerror(socket_last_error()));
            }

            // blank socket_read means done
            if ($read == '')
            {
                break;
            }

            $bytes_read += strlen($read);
            $socket_message .= $read;

            if (!$have_header && $bytes_read >= self::SOCKET_HEADER_SIZE)
            {
                $have_header = true;
                list($bytes_total) = array_values(unpack('N', $socket_message));
                $bytes_read = 0;
                $socket_message = '';
            }
        }

        return @unserialize($socket_message);
    }

    /**
     * @param resource $socket
     * @param mixed $message
     */
    private function socket_send($socket, $message): void
    {
        $serialized_message = @serialize($message);
        if ($serialized_message == false)
        {
            throw new \RuntimeException('socket_send failed to serialize message');
        }

        $header = pack('N', strlen($serialized_message));
        $data = $header . $serialized_message;
        $bytes_left = strlen($data);
        while ($bytes_left > 0)
        {
            $bytes_sent = @socket_write($socket, $data);
            if ($bytes_sent === false)
            {
                throw new \RuntimeException('socket_send failed to write to socket');
            }

            $bytes_left -= $bytes_sent;
            $data = substr($data, $bytes_sent);
        }
    }

    private function runInFork(TestCase $test): void
    {
        list($socket_child, $socket_parent) = $this->ipc_init();

        $pid = pcntl_fork();

        if ($pid === -1 ) {
            throw new \Exception('could not fork');
        } else if ($pid) {
            // we are the parent

            socket_close($socket_parent);

            // read child stdout, stderr
            $result = $this->socket_receive($socket_child);

            $stderr = '';
            $stdout = '';
            if (is_array($result) && array_key_exists('error', $result)) {
                $stderr = $result['error'];
            } else {
                $stdout = $result;
            }

            $php = AbstractPhpProcess::factory();
            $php->processChildResult($test, $stdout, $stderr);

        } else {
            // we are the child

            socket_close($socket_child);

            $offset                  = hrtime();
            $dispatcher = Event\Facade::instance()->initForIsolation(
                \PHPUnit\Event\Telemetry\HRTime::fromSecondsAndNanoseconds(
                    $offset[0],
                    $offset[1]
                )
            );

            $test->setInIsolation(true);
            try {
                $test->run();
            } catch (Throwable $e) {
                $this->socket_send($socket_parent, ['error' => $e->getMessage()]);
                exit();
            }

            $result = serialize(
                [
                    'testResult'    => $test->result(),
                    'codeCoverage'  => CodeCoverage::instance()->isActive() ? CodeCoverage::instance()->codeCoverage() : null,
                    'numAssertions' => $test->numberOfAssertionsPerformed(),
                    'output'        => !$test->expectsOutput() ? $test->output() : '',
                    'events'        => $dispatcher->flush(),
                    'passedTests'   => PassedTests::instance()
                ]
            );

            // send result into parent
            $this->socket_send($socket_parent, $result);
            exit();
        }
    }

    private function runInWorkerProcess(TestCase $test, bool $runEntireClass, bool $preserveGlobalState): void
    {
        $class = new ReflectionClass($test);

        if ($runEntireClass) {
            $template = new Template(
                __DIR__ . '/../Util/PHP/Template/TestCaseClass.tpl',
            );
        } else {
            $template = new Template(
                __DIR__ . '/../Util/PHP/Template/TestCaseMethod.tpl',
            );
        }

        $bootstrap     = '';
        $constants     = '';
        $globals       = '';
        $includedFiles = '';
        $iniSettings   = '';

        if (ConfigurationRegistry::get()->hasBootstrap()) {
            $bootstrap = ConfigurationRegistry::get()->bootstrap();
        }

        if ($preserveGlobalState) {
            $constants     = GlobalState::getConstantsAsString();
            $globals       = GlobalState::getGlobalsAsString();
            $includedFiles = GlobalState::getIncludedFilesAsString();
            $iniSettings   = GlobalState::getIniSettingsAsString();
        }

        $coverage         = CodeCoverage::instance()->isActive() ? 'true' : 'false';
        $linesToBeIgnored = var_export(CodeCoverage::instance()->linesToBeIgnored(), true);

        if (defined('PHPUNIT_COMPOSER_INSTALL')) {
            $composerAutoload = var_export(PHPUNIT_COMPOSER_INSTALL, true);
        } else {
            $composerAutoload = '\'\'';
        }

        if (defined('__PHPUNIT_PHAR__')) {
            $phar = var_export(__PHPUNIT_PHAR__, true);
        } else {
            $phar = '\'\'';
        }

        $data            = var_export(serialize($test->providedData()), true);
        $dataName        = var_export($test->dataName(), true);
        $dependencyInput = var_export(serialize($test->dependencyInput()), true);
        $includePath     = var_export(get_include_path(), true);
        // must do these fixes because TestCaseMethod.tpl has unserialize('{data}') in it, and we can't break BC
        // the lines above used to use addcslashes() rather than var_export(), which breaks null byte escape sequences
        $data                    = "'." . $data . ".'";
        $dataName                = "'.(" . $dataName . ").'";
        $dependencyInput         = "'." . $dependencyInput . ".'";
        $includePath             = "'." . $includePath . ".'";
        $offset                  = hrtime();
        $serializedConfiguration = $this->saveConfigurationForChildProcess();
        $processResultFile       = tempnam(sys_get_temp_dir(), 'phpunit_');

        $var = [
            'bootstrap'                      => $bootstrap,
            'composerAutoload'               => $composerAutoload,
            'phar'                           => $phar,
            'filename'                       => $class->getFileName(),
            'className'                      => $class->getName(),
            'collectCodeCoverageInformation' => $coverage,
            'linesToBeIgnored'               => $linesToBeIgnored,
            'data'                           => $data,
            'dataName'                       => $dataName,
            'dependencyInput'                => $dependencyInput,
            'constants'                      => $constants,
            'globals'                        => $globals,
            'include_path'                   => $includePath,
            'included_files'                 => $includedFiles,
            'iniSettings'                    => $iniSettings,
            'name'                           => $test->name(),
            'offsetSeconds'                  => $offset[0],
            'offsetNanoseconds'              => $offset[1],
            'serializedConfiguration'        => $serializedConfiguration,
            'processResultFile'              => $processResultFile,
        ];

        if (!$runEntireClass) {
            $var['methodName'] = $test->name();
        }

        $template->setVar($var);

        $php = AbstractPhpProcess::factory();
        $php->runTestJob($template->render(), $test, $processResultFile);

        @unlink($serializedConfiguration);
    }

    /**
     * @psalm-param class-string $className
     * @psalm-param non-empty-string $methodName
     */
    private function hasCoverageMetadata(string $className, string $methodName): bool
    {
        foreach (MetadataRegistry::parser()->forClassAndMethod($className, $methodName) as $metadata) {
            if ($metadata->isCovers()) {
                return true;
            }

            if ($metadata->isCoversClass()) {
                return true;
            }

            if ($metadata->isCoversTrait()) {
                return true;
            }

            if ($metadata->isCoversMethod()) {
                return true;
            }

            if ($metadata->isCoversFunction()) {
                return true;
            }

            if ($metadata->isCoversNothing()) {
                return true;
            }
        }

        return false;
    }

    private function canTimeLimitBeEnforced(): bool
    {
        if ($this->timeLimitCanBeEnforced !== null) {
            return $this->timeLimitCanBeEnforced;
        }

        if (!class_exists(Invoker::class)) {
            $this->timeLimitCanBeEnforced = false;

            return $this->timeLimitCanBeEnforced;
        }

        $this->timeLimitCanBeEnforced = (new Invoker)->canInvokeWithTimeout();

        return $this->timeLimitCanBeEnforced;
    }

    private function shouldTimeLimitBeEnforced(TestCase $test): bool
    {
        if (!$this->configuration->enforceTimeLimit()) {
            return false;
        }

        if (!(($this->configuration->defaultTimeLimit() || $test->size()->isKnown()))) {
            return false;
        }

        if (extension_loaded('xdebug') && xdebug_is_debugger_active()) {
            return false;
        }

        return true;
    }

    /**
     * @throws Throwable
     */
    private function runTestWithTimeout(TestCase $test): bool
    {
        $_timeout = $this->configuration->defaultTimeLimit();
        $testSize = $test->size();

        if ($testSize->isSmall()) {
            $_timeout = $this->configuration->timeoutForSmallTests();
        } elseif ($testSize->isMedium()) {
            $_timeout = $this->configuration->timeoutForMediumTests();
        } elseif ($testSize->isLarge()) {
            $_timeout = $this->configuration->timeoutForLargeTests();
        }

        try {
            (new Invoker)->invoke([$test, 'runBare'], [], $_timeout);
        } catch (TimeoutException) {
            Event\Facade::emitter()->testConsideredRisky(
                $test->valueObjectForEvents(),
                sprintf(
                    'This test was aborted after %d second%s',
                    $_timeout,
                    $_timeout !== 1 ? 's' : '',
                ),
            );

            return true;
        }

        return false;
    }

    /**
     * @throws ProcessIsolationException
     */
    private function saveConfigurationForChildProcess(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'phpunit_');

        if ($path === false) {
            throw new ProcessIsolationException;
        }

        if (!ConfigurationRegistry::saveTo($path)) {
            throw new ProcessIsolationException;
        }

        return $path;
    }

    private function shouldErrorHandlerBeUsed(TestCase $test): bool
    {
        if (MetadataRegistry::parser()->forMethod($test::class, $test->name())->isWithoutErrorHandler()->isNotEmpty()) {
            return false;
        }

        return true;
    }
}
