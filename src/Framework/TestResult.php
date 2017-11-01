<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Framework;

use AssertionError;
use Countable;
use Error;
use PHP_Invoker;
use PHP_Invoker_TimeoutException;
use PHP_Timer;
use PHPUnit\Framework\MockObject\Exception as MockObjectException;
use PHPUnit\Util\Blacklist;
use PHPUnit\Util\InvalidArgumentHelper;
use PHPUnit\Util\Printer;
use ReflectionClass;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\CoveredCodeNotExecutedException as OriginalCoveredCodeNotExecutedException;
use SebastianBergmann\CodeCoverage\Exception as OriginalCodeCoverageException;
use SebastianBergmann\CodeCoverage\MissingCoversAnnotationException as OriginalMissingCoversAnnotationException;
use SebastianBergmann\CodeCoverage\UnintentionallyCoveredCodeException;
use SebastianBergmann\ResourceOperations\ResourceOperations;
use Throwable;

/**
 * A TestResult collects the results of executing a test case.
 */
class TestResult implements Countable
{
    /**
     * @var array
     */
    protected $passed = [];

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var array
     */
    protected $failures = [];

    /**
     * @var array
     */
    protected $warnings = [];

    /**
     * @var array
     */
    protected $notImplemented = [];

    /**
     * @var array
     */
    protected $risky = [];

    /**
     * @var array
     */
    protected $skipped = [];

    /**
     * @var array
     */
    protected $listeners = [];

    /**
     * @var int
     */
    protected $runTests = 0;

    /**
     * @var float
     */
    protected $time = 0;

    /**
     * @var TestSuite
     */
    protected $topTestSuite;

    /**
     * Code Coverage information.
     *
     * @var CodeCoverage
     */
    protected $codeCoverage;

    /**
     * @var bool
     */
    protected $convertErrorsToExceptions = true;

    /**
     * @var bool
     */
    protected $stop = false;

    /**
     * @var bool
     */
    protected $stopOnError = false;

    /**
     * @var bool
     */
    protected $stopOnFailure = false;

    /**
     * @var bool
     */
    protected $stopOnWarning = false;

    /**
     * @var bool
     */
    protected $beStrictAboutTestsThatDoNotTestAnything = true;

    /**
     * @var bool
     */
    protected $beStrictAboutOutputDuringTests = false;

    /**
     * @var bool
     */
    protected $beStrictAboutTodoAnnotatedTests = false;

    /**
     * @var bool
     */
    protected $beStrictAboutResourceUsageDuringSmallTests = false;

    /**
     * @var bool
     */
    protected $enforceTimeLimit = false;

    /**
     * @var int
     */
    protected $timeoutForSmallTests = 1;

    /**
     * @var int
     */
    protected $timeoutForMediumTests = 10;

    /**
     * @var int
     */
    protected $timeoutForLargeTests = 60;

    /**
     * @var bool
     */
    protected $stopOnRisky = false;

    /**
     * @var bool
     */
    protected $stopOnIncomplete = false;

    /**
     * @var bool
     */
    protected $stopOnSkipped = false;

    /**
     * @var bool
     */
    protected $lastTestFailed = false;

    /**
     * @var bool
     */
    private $registerMockObjectsFromTestArgumentsRecursively = false;

    /**
     * Registers a TestListener.
     *
     * @param TestListener $listener
     */
    public function addListener(TestListener $listener)
    {
        $this->listeners[] = $listener;
    }

    /**
     * Unregisters a TestListener.
     *
     * @param TestListener $listener
     */
    public function removeListener(TestListener $listener)
    {
        foreach ($this->listeners as $key => $_listener) {
            if ($listener === $_listener) {
                unset($this->listeners[$key]);
            }
        }
    }

    /**
     * Flushes all flushable TestListeners.
     */
    public function flushListeners()
    {
        foreach ($this->listeners as $listener) {
            if ($listener instanceof Printer) {
                $listener->flush();
            }
        }
    }

