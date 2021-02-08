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
use function get_class;
use function get_include_path;
use function serialize;
use function sprintf;
use function var_export;
use AssertionError;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\Util\Error\Handler;
use PHPUnit\Util\ExcludeList;
use PHPUnit\Util\GlobalState;
use PHPUnit\Util\PHP\AbstractPhpProcess;
use PHPUnit\Util\Test as TestUtil;
use ReflectionClass;
use SebastianBergmann\CodeCoverage\Exception as OriginalCodeCoverageException;
use SebastianBergmann\CodeCoverage\UnintentionallyCoveredCodeException;
use SebastianBergmann\Invoker\Invoker;
use SebastianBergmann\Invoker\TimeoutException;
use SebastianBergmann\ResourceOperations\ResourceOperations;
use SebastianBergmann\Template\Template;
use SebastianBergmann\Timer\Timer;
use Throwable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestRunner
{
    /**
     * @throws \SebastianBergmann\CodeCoverage\InvalidArgumentException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws CodeCoverageException
     * @throws UnintentionallyCoveredCodeException
     */
    public function run(TestCase $test, TestResult $result): void
    {
        Assert::resetCount();

        if ($result->shouldMockObjectsFromTestArgumentsBeRegisteredRecursively()) {
            $test->registerMockObjectsFromTestArgumentsRecursively();
        }

        $shouldCodeCoverageBeCollected = TestUtil::shouldCodeCoverageBeCollectedFor(
            get_class($test),
            $test->getName(false)
        );

        $error      = false;
        $failure    = false;
        $warning    = false;
        $incomplete = false;
        $risky      = false;
        $skipped    = false;

        $result->startTest($test);

        if ($result->shouldDeprecationsBeConvertedToExceptions() ||
            $result->shouldErrorsBeConvertedToExceptions() ||
            $result->shouldNoticeBeConvertedToExceptions() ||
            $result->shouldWarningsBeConvertedToExceptions()) {
            $errorHandler = new Handler(
                $result->shouldDeprecationsBeConvertedToExceptions(),
                $result->shouldErrorsBeConvertedToExceptions(),
                $result->shouldNoticeBeConvertedToExceptions(),
                $result->shouldWarningsBeConvertedToExceptions()
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

        $monitorFunctions = $result->isStrictAboutResourceUsageDuringSmallTests() &&
            !$test instanceof ErrorTestCase &&
            !$test instanceof WarningTestCase &&
            $test->size()->isSmall() &&
            function_exists('xdebug_start_function_monitor');

        if ($monitorFunctions) {
            /* @noinspection ForgottenDebugOutputInspection */
            xdebug_start_function_monitor(ResourceOperations::getFunctions());
        }

        $timer = new Timer;
        $timer->start();

        try {
            $invoker = new Invoker;

            if (!$test instanceof ErrorTestCase &&
                !$test instanceof WarningTestCase &&
                $result->enforcesTimeLimit() &&
                ($result->defaultTimeLimit() || $test->size()->isKnown()) &&
                $invoker->canInvokeWithTimeout()) {
                $_timeout = $result->defaultTimeLimit();

                if ($test->size()->isSmall()) {
                    $_timeout = $result->timeoutForSmallTests();
                } elseif ($test->size()->isMedium()) {
                    $_timeout = $result->timeoutForMediumTests();
                } elseif ($test->size()->isLarge()) {
                    $_timeout = $result->timeoutForLargeTests();
                }

                $invoker->invoke([$test, 'runBare'], [], $_timeout);
            } else {
                $test->runBare();
            }
        } catch (TimeoutException $e) {
            $result->addFailure(
                $test,
                new RiskyTestError(
                    $e->getMessage()
                ),
                $_timeout
            );

            $risky = true;
        } catch (AssertionFailedError $e) {
            $failure = true;

            if ($e instanceof RiskyTestError) {
                $risky = true;
            } elseif ($e instanceof IncompleteTestError) {
                $incomplete = true;
            } elseif ($e instanceof SkippedTestError) {
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

        if ($monitorFunctions) {
            $excludeList = new ExcludeList;

            /** @noinspection ForgottenDebugOutputInspection */
            $functions = xdebug_get_monitored_functions();

            /* @noinspection ForgottenDebugOutputInspection */
            xdebug_stop_function_monitor();

            foreach ($functions as $function) {
                if (!$excludeList->isExcluded($function['filename'])) {
                    $result->addFailure(
                        $test,
                        new RiskyTestError(
                            sprintf(
                                '%s() used in %s:%s',
                                $function['function'],
                                $function['filename'],
                                $function['lineno']
                            )
                        ),
                        $time
                    );
                }
            }
        }

        if ($result->isStrictAboutTestsThatDoNotTestAnything() &&
            $test->numberOfAssertionsPerformed() === 0) {
            $risky = true;
        }

        if ($result->enforcesCoversAnnotation() && !$error && !$failure && !$warning && !$incomplete && !$skipped && !$risky) {
            $annotations = TestUtil::parseTestMethodAnnotations(
                get_class($test),
                $test->getName(false)
            );

            if (!isset($annotations['class']['covers']) &&
                !isset($annotations['method']['covers']) &&
                !isset($annotations['class']['coversNothing']) &&
                !isset($annotations['method']['coversNothing'])) {
                $result->addFailure(
                    $test,
                    new MissingCoversAnnotationException(
                        'This test does not have a @covers annotation but is expected to have one'
                    ),
                    $time
                );

                $risky = true;
            }
        }

        if ($collectCodeCoverage) {
            $append           = !$risky && !$incomplete && !$skipped;
            $linesToBeCovered = [];
            $linesToBeUsed    = [];

            if ($append) {
                try {
                    $linesToBeCovered = \PHPUnit\Util\Test::linesToBeCovered(
                        get_class($test),
                        $test->getName(false)
                    );

                    $linesToBeUsed = \PHPUnit\Util\Test::getLinesToBeUsed(
                        get_class($test),
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
                $unintentionallyCoveredCodeError = new UnintentionallyCoveredCodeError(
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

        if ($error) {
            $result->addError($test, $e, $time);
        } elseif ($failure) {
            $result->addFailure($test, $e, $time);
        } elseif ($warning) {
            $result->addWarning($test, $e, $time);
        } elseif (isset($unintentionallyCoveredCodeError)) {
            $result->addFailure(
                $test,
                $unintentionallyCoveredCodeError,
                $time
            );
        } elseif ($result->isStrictAboutTestsThatDoNotTestAnything() &&
            !$test->doesNotPerformAssertions() &&
            $test->numberOfAssertionsPerformed() === 0) {
            $reflected = new ReflectionClass($test);
            $name      = $test->getName(false);

            if ($name && $reflected->hasMethod($name)) {
                $reflected = $reflected->getMethod($name);
            }

            $result->addFailure(
                $test,
                new RiskyTestError(
                    sprintf(
                        "This test did not perform any assertions\n\n%s:%d",
                        $reflected->getFileName(),
                        $reflected->getStartLine()
                    )
                ),
                $time
            );
        } elseif ($result->isStrictAboutTestsThatDoNotTestAnything() &&
            $test->doesNotPerformAssertions() &&
            $test->numberOfAssertionsPerformed() > 0) {
            $result->addFailure(
                $test,
                new RiskyTestError(
                    sprintf(
                        'This test is annotated with "@doesNotPerformAssertions" but performed %d assertions',
                        $test->numberOfAssertionsPerformed()
                    )
                ),
                $time
            );
        } elseif ($result->isStrictAboutOutputDuringTests() && $test->hasOutput()) {
            $result->addFailure(
                $test,
                new OutputError(
                    sprintf(
                        'This test printed output: %s',
                        $test->output()
                    )
                ),
                $time
            );
        } elseif ($result->isStrictAboutTodoAnnotatedTests()) {
            $annotations = TestUtil::parseTestMethodAnnotations(
                get_class($test),
                $test->getName(false)
            );

            if (isset($annotations['method']['todo'])) {
                $result->addFailure(
                    $test,
                    new RiskyTestError(
                        'Test method is annotated with @todo'
                    ),
                    $time
                );
            }
        }

        $result->endTest($test, $time);
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

        $coverage                                   = CodeCoverage::isActive() ? 'true' : 'false';
        $isStrictAboutTestsThatDoNotTestAnything    = $result->isStrictAboutTestsThatDoNotTestAnything() ? 'true' : 'false';
        $isStrictAboutOutputDuringTests             = $result->isStrictAboutOutputDuringTests() ? 'true' : 'false';
        $enforcesTimeLimit                          = $result->enforcesTimeLimit() ? 'true' : 'false';
        $isStrictAboutTodoAnnotatedTests            = $result->isStrictAboutTodoAnnotatedTests() ? 'true' : 'false';
        $isStrictAboutResourceUsageDuringSmallTests = $result->isStrictAboutResourceUsageDuringSmallTests() ? 'true' : 'false';

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

        $var = [
            'composerAutoload'                           => $composerAutoload,
            'phar'                                       => $phar,
            'filename'                                   => $class->getFileName(),
            'className'                                  => $class->getName(),
            'collectCodeCoverageInformation'             => $coverage,
            'cachesStaticAnalysis'                       => $cachesStaticAnalysis,
            'codeCoverageCacheDirectory'                 => $codeCoverageCacheDirectory,
            'pathCoverage'                               => $pathCoverage,
            'data'                                       => $data,
            'dataName'                                   => $dataName,
            'dependencyInput'                            => $dependencyInput,
            'constants'                                  => $constants,
            'globals'                                    => $globals,
            'include_path'                               => $includePath,
            'included_files'                             => $includedFiles,
            'iniSettings'                                => $iniSettings,
            'isStrictAboutTestsThatDoNotTestAnything'    => $isStrictAboutTestsThatDoNotTestAnything,
            'isStrictAboutOutputDuringTests'             => $isStrictAboutOutputDuringTests,
            'enforcesTimeLimit'                          => $enforcesTimeLimit,
            'isStrictAboutTodoAnnotatedTests'            => $isStrictAboutTodoAnnotatedTests,
            'isStrictAboutResourceUsageDuringSmallTests' => $isStrictAboutResourceUsageDuringSmallTests,
            'codeCoverageFilter'                         => $codeCoverageFilter,
            'configurationFilePath'                      => $configurationFilePath,
            'name'                                       => $test->getName(false),
        ];

        if (!$runEntireClass) {
            $var['methodName'] = $test->getName(false);
        }

        $template->setVar($var);

        $php = AbstractPhpProcess::factory();
        $php->runTestJob($template->render(), $test, $result);
    }
}
