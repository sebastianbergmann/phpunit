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
use PHPUnit\Framework\MockObject\Exception as MockObjectException;
use PHPUnit\Util\Blacklist;
use PHPUnit\Util\ErrorHandler;
use PHPUnit\Util\Printer;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\CoveredCodeNotExecutedException as OriginalCoveredCodeNotExecutedException;
use SebastianBergmann\CodeCoverage\Exception as OriginalCodeCoverageException;
use SebastianBergmann\CodeCoverage\MissingCoversAnnotationException as OriginalMissingCoversAnnotationException;
use SebastianBergmann\CodeCoverage\UnintentionallyCoveredCodeException;
use SebastianBergmann\Invoker\Invoker;
use SebastianBergmann\Invoker\TimeoutException;
use SebastianBergmann\ResourceOperations\ResourceOperations;
use SebastianBergmann\Timer\Timer;
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
    public function addListener(TestListener $listener): void
    {
        $this->listeners[] = $listener;
    }

    /**
     * Unregisters a TestListener.
     *
     * @param TestListener $listener
     */
    public function removeListener(TestListener $listener): void
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
    public function flushListeners(): void
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
    public function addError(Test $test, Throwable $t, $time): void
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
    public function addWarning(Test $test, Warning $e, $time): void
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
    public function addFailure(Test $test, AssertionFailedError $e, $time): void
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
     * Informs the result that a test suite will be started.
     *
     * @param TestSuite $suite
     */
    public function startTestSuite(TestSuite $suite): void
    {
        if ($this->topTestSuite === null) {
            $this->topTestSuite = $suite;
        }

        foreach ($this->listeners as $listener) {
            $listener->startTestSuite($suite);
        }
    }

    /**
     * Informs the result that a test suite was completed.
     *
     * @param TestSuite $suite
     */
    public function endTestSuite(TestSuite $suite): void
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
    public function startTest(Test $test): void
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
     *
     * @throws \Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function endTest(Test $test, $time): void
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
    public function allHarmless(): bool
    {
        return $this->riskyCount() == 0;
    }

    /**
     * Gets the number of risky tests.
     *
     * @return int
     */
    public function riskyCount(): int
    {
        return \count($this->risky);
    }

    /**
     * Returns true if no incomplete test occurred.
     *
     * @return bool
     */
    public function allCompletelyImplemented(): bool
    {
        return $this->notImplementedCount() == 0;
    }

    /**
     * Gets the number of incomplete tests.
     *
     * @return int
     */
    public function notImplementedCount(): int
    {
        return \count($this->notImplemented);
    }

    /**
     * Returns an Enumeration for the risky tests.
     *
     * @return array
     */
    public function risky(): array
    {
        return $this->risky;
    }

    /**
     * Returns an Enumeration for the incomplete tests.
     *
     * @return array
     */
    public function notImplemented(): array
    {
        return $this->notImplemented;
    }

    /**
     * Returns true if no test has been skipped.
     *
     * @return bool
     */
    public function noneSkipped(): bool
    {
        return $this->skippedCount() == 0;
    }

    /**
     * Gets the number of skipped tests.
     *
     * @return int
     */
    public function skippedCount(): int
    {
        return \count($this->skipped);
    }

    /**
     * Returns an Enumeration for the skipped tests.
     *
     * @return array
     */
    public function skipped(): array
    {
        return $this->skipped;
    }

    /**
     * Gets the number of detected errors.
     *
     * @return int
     */
    public function errorCount(): int
    {
        return \count($this->errors);
    }

    /**
     * Returns an Enumeration for the errors.
     *
     * @return array
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Gets the number of detected failures.
     *
     * @return int
     */
    public function failureCount(): int
    {
        return \count($this->failures);
    }

    /**
     * Returns an Enumeration for the failures.
     *
     * @return array
     */
    public function failures(): array
    {
        return $this->failures;
    }

    /**
     * Gets the number of detected warnings.
     *
     * @return int
     */
    public function warningCount(): int
    {
        return \count($this->warnings);
    }

    /**
     * Returns an Enumeration for the warnings.
     *
     * @return array
     */
    public function warnings(): array
    {
        return $this->warnings;
    }

    /**
     * Returns the names of the tests that have passed.
     *
     * @return array
     */
    public function passed(): array
    {
        return $this->passed;
    }

    /**
     * Returns the (top) test suite.
     *
     * @return TestSuite
     */
    public function topTestSuite(): TestSuite
    {
        return $this->topTestSuite;
    }

    /**
     * Returns whether code coverage information should be collected.
     *
     * @return bool If code coverage should be collected
     */
    public function getCollectCodeCoverageInformation(): bool
    {
        return $this->codeCoverage !== null;
    }

    /**
     * Runs a TestCase.
     *
     * @param Test $test
     *
     * @throws CodeCoverageException
     * @throws OriginalCoveredCodeNotExecutedException
     * @throws OriginalMissingCoversAnnotationException
     * @throws UnintentionallyCoveredCodeException
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \SebastianBergmann\CodeCoverage\InvalidArgumentException
     * @throws \SebastianBergmann\CodeCoverage\RuntimeException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function run(Test $test): void
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
                [ErrorHandler::class, 'handleError'],
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

        Timer::start();

        try {
            if (!$test instanceof WarningTestCase &&
                $test->getSize() != \PHPUnit\Util\Test::UNKNOWN &&
                $this->enforceTimeLimit &&
                \extension_loaded('pcntl') && \class_exists(Invoker::class)) {
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

                $invoker = new Invoker;
                $invoker->invoke([$test, 'runBare'], [], $_timeout);
            } else {
                $test->runBare();
            }
        } catch (TimeoutException $e) {
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

        $time = Timer::stop();
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
    public function count(): int
    {
        return $this->runTests;
    }

    /**
     * Checks whether the test run should stop.
     *
     * @return bool
     */
    public function shouldStop(): bool
    {
        return $this->stop;
    }

    /**
     * Marks that the test run should stop.
     */
    public function stop(): void
    {
        $this->stop = true;
    }

    /**
     * Returns the code coverage object.
     *
     * @return CodeCoverage
     */
    public function getCodeCoverage(): ?CodeCoverage
    {
        return $this->codeCoverage;
    }

    /**
     * Sets the code coverage object.
     *
     * @param CodeCoverage $codeCoverage
     */
    public function setCodeCoverage(CodeCoverage $codeCoverage): void
    {
        $this->codeCoverage = $codeCoverage;
    }

    /**
     * Enables or disables the error-to-exception conversion.
     *
     * @param bool $flag
     */
    public function convertErrorsToExceptions(bool $flag): void
    {
        $this->convertErrorsToExceptions = $flag;
    }

    /**
     * Returns the error-to-exception conversion setting.
     */
    public function getConvertErrorsToExceptions(): bool
    {
        return $this->convertErrorsToExceptions;
    }

    /**
     * Enables or disables the stopping when an error occurs.
     *
     * @param bool $flag
     */
    public function stopOnError(bool $flag): void
    {
        $this->stopOnError = $flag;
    }

    /**
     * Enables or disables the stopping when a failure occurs.
     *
     * @param bool $flag
     */
    public function stopOnFailure(bool $flag): void
    {
        $this->stopOnFailure = $flag;
    }

    /**
     * Enables or disables the stopping when a warning occurs.
     *
     * @param bool $flag
     */
    public function stopOnWarning(bool $flag): void
    {
        $this->stopOnWarning = $flag;
    }

    /**
     * @param bool $flag
     */
    public function beStrictAboutTestsThatDoNotTestAnything(bool $flag): void
    {
        $this->beStrictAboutTestsThatDoNotTestAnything = $flag;
    }

    /**
     * @return bool
     */
    public function isStrictAboutTestsThatDoNotTestAnything(): bool
    {
        return $this->beStrictAboutTestsThatDoNotTestAnything;
    }

    /**
     * @param bool $flag
     */
    public function beStrictAboutOutputDuringTests(bool $flag): void
    {
        $this->beStrictAboutOutputDuringTests = $flag;
    }

    /**
     * @return bool
     */
    public function isStrictAboutOutputDuringTests(): bool
    {
        return $this->beStrictAboutOutputDuringTests;
    }

    /**
     * @param bool $flag
     */
    public function beStrictAboutResourceUsageDuringSmallTests(bool $flag): void
    {
        $this->beStrictAboutResourceUsageDuringSmallTests = $flag;
    }

    /**
     * @return bool
     */
    public function isStrictAboutResourceUsageDuringSmallTests(): bool
    {
        return $this->beStrictAboutResourceUsageDuringSmallTests;
    }

    /**
     * @param bool $flag
     */
    public function enforceTimeLimit(bool $flag): void
    {
        $this->enforceTimeLimit = $flag;
    }

    /**
     * @return bool
     */
    public function enforcesTimeLimit(): bool
    {
        return $this->enforceTimeLimit;
    }

    /**
     * @param bool $flag
     */
    public function beStrictAboutTodoAnnotatedTests(bool $flag): void
    {
        $this->beStrictAboutTodoAnnotatedTests = $flag;
    }

    /**
     * @return bool
     */
    public function isStrictAboutTodoAnnotatedTests(): bool
    {
        return $this->beStrictAboutTodoAnnotatedTests;
    }

    /**
     * Enables or disables the stopping for risky tests.
     *
     * @param bool $flag
     */
    public function stopOnRisky(bool $flag): void
    {
        $this->stopOnRisky = $flag;
    }

    /**
     * Enables or disables the stopping for incomplete tests.
     *
     * @param bool $flag
     */
    public function stopOnIncomplete(bool $flag): void
    {
        $this->stopOnIncomplete = $flag;
    }

    /**
     * Enables or disables the stopping for skipped tests.
     *
     * @param bool $flag
     */
    public function stopOnSkipped(bool $flag): void
    {
        $this->stopOnSkipped = $flag;
    }

    /**
     * Returns the time spent running the tests.
     *
     * @return float
     */
    public function time(): float
    {
        return $this->time;
    }

    /**
     * Returns whether the entire test was successful or not.
     *
     * @return bool
     */
    public function wasSuccessful(): bool
    {
        return empty($this->errors) && empty($this->failures) && empty($this->warnings);
    }

    /**
     * Sets the timeout for small tests.
     *
     * @param int $timeout
     */
    public function setTimeoutForSmallTests(int $timeout): void
    {
        $this->timeoutForSmallTests = $timeout;
    }

    /**
     * Sets the timeout for medium tests.
     *
     * @param int $timeout
     */
    public function setTimeoutForMediumTests(int $timeout): void
    {
        $this->timeoutForMediumTests = $timeout;
    }

    /**
     * Sets the timeout for large tests.
     *
     * @param int $timeout
     */
    public function setTimeoutForLargeTests(int $timeout): void
    {
        $this->timeoutForLargeTests = $timeout;
    }

    /**
     * Returns the set timeout for large tests.
     *
     * @return int
     */
    public function getTimeoutForLargeTests(): int
    {
        return $this->timeoutForLargeTests;
    }

    /**
     * @param bool $flag
     */
    public function setRegisterMockObjectsFromTestArgumentsRecursively(bool $flag): void
    {
        $this->registerMockObjectsFromTestArgumentsRecursively = $flag;
    }
}