    /**
     * Adds an error to the list of errors.
     *
     * @param Test      $test
     * @param Throwable $t
     * @param float     $time
     */
    public function addError(Test $test, Throwable $t, $time)
    {
        if ($t instanceof RiskyTest) {
            $this->risky[] = new TestFailure($test, $t);
            $notifyMethod  = 'addRiskyTest';

            if ($test instanceof TestCase) {
                $test->markAsRisky();
            }

            if ($this->stopOnRisky) {
                $this->stop();
            }
        } elseif ($t instanceof IncompleteTest) {
            $this->notImplemented[] = new TestFailure($test, $t);
            $notifyMethod           = 'addIncompleteTest';

            if ($this->stopOnIncomplete) {
                $this->stop();
            }
        } elseif ($t instanceof SkippedTest) {
            $this->skipped[] = new TestFailure($test, $t);
            $notifyMethod    = 'addSkippedTest';

            if ($this->stopOnSkipped) {
                $this->stop();
            }
        } else {
            $this->errors[] = new TestFailure($test, $t);
            $notifyMethod   = 'addError';

            if ($this->stopOnError || $this->stopOnFailure) {
                $this->stop();
            }
        }

        // @see https://github.com/sebastianbergmann/phpunit/issues/1953
        if ($t instanceof Error) {
            $t = new ExceptionWrapper($t);
        }

        foreach ($this->listeners as $listener) {
            $listener->$notifyMethod($test, $t, $time);
        }

        $this->lastTestFailed = true;
        $this->time += $time;
    }

    /**
     * Adds a warning to the list of warnings.
     * The passed in exception caused the warning.
     *
     * @param Test    $test
     * @param Warning $e
     * @param float   $time
     */
    public function addWarning(Test $test, Warning $e, $time)
    {
        if ($this->stopOnWarning) {
            $this->stop();
        }

        $this->warnings[] = new TestFailure($test, $e);

        foreach ($this->listeners as $listener) {
            $listener->addWarning($test, $e, $time);
        }

        $this->time += $time;
    }

    /**
     * Adds a failure to the list of failures.
     * The passed in exception caused the failure.
     *
     * @param Test                 $test
     * @param AssertionFailedError $e
     * @param float                $time
     */
    public function addFailure(Test $test, AssertionFailedError $e, $time)
    {
        if ($e instanceof RiskyTest || $e instanceof OutputError) {
            $this->risky[] = new TestFailure($test, $e);
            $notifyMethod  = 'addRiskyTest';

            if ($test instanceof TestCase) {
                $test->markAsRisky();
            }

            if ($this->stopOnRisky) {
                $this->stop();
            }
        } elseif ($e instanceof IncompleteTest) {
            $this->notImplemented[] = new TestFailure($test, $e);
            $notifyMethod           = 'addIncompleteTest';

            if ($this->stopOnIncomplete) {
                $this->stop();
            }
        } elseif ($e instanceof SkippedTest) {
            $this->skipped[] = new TestFailure($test, $e);
            $notifyMethod    = 'addSkippedTest';

            if ($this->stopOnSkipped) {
                $this->stop();
            }
        } else {
            $this->failures[] = new TestFailure($test, $e);
            $notifyMethod     = 'addFailure';

            if ($this->stopOnFailure) {
                $this->stop();
            }
        }

        foreach ($this->listeners as $listener) {
            $listener->$notifyMethod($test, $e, $time);
        }

        $this->lastTestFailed = true;
        $this->time += $time;
    }

    /**
     * Informs the result that a testsuite will be started.
     *
     * @param TestSuite $suite
     */
    public function startTestSuite(TestSuite $suite)
    {
        if ($this->topTestSuite === null) {
            $this->topTestSuite = $suite;
        }

        foreach ($this->listeners as $listener) {
            $listener->startTestSuite($suite);
        }
    }

