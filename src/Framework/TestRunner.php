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

use const PHP_EOL;
use function assert;
use function class_exists;
use function defined;
use function extension_loaded;
use function file_exists;
use function file_get_contents;
use function get_include_path;
use function hrtime;
use function restore_error_handler;
use function serialize;
use function set_error_handler;
use function sprintf;
use function sys_get_temp_dir;
use function tempnam;
use function trim;
use function unlink;
use function unserialize;
use function var_export;
use AssertionError;
use ErrorException;
use PHPUnit\Event\Code\TestMethodBuilder;
use PHPUnit\Event\Code\ThrowableBuilder;
use PHPUnit\Event\Facade;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Metadata\Api\CodeCoverage as CodeCoverageMetadataApi;
use PHPUnit\Metadata\Parser\Registry as MetadataRegistry;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\Runner\ErrorHandler;
use PHPUnit\TestRunner\TestResult\PassedTests;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;
use PHPUnit\Util\GlobalState;
use PHPUnit\Util\PHP\Job;
use PHPUnit\Util\PHP\JobRunnerRegistry;
use PHPUnit\Util\PHP\PhpProcessException;
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
            Facade::emitter()->testConsideredRisky(
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
                    Facade::emitter()->testTriggeredPhpunitWarning(
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
                Facade::emitter()->testConsideredRisky(
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
            Facade::emitter()->testConsideredRisky(
                $test->valueObjectForEvents(),
                'This test did not perform any assertions',
            );
        }

        if ($test->doesNotPerformAssertions() &&
            $test->numberOfAssertionsPerformed() > 0) {
            Facade::emitter()->testConsideredRisky(
                $test->valueObjectForEvents(),
                sprintf(
                    'This test is not expected to perform assertions but performed %d assertion%s',
                    $test->numberOfAssertionsPerformed(),
                    $test->numberOfAssertionsPerformed() > 1 ? 's' : '',
                ),
            );
        }

        if ($test->hasUnexpectedOutput()) {
            Facade::emitter()->testPrintedUnexpectedOutput($test->output());
        }

        if ($this->configuration->disallowTestOutput() && $test->hasUnexpectedOutput()) {
            Facade::emitter()->testConsideredRisky(
                $test->valueObjectForEvents(),
                sprintf(
                    'This test printed output: %s',
                    $test->output(),
                ),
            );
        }

        if ($test->wasPrepared()) {
            Facade::emitter()->testFinished(
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

        $code = $template->render();

        assert($code !== '');

        $this->runTestJob($code, $test, $processResultFile);

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
            Facade::emitter()->testConsideredRisky(
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

    /**
     * @psalm-param non-empty-string $code
     *
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws PhpProcessException
     */
    private function runTestJob(string $code, Test $test, string $processResultFile): void
    {
        $result = JobRunnerRegistry::run(new Job($code));

        $processResult = '';

        if (file_exists($processResultFile)) {
            $processResult = file_get_contents($processResultFile);

            @unlink($processResultFile);
        }

        $this->processChildResult(
            $test,
            $processResult,
            $result->stderr(),
        );
    }

    /**
     * @throws Exception
     * @throws NoPreviousThrowableException
     */
    private function processChildResult(Test $test, string $stdout, string $stderr): void
    {
        if (!empty($stderr)) {
            $exception = new Exception(trim($stderr));

            assert($test instanceof TestCase);

            Facade::emitter()->testErrored(
                TestMethodBuilder::fromTestCase($test),
                ThrowableBuilder::from($exception),
            );

            return;
        }

        set_error_handler(
            /**
             * @throws ErrorException
             */
            static function (int $errno, string $errstr, string $errfile, int $errline): never
            {
                throw new ErrorException($errstr, $errno, $errno, $errfile, $errline);
            },
        );

        try {
            $childResult = unserialize($stdout);

            restore_error_handler();

            if ($childResult === false) {
                $exception = new AssertionFailedError('Test was run in child process and ended unexpectedly');

                assert($test instanceof TestCase);

                Facade::emitter()->testErrored(
                    TestMethodBuilder::fromTestCase($test),
                    ThrowableBuilder::from($exception),
                );

                Facade::emitter()->testFinished(
                    TestMethodBuilder::fromTestCase($test),
                    0,
                );
            }
        } catch (ErrorException $e) {
            restore_error_handler();

            $childResult = false;

            $exception = new Exception(trim($stdout), 0, $e);

            assert($test instanceof TestCase);

            Facade::emitter()->testErrored(
                TestMethodBuilder::fromTestCase($test),
                ThrowableBuilder::from($exception),
            );
        }

        if ($childResult !== false) {
            if (!empty($childResult['output'])) {
                $output = $childResult['output'];
            }

            Facade::instance()->forward($childResult['events']);
            PassedTests::instance()->import($childResult['passedTests']);

            assert($test instanceof TestCase);

            $test->setResult($childResult['testResult']);
            $test->addToAssertionCount($childResult['numAssertions']);

            if (CodeCoverage::instance()->isActive() && $childResult['codeCoverage'] instanceof \SebastianBergmann\CodeCoverage\CodeCoverage) {
                CodeCoverage::instance()->codeCoverage()->merge(
                    $childResult['codeCoverage'],
                );
            }
        }

        if (!empty($output)) {
            print $output;
        }
    }
}
