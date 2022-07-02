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
use function defined;
use function get_include_path;
use function hrtime;
use function serialize;
use function sprintf;
use function sys_get_temp_dir;
use function tempnam;
use function var_export;
use AssertionError;
use PHPUnit\Event;
use PHPUnit\Metadata\Api\CodeCoverage as CodeCoverageMetadataApi;
use PHPUnit\Metadata\Parser\Registry as MetadataRegistry;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Configuration\Registry;
use PHPUnit\Util\Error\Handler as ErrorHandler;
use PHPUnit\Util\GlobalState;
use PHPUnit\Util\PHP\AbstractPhpProcess;
use ReflectionClass;
use SebastianBergmann\CodeCoverage\Exception as OriginalCodeCoverageException;
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
    private Configuration $configuration;

    public function __construct()
    {
        $this->configuration = Registry::get();
    }

    /**
     * @throws \SebastianBergmann\CodeCoverage\InvalidArgumentException
     * @throws CodeCoverageException
     * @throws UnintentionallyCoveredCodeException
     */
    public function run(TestCase $test, TestResult $result): void
    {
        Assert::resetCount();

        if ($this->configuration->registerMockObjectsFromTestArgumentsRecursively()) {
            $test->registerMockObjectsFromTestArgumentsRecursively();
        }

        $shouldCodeCoverageBeCollected = (new CodeCoverageMetadataApi)->shouldCodeCoverageBeCollectedFor(
            $test::class,
            $test->getName(false)
        );

        $error      = false;
        $failure    = false;
        $warning    = false;
        $incomplete = false;
        $risky      = false;
        $skipped    = false;

        $result->startTest($test);

        ErrorHandler::instance()->enable();

        $collectCodeCoverage = CodeCoverage::isActive() &&
                               !$test instanceof ErrorTestCase &&
                               !$test instanceof WarningTestCase &&
                               $shouldCodeCoverageBeCollected;

        if ($collectCodeCoverage) {
            CodeCoverage::start($test);
        }

        try {
            if ($this->canTimeLimitBeEnforced() &&
                $this->shouldTimeLimitBeEnforced($test)) {
                $risky = $this->runTestWithTimeout($test, $result);
            } else {
                $test->runBare();
            }
        } catch (AssertionFailedError $e) {
            $failure = true;

            if ($e instanceof RiskyTest) {
                $risky = true;
            } elseif ($e instanceof IncompleteTestError) {
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
                    $frame['line']
                )
            );
        } catch (Warning $e) {
            $warning = true;
        } catch (Exception $e) {
            $error = true;
        } catch (Throwable $e) {
            $e     = new ExceptionWrapper($e);
            $error = true;
        }

        $test->addToAssertionCount(Assert::getCount());

        if ($this->configuration->reportUselessTests() &&
            $test->numberOfAssertionsPerformed() === 0) {
            $risky = true;
        }

        if (!$error && !$failure && !$warning && !$incomplete && !$skipped && !$risky &&
            $this->configuration->requireCoverageMetadata() &&
            !$this->hasCoverageMetadata($test::class, $test->getName(false))) {
            $riskyDueToMissingCodeCoverageMetadataException = new RiskyDueToMissingCodeCoverageMetadataException;

            Event\Facade::emitter()->testConsideredRisky(
                $test->valueObjectForEvents(),
                $riskyDueToMissingCodeCoverageMetadataException->getMessage()
            );

            $result->addFailure(
                $test,
                $riskyDueToMissingCodeCoverageMetadataException,
            );

            $risky = true;
        }

        if ($collectCodeCoverage) {
            $append           = !$risky && !$incomplete && !$skipped;
            $linesToBeCovered = [];
            $linesToBeUsed    = [];

            if ($append) {
                try {
                    $linesToBeCovered = (new CodeCoverageMetadataApi)->linesToBeCovered(
                        $test::class,
                        $test->getName(false)
                    );

                    $linesToBeUsed = (new CodeCoverageMetadataApi)->linesToBeUsed(
                        $test::class,
                        $test->getName(false)
                    );
                } catch (InvalidCoversTargetException $cce) {
                    Event\Facade::emitter()->testTriggeredPhpunitWarning(
                        $test->valueObjectForEvents(),
                        $cce->getMessage()
                    );

                    $result->addWarning(
                        $test,
                        new Warning(
                            $cce->getMessage()
                        ),
                    );
                }
            }

            try {
                CodeCoverage::stop(
                    $append,
                    $linesToBeCovered,
                    $linesToBeUsed
                );
            } catch (UnintentionallyCoveredCodeException $cce) {
                $unintentionallyCoveredCodeError = new RiskyDueToUnintentionallyCoveredCodeException(
                    'This test executed code that is not listed as code to be covered or used:' .
                    PHP_EOL . $cce->getMessage()
                );
            } catch (OriginalCodeCoverageException $cce) {
                $error = true;

                $e = $e ?? $cce;
            }
        }

        ErrorHandler::instance()->disable();

        if ($error && isset($e)) {
            $result->addError($test, $e);
        } elseif ($failure && isset($e)) {
            $result->addFailure($test, $e);
        } elseif ($warning && isset($e)) {
            $result->addWarning($test, $e);
        } elseif (isset($unintentionallyCoveredCodeError)) {
            Event\Facade::emitter()->testConsideredRisky(
                $test->valueObjectForEvents(),
                $unintentionallyCoveredCodeError->getMessage()
            );

            $result->addFailure(
                $test,
                $unintentionallyCoveredCodeError,
            );
        } elseif ($this->configuration->reportUselessTests() &&
            !$test->doesNotPerformAssertions() &&
            $test->numberOfAssertionsPerformed() === 0) {
            $riskyBecauseNoAssertionsWerePerformedException = new RiskyBecauseNoAssertionsWerePerformedException;

            $result->addFailure(
                $test,
                $riskyBecauseNoAssertionsWerePerformedException,
            );

            Event\Facade::emitter()->testConsideredRisky(
                $test->valueObjectForEvents(),
                $riskyBecauseNoAssertionsWerePerformedException->getMessage()
            );
        } elseif ($this->configuration->reportUselessTests() &&
            $test->doesNotPerformAssertions() &&
            $test->numberOfAssertionsPerformed() > 0) {
            $riskyDueToUnexpectedAssertionsException = new RiskyDueToUnexpectedAssertionsException(
                $test->numberOfAssertionsPerformed()
            );

            $result->addFailure(
                $test,
                $riskyDueToUnexpectedAssertionsException,
            );

            Event\Facade::emitter()->testConsideredRisky(
                $test->valueObjectForEvents(),
                $riskyDueToUnexpectedAssertionsException->getMessage()
            );
        } elseif ($this->configuration->disallowTestOutput() && $test->hasOutput()) {
            $riskyDueToOutputException = new RiskyDueToOutputException(
                sprintf(
                    'This test printed output: %s',
                    $test->output()
                )
            );

            $result->addFailure(
                $test,
                $riskyDueToOutputException,
            );

            Event\Facade::emitter()->testConsideredRisky(
                $test->valueObjectForEvents(),
                $riskyDueToOutputException->getMessage()
            );
        }

        if ($test->wasPrepared()) {
            Event\Facade::emitter()->testFinished(
                $test->valueObjectForEvents(),
                $test->numberOfAssertionsPerformed()
            );
        }
    }

    public function runInSeparateProcess(TestCase $test, TestResult $result, bool $runEntireClass, bool $preserveGlobalState): void
    {
        $class = new ReflectionClass($test);

        if ($runEntireClass) {
            $template = new Template(
                __DIR__ . '/../Util/PHP/Template/TestCaseClass.tpl'
            );
        } else {
            $template = new Template(
                __DIR__ . '/../Util/PHP/Template/TestCaseMethod.tpl'
            );
        }

        if ($preserveGlobalState) {
            $constants     = GlobalState::getConstantsAsString();
            $globals       = GlobalState::getGlobalsAsString();
            $includedFiles = GlobalState::getIncludedFilesAsString();
            $iniSettings   = GlobalState::getIniSettingsAsString();
        } else {
            $constants = '';

            if (!empty($GLOBALS['__PHPUNIT_BOOTSTRAP'])) {
                $globals = '$GLOBALS[\'__PHPUNIT_BOOTSTRAP\'] = ' . var_export($GLOBALS['__PHPUNIT_BOOTSTRAP'], true) . ";\n";
            } else {
                $globals = '';
            }

            $includedFiles = '';
            $iniSettings   = '';
        }

        $coverage = CodeCoverage::isActive() ? 'true' : 'false';

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

        $codeCoverageFilter         = null;
        $cachesStaticAnalysis       = 'false';
        $codeCoverageCacheDirectory = null;
        $pathCoverage               = 'false';

        if (CodeCoverage::isActive()) {
            $codeCoverageFilter = CodeCoverage::instance()->filter();

            if (CodeCoverage::instance()->collectsBranchAndPathCoverage()) {
                $pathCoverage = 'true';
            }

            if (CodeCoverage::instance()->cachesStaticAnalysis()) {
                $cachesStaticAnalysis       = 'true';
                $codeCoverageCacheDirectory = CodeCoverage::instance()->cacheDirectory();
            }
        }

        $data                       = var_export(serialize($test->getProvidedData()), true);
        $dataName                   = var_export($test->dataName(), true);
        $dependencyInput            = var_export(serialize($test->dependencyInput()), true);
        $includePath                = var_export(get_include_path(), true);
        $codeCoverageFilter         = var_export(serialize($codeCoverageFilter), true);
        $codeCoverageCacheDirectory = var_export(serialize($codeCoverageCacheDirectory), true);
        // must do these fixes because TestCaseMethod.tpl has unserialize('{data}') in it, and we can't break BC
        // the lines above used to use addcslashes() rather than var_export(), which breaks null byte escape sequences
        $data                       = "'." . $data . ".'";
        $dataName                   = "'.(" . $dataName . ").'";
        $dependencyInput            = "'." . $dependencyInput . ".'";
        $includePath                = "'." . $includePath . ".'";
        $codeCoverageFilter         = "'." . $codeCoverageFilter . ".'";
        $codeCoverageCacheDirectory = "'." . $codeCoverageCacheDirectory . ".'";

        $configurationFilePath = $GLOBALS['__PHPUNIT_CONFIGURATION_FILE'] ?? '';

        $offset = hrtime(false);

        $serializedConfiguration = $this->saveConfigurationForChildProcess();

        $var = [
            'composerAutoload'               => $composerAutoload,
            'phar'                           => $phar,
            'filename'                       => $class->getFileName(),
            'className'                      => $class->getName(),
            'collectCodeCoverageInformation' => $coverage,
            'cachesStaticAnalysis'           => $cachesStaticAnalysis,
            'codeCoverageCacheDirectory'     => $codeCoverageCacheDirectory,
            'pathCoverage'                   => $pathCoverage,
            'data'                           => $data,
            'dataName'                       => $dataName,
            'dependencyInput'                => $dependencyInput,
            'constants'                      => $constants,
            'globals'                        => $globals,
            'include_path'                   => $includePath,
            'included_files'                 => $includedFiles,
            'iniSettings'                    => $iniSettings,
            'codeCoverageFilter'             => $codeCoverageFilter,
            'configurationFilePath'          => $configurationFilePath,
            'name'                           => $test->getName(false),
            'offsetSeconds'                  => $offset[0],
            'offsetNanoseconds'              => $offset[1],
            'serializedConfiguration'        => $serializedConfiguration,
        ];

        if (!$runEntireClass) {
            $var['methodName'] = $test->getName(false);
        }

        $template->setVar($var);

        $php = AbstractPhpProcess::factory();
        $php->runTestJob($template->render(), $test, $result);

        @unlink($serializedConfiguration);
    }

    /**
     * @psalm-param class-string $className
     */
    private function hasCoverageMetadata(string $className, string $methodName): bool
    {
        $metadata = MetadataRegistry::parser()->forClassAndMethod($className, $methodName);

        if ($metadata->isCovers()->isNotEmpty()) {
            return true;
        }

        if ($metadata->isCoversClass()->isNotEmpty()) {
            return true;
        }

        if ($metadata->isCoversFunction()->isNotEmpty()) {
            return true;
        }

        if ($metadata->isCoversNothing()->isNotEmpty()) {
            return true;
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
        if ($test instanceof ErrorTestCase) {
            return false;
        }

        if ($test instanceof WarningTestCase) {
            return false;
        }

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
    private function runTestWithTimeout(TestCase $test, TestResult $result): bool
    {
        $_timeout = $this->configuration->defaultTimeLimit();

        if ($test->size()->isSmall()) {
            $_timeout = $this->configuration->timeoutForSmallTests();
        } elseif ($test->size()->isMedium()) {
            $_timeout = $this->configuration->timeoutForMediumTests();
        } elseif ($test->size()->isLarge()) {
            $_timeout = $this->configuration->timeoutForLargeTests();
        }

        try {
            (new Invoker)->invoke([$test, 'runBare'], [], $_timeout);
        } catch (TimeoutException) {
            $riskyDueToTimeoutException = new RiskyDueToTimeoutException($_timeout);

            $result->addFailure(
                $test,
                $riskyDueToTimeoutException,
            );

            Event\Facade::emitter()->testConsideredRisky(
                $test->valueObjectForEvents(),
                $riskyDueToTimeoutException->getMessage()
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
        $path = tempnam(sys_get_temp_dir(), 'PHPUnit');

        if (!$path) {
            throw new ProcessIsolationException;
        }

        if (!Registry::saveTo($path)) {
            throw new ProcessIsolationException;
        }

        return $path;
    }
}