    /**
     * Informs the result that a testsuite was completed.
     *
     * @param TestSuite $suite
     */
    public function endTestSuite(TestSuite $suite)
    {
        foreach ($this->listeners as $listener) {
            $listener->endTestSuite($suite);
        }
    }

    /**
     * Informs the result that a test will be started.
     *
     * @param Test $test
     */
    public function startTest(Test $test)
    {
        $this->lastTestFailed = false;
        $this->runTests += \count($test);

        foreach ($this->listeners as $listener) {
            $listener->startTest($test);
        }
    }

    /**
     * Informs the result that a test was completed.
     *
     * @param Test  $test
     * @param float $time
     */
    public function endTest(Test $test, $time)
    {
        foreach ($this->listeners as $listener) {
            $listener->endTest($test, $time);
        }

        if (!$this->lastTestFailed && $test instanceof TestCase) {
            $class = \get_class($test);
            $key   = $class . '::' . $test->getName();

            $this->passed[$key] = [
                'result' => $test->getResult(),
                'size'   => \PHPUnit\Util\Test::getSize(
                    $class,
                    $test->getName(false)
                )
            ];

            $this->time += $time;
        }
    }

    /**
     * Returns true if no risky test occurred.
     *
     * @return bool
     */
    public function allHarmless()
    {
        return $this->riskyCount() == 0;
    }

    /**
     * Gets the number of risky tests.
     *
     * @return int
     */
    public function riskyCount()
    {
        return \count($this->risky);
    }

    /**
     * Returns true if no incomplete test occurred.
     *
     * @return bool
     */
    public function allCompletelyImplemented()
    {
        return $this->notImplementedCount() == 0;
    }

    /**
     * Gets the number of incomplete tests.
     *
     * @return int
     */
    public function notImplementedCount()
    {
        return \count($this->notImplemented);
    }

    /**
     * Returns an Enumeration for the risky tests.
     *
     * @return array
     */
    public function risky()
    {
        return $this->risky;
    }

    /**
     * Returns an Enumeration for the incomplete tests.
     *
     * @return array
     */
    public function notImplemented()
    {
        return $this->notImplemented;
    }

    /**
     * Returns true if no test has been skipped.
     *
     * @return bool
     */
    public function noneSkipped()
    {
        return $this->skippedCount() == 0;
    }

    /**
     * Gets the number of skipped tests.
     *
     * @return int
     */
    public function skippedCount()
    {
        return \count($this->skipped);
    }

    /**
     * Returns an Enumeration for the skipped tests.
     *
     * @return array
     */
    public function skipped()
    {
        return $this->skipped;
    }

    /**
     * Gets the number of detected errors.
     *
     * @return int
     */
    public function errorCount()
    {
        return \count($this->errors);
    }

    /**
     * Returns an Enumeration for the errors.
     *
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Gets the number of detected failures.
     *
     * @return int
     */
    public function failureCount()
    {
        return \count($this->failures);
    }

    /**
     * Returns an Enumeration for the failures.
     *
     * @return array
     */
    public function failures()
    {
        return $this->failures;
    }

    /**
     * Gets the number of detected warnings.
     *
     * @return int
     */
    public function warningCount()
    {
        return \count($this->warnings);
    }

    /**
     * Returns an Enumeration for the warnings.
     *
     * @return array
     */
    public function warnings()
    {
        return $this->warnings;
    }

    /**
     * Returns the names of the tests that have passed.
     *
     * @return array
     */
    public function passed()
    {
        return $this->passed;
    }

    /**
     * Returns the (top) test suite.
     *
     * @return TestSuite
     */
    public function topTestSuite()
    {
        return $this->topTestSuite;
    }

    /**
     * Returns whether code coverage information should be collected.
     *
     * @return bool If code coverage should be collected
     */
    public function getCollectCodeCoverageInformation()
    {
        return $this->codeCoverage !== null;
    }

