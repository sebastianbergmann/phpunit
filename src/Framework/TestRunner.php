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
use PHPUnit\Util\Error\Handler;
use PHPUnit\Util\GlobalState;
use PHPUnit\Util\PHP\AbstractPhpProcess;
use ReflectionClass;
use SebastianBergmann\CodeCoverage\Exception as OriginalCodeCoverageException;
use SebastianBergmann\CodeCoverage\UnintentionallyCoveredCodeException;
use SebastianBergmann\Invoker\Invoker;
use SebastianBergmann\Invoker\TimeoutException;
use SebastianBergmann\Template\Template;
use SebastianBergmann\Timer\Timer;
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

        if ($this->configuration->convertDeprecationsToExceptions() ||
            $this->configuration->convertErrorsToExceptions() ||
            $this->configuration->convertNoticesToExceptions() ||
            $this->configuration->convertWarningsToExceptions()) {
            $errorHandler = new Handler(
                $this->configuration->convertDeprecationsToExceptions(),
                $this->configuration->convertErrorsToExceptions(),
                $this->configuration->convertNoticesToExceptions(),
                $this->configuration->convertWarningsToExceptions()
            );

            $errorHandler->register();
        }

        $collectCodeCoverage = CodeCoverage::isActive() &&
                               !$test instanceof ErrorTestCase &&
                               !$test instanceof WarningTestCase &&
                               $shouldCodeCoverageBeCollected;

        if ($collectCodeCoverage) {
            CodeCoverage::start($test);
        }

        $timer = new Timer;
        $timer->start();

        try {
            if ($this->canTimeLimitBeEnforced() &&
                $this->shouldTimeLimitBeEnforced($test, $result)) {
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

        $time = $timer->stop()->asSeconds();

        $test->addToAssertionCount(Assert::getCount());

        if ($this->configuration->reportUselessTests() &&
            $test->numberOfAssertionsPerformed() === 0) {
            $risky = true;
        }

        if (!$error && !$failure && !$warning && !$incomplete && !$skipped && !$risky &&
            $this->configuration->requireCoverageMetadata() &&
            !$this->hasCoverageMetadata($test::class, $test->getName(false))) {
            $result->addFailure(
                $test,
                new RiskyDueToMissingCodeCoverageMetadataException,
                $time
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
                    $result->addWarning(
                        $test,
                        new Warning(
                            $cce->getMessage()
                        ),
                        $time
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

        if (isset($errorHandler)) {
            $errorHandler->unregister();

            unset($errorHandler);
        }

        if ($error && isset($e)) {
            $result->addError($test, $e, $time);
        } elseif ($failure && isset($e)) {
            $result->addFailure($test, $e, $time);
        } elseif ($warning && isset($e)) {
            $result->addWarning($test, $e, $time);
        } elseif (isset($unintentionallyCoveredCodeError)) {
            $result->addFailure(
                $test,
                $unintentionallyCoveredCodeError,
                $time
            );
        } elseif ($this->configuration->reportUselessTests() &&
            !$test->doesNotPerformAssertions() &&
            $test->numberOfAssertionsPerformed() === 0) {
            $result->addFailure(
                $test,
                new RiskyBecauseNoAssertionsWerePerformedException,
                $time
            );
        } elseif ($this->configuration->reportUselessTests() &&
            $test->doesNotPerformAssertions() &&
            $test->numberOfAssertionsPerformed() > 0) {
            $result->addFailure(
                $test,
                new RiskyDueToUnexpectedAssertionsException(
                    $test->numberOfAssertionsPerformed()
                ),
                $time
            );
        } elseif ($this->configuration->disallowTestOutput() && $test->hasOutput()) {
            $result->addFailure(
                $test,
                new RiskyDueToOutputException(
                    sprintf(
                        'This test printed output: %s',
                        $test->output()
                    )
                ),
                $time
            );
        }

        $result->endTest($test, $time);

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

    private function shouldTimeLimitBeEnforced(TestCase $test, TestResult $result): bool
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
            $result->addFailure(
                $test,
                new RiskyDueToTimeoutException($_timeout),
                $_timeout
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