    /**
     * Runs a TestCase.
     *
     * @param Test $test
     */
    public function run(Test $test)
    {
        Assert::resetCount();

        $coversNothing = false;

        if ($test instanceof TestCase) {
            $test->setRegisterMockObjectsFromTestArgumentsRecursively(
                $this->registerMockObjectsFromTestArgumentsRecursively
            );

            $annotations = $test->getAnnotations();

            if (isset($annotations['class']['coversNothing']) || isset($annotations['method']['coversNothing'])) {
                $coversNothing = true;
            }
        }

        $error      = false;
        $failure    = false;
        $warning    = false;
        $incomplete = false;
        $risky      = false;
        $skipped    = false;

        $this->startTest($test);

        $errorHandlerSet = false;

        if ($this->convertErrorsToExceptions) {
            $oldErrorHandler = \set_error_handler(
                [\PHPUnit\Util\ErrorHandler::class, 'handleError'],
                E_ALL | E_STRICT
            );

            if ($oldErrorHandler === null) {
                $errorHandlerSet = true;
            } else {
                \restore_error_handler();
            }
        }

        $collectCodeCoverage = $this->codeCoverage !== null &&
                               !$test instanceof WarningTestCase &&
                               !$coversNothing;

        if ($collectCodeCoverage) {
            $this->codeCoverage->start($test);
        }

        $monitorFunctions = $this->beStrictAboutResourceUsageDuringSmallTests &&
            !$test instanceof WarningTestCase &&
            $test->getSize() == \PHPUnit\Util\Test::SMALL &&
            \function_exists('xdebug_start_function_monitor');

        if ($monitorFunctions) {
            \xdebug_start_function_monitor(ResourceOperations::getFunctions());
        }

        PHP_Timer::start();

        try {
            if (!$test instanceof WarningTestCase &&
                $test->getSize() != \PHPUnit\Util\Test::UNKNOWN &&
                $this->enforceTimeLimit &&
                \extension_loaded('pcntl') && \class_exists('PHP_Invoker')) {
                switch ($test->getSize()) {
                    case \PHPUnit\Util\Test::SMALL:
                        $_timeout = $this->timeoutForSmallTests;

                        break;

                    case \PHPUnit\Util\Test::MEDIUM:
                        $_timeout = $this->timeoutForMediumTests;

                        break;

                    case \PHPUnit\Util\Test::LARGE:
                        $_timeout = $this->timeoutForLargeTests;

                        break;
                }

                $invoker = new PHP_Invoker;
                $invoker->invoke([$test, 'runBare'], [], $_timeout);
            } else {
                $test->runBare();
            }
        } catch (PHP_Invoker_TimeoutException $e) {
            $this->addFailure(
                $test,
                new RiskyTestError(
                    $e->getMessage()
                ),
                $_timeout
            );

            $risky = true;
        } catch (MockObjectException $e) {
            $e = new Warning(
                $e->getMessage()
            );

            $warning = true;
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
                \sprintf(
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

        $time = PHP_Timer::stop();
        $test->addToAssertionCount(Assert::getCount());

        if ($monitorFunctions) {
            $blacklist = new Blacklist;
            $functions = \xdebug_get_monitored_functions();
            \xdebug_stop_function_monitor();

            foreach ($functions as $function) {
                if (!$blacklist->isBlacklisted($function['filename'])) {
                    $this->addFailure(
                        $test,
                        new RiskyTestError(
                            \sprintf(
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

        if ($this->beStrictAboutTestsThatDoNotTestAnything &&
            $test->getNumAssertions() == 0) {
            $risky = true;
        }

        if ($collectCodeCoverage) {
            $append           = !$risky && !$incomplete && !$skipped;
            $linesToBeCovered = [];
            $linesToBeUsed    = [];

            if ($append && $test instanceof TestCase) {
                try {
                    $linesToBeCovered = \PHPUnit\Util\Test::getLinesToBeCovered(
                        \get_class($test),
                        $test->getName(false)
                    );

                    $linesToBeUsed = \PHPUnit\Util\Test::getLinesToBeUsed(
                        \get_class($test),
                        $test->getName(false)
                    );
                } catch (InvalidCoversTargetException $cce) {
                    $this->addWarning(
                        $test,
                        new Warning(
                            $cce->getMessage()
                        ),
                        $time
                    );
                }
            }

            try {
                $this->codeCoverage->stop(
                    $append,
                    $linesToBeCovered,
                    $linesToBeUsed
                );
            } catch (UnintentionallyCoveredCodeException $cce) {
                $this->addFailure(
                    $test,
                    new UnintentionallyCoveredCodeError(
                        'This test executed code that is not listed as code to be covered or used:' .
                        PHP_EOL . $cce->getMessage()
                    ),
                    $time
                );
            } catch (OriginalCoveredCodeNotExecutedException $cce) {
                $this->addFailure(
                    $test,
                    new CoveredCodeNotExecutedException(
                        'This test did not execute all the code that is listed as code to be covered:' .
                        PHP_EOL . $cce->getMessage()
                    ),
                    $time
                );
            } catch (OriginalMissingCoversAnnotationException $cce) {
                if ($linesToBeCovered !== false) {
                    $this->addFailure(
                        $test,
                        new MissingCoversAnnotationException(
                            'This test does not have a @covers annotation but is expected to have one'
                        ),
                        $time
                    );
                }
            } catch (OriginalCodeCoverageException $cce) {
                $error = true;

                if (!isset($e)) {
                    $e = $cce;
                }
            }
        }

        if ($errorHandlerSet === true) {
            \restore_error_handler();
        }

        if ($error === true) {
            $this->addError($test, $e, $time);
        } elseif ($failure === true) {
            $this->addFailure($test, $e, $time);
        } elseif ($warning === true) {
            $this->addWarning($test, $e, $time);
        } elseif ($this->beStrictAboutTestsThatDoNotTestAnything &&
            !$test->doesNotPerformAssertions() &&
            $test->getNumAssertions() == 0) {
            $this->addFailure(
                $test,
                new RiskyTestError(
                    'This test did not perform any assertions'
                ),
                $time
            );
        } elseif ($this->beStrictAboutOutputDuringTests && $test->hasOutput()) {
            $this->addFailure(
                $test,
                new OutputError(
                    \sprintf(
                        'This test printed output: %s',
                        $test->getActualOutput()
                    )
                ),
                $time
            );
        } elseif ($this->beStrictAboutTodoAnnotatedTests && $test instanceof TestCase) {
            $annotations = $test->getAnnotations();

            if (isset($annotations['method']['todo'])) {
                $this->addFailure(
                    $test,
                    new RiskyTestError(
                        'Test method is annotated with @todo'
                    ),
                    $time
                );
            }
        }

        $this->endTest($test, $time);
    }

    /**
     * Gets the number of run tests.
     *
     * @return int
     */
    public function count()
    {
        return $this->runTests;
    }

    /**
     * Checks whether the test run should stop.
     *
     * @return bool
     */
    public function shouldStop()
    {
        return $this->stop;
    }

    /**
     * Marks that the test run should stop.
     */
    public function stop()
    {
        $this->stop = true;
    }

    /**
     * Returns the code coverage object.
     *
     * @return CodeCoverage
     */
    public function getCodeCoverage()
    {
        return $this->codeCoverage;
    }

    /**
     * Sets the code coverage object.
     *
     * @param CodeCoverage $codeCoverage
     */
    public function setCodeCoverage(CodeCoverage $codeCoverage)
    {
        $this->codeCoverage = $codeCoverage;
    }

    /**
     * Enables or disables the error-to-exception conversion.
     *
     * @param bool $flag
     *
     * @throws Exception
     */
    public function convertErrorsToExceptions($flag)
    {
        if (!\is_bool($flag)) {
            throw InvalidArgumentHelper::factory(1, 'boolean');
        }

        $this->convertErrorsToExceptions = $flag;
    }

    /**
     * Returns the error-to-exception conversion setting.
     *
     * @return bool
     */
    public function getConvertErrorsToExceptions()
    {
        return $this->convertErrorsToExceptions;
    }

    /**
     * Enables or disables the stopping when an error occurs.
     *
     * @param bool $flag
     *
     * @throws Exception
     */
    public function stopOnError($flag)
    {
        if (!\is_bool($flag)) {
            throw InvalidArgumentHelper::factory(1, 'boolean');
        }

        $this->stopOnError = $flag;
    }

    /**
     * Enables or disables the stopping when a failure occurs.
     *
     * @param bool $flag
     *
     * @throws Exception
     */
    public function stopOnFailure($flag)
    {
        if (!\is_bool($flag)) {
            throw InvalidArgumentHelper::factory(1, 'boolean');
        }

        $this->stopOnFailure = $flag;
    }

    /**
     * Enables or disables the stopping when a warning occurs.
     *
     * @param bool $flag
     *
     * @throws Exception
     */
    public function stopOnWarning($flag)
    {
        if (!\is_bool($flag)) {
            throw InvalidArgumentHelper::factory(1, 'boolean');
        }

        $this->stopOnWarning = $flag;
    }

    /**
     * @param bool $flag
     *
     * @throws Exception
     */
    public function beStrictAboutTestsThatDoNotTestAnything($flag)
    {
        if (!\is_bool($flag)) {
            throw InvalidArgumentHelper::factory(1, 'boolean');
        }

        $this->beStrictAboutTestsThatDoNotTestAnything = $flag;
    }

    /**
     * @return bool
     */
    public function isStrictAboutTestsThatDoNotTestAnything()
    {
        return $this->beStrictAboutTestsThatDoNotTestAnything;
    }

    /**
     * @param bool $flag
     *
     * @throws Exception
     */
    public function beStrictAboutOutputDuringTests($flag)
    {
        if (!\is_bool($flag)) {
            throw InvalidArgumentHelper::factory(1, 'boolean');
        }

        $this->beStrictAboutOutputDuringTests = $flag;
    }

    /**
     * @return bool
     */
    public function isStrictAboutOutputDuringTests()
    {
        return $this->beStrictAboutOutputDuringTests;
    }

    /**
     * @param bool $flag
     *
     * @throws Exception
     */
    public function beStrictAboutResourceUsageDuringSmallTests($flag)
    {
        if (!\is_bool($flag)) {
            throw InvalidArgumentHelper::factory(1, 'boolean');
        }

        $this->beStrictAboutResourceUsageDuringSmallTests = $flag;
    }

    /**
     * @return bool
     */
    public function isStrictAboutResourceUsageDuringSmallTests()
    {
        return $this->beStrictAboutResourceUsageDuringSmallTests;
    }

    /**
     * @param bool $flag
     *
     * @throws Exception
     */
    public function enforceTimeLimit($flag)
    {
        if (!\is_bool($flag)) {
            throw InvalidArgumentHelper::factory(1, 'boolean');
        }

        $this->enforceTimeLimit = $flag;
    }

    /**
     * @return bool
     */
    public function enforcesTimeLimit()
    {
        return $this->enforceTimeLimit;
    }

    /**
     * @param bool $flag
     *
     * @throws Exception
     */
    public function beStrictAboutTodoAnnotatedTests($flag)
    {
        if (!\is_bool($flag)) {
            throw InvalidArgumentHelper::factory(1, 'boolean');
        }

        $this->beStrictAboutTodoAnnotatedTests = $flag;
    }

    /**
     * @return bool
     */
    public function isStrictAboutTodoAnnotatedTests()
    {
        return $this->beStrictAboutTodoAnnotatedTests;
    }

    /**
     * Enables or disables the stopping for risky tests.
     *
     * @param bool $flag
     *
     * @throws Exception
     */
    public function stopOnRisky($flag)
    {
        if (!\is_bool($flag)) {
            throw InvalidArgumentHelper::factory(1, 'boolean');
        }

        $this->stopOnRisky = $flag;
    }

    /**
     * Enables or disables the stopping for incomplete tests.
     *
     * @param bool $flag
     *
     * @throws Exception
     */
    public function stopOnIncomplete($flag)
    {
        if (!\is_bool($flag)) {
            throw InvalidArgumentHelper::factory(1, 'boolean');
        }

        $this->stopOnIncomplete = $flag;
    }

    /**
     * Enables or disables the stopping for skipped tests.
     *
     * @param bool $flag
     *
     * @throws Exception
     */
    public function stopOnSkipped($flag)
    {
        if (!\is_bool($flag)) {
            throw InvalidArgumentHelper::factory(1, 'boolean');
        }

        $this->stopOnSkipped = $flag;
    }

    /**
     * Returns the time spent running the tests.
     *
     * @return float
     */
    public function time()
    {
        return $this->time;
    }

    /**
     * Returns whether the entire test was successful or not.
     *
     * @return bool
     */
    public function wasSuccessful()
    {
        return empty($this->errors) && empty($this->failures) && empty($this->warnings);
    }

    /**
     * Sets the timeout for small tests.
     *
     * @param int $timeout
     *
     * @throws Exception
     */
    public function setTimeoutForSmallTests($timeout)
    {
        if (!\is_int($timeout)) {
            throw InvalidArgumentHelper::factory(1, 'integer');
        }

        $this->timeoutForSmallTests = $timeout;
    }

    /**
     * Sets the timeout for medium tests.
     *
     * @param int $timeout
     *
     * @throws Exception
     */
    public function setTimeoutForMediumTests($timeout)
    {
        if (!\is_int($timeout)) {
            throw InvalidArgumentHelper::factory(1, 'integer');
        }

        $this->timeoutForMediumTests = $timeout;
    }

    /**
     * Sets the timeout for large tests.
     *
     * @param int $timeout
     *
     * @throws Exception
     */
    public function setTimeoutForLargeTests($timeout)
    {
        if (!\is_int($timeout)) {
            throw InvalidArgumentHelper::factory(1, 'integer');
        }

        $this->timeoutForLargeTests = $timeout;
    }

    /**
     * Returns the set timeout for large tests.
     *
     * @return int
     */
    public function getTimeoutForLargeTests()
    {
        return $this->timeoutForLargeTests;
    }

    /**
     * @param bool $flag
     */
    public function setRegisterMockObjectsFromTestArgumentsRecursively($flag)
    {
        if (!\is_bool($flag)) {
            throw InvalidArgumentHelper::factory(1, 'boolean');
        }

        $this->registerMockObjectsFromTestArgumentsRecursively = $flag;
    }

    /**
     * Returns the class hierarchy for a given class.
     *
     * @param string $className
     * @param bool   $asReflectionObjects
     *
     * @return array
     */
    protected function getHierarchy($className, $asReflectionObjects = false)
    {
        if ($asReflectionObjects) {
            $classes = [new ReflectionClass($className)];
        } else {
            $classes = [$className];
        }

        $done = false;

        while (!$done) {
            if ($asReflectionObjects) {
                $class = new ReflectionClass(
                    $classes[\count($classes) - 1]->getName()
                );
            } else {
                $class = new ReflectionClass($classes[\count($classes) - 1]);
            }

            $parent = $class->getParentClass();

            if ($parent !== false) {
                if ($asReflectionObjects) {
                    $classes[] = $parent;
                } else {
                    $classes[] = $parent->getName();
                }
            } else {
                $done = true;
            }
        }

        return $classes;
    }
}
