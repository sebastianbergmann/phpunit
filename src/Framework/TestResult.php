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
use function count;
use function function_exists;
use function get_class;
use function sprintf;
use AssertionError;
use Countable;
use Error;
use PHPUnit\Util\Error\Handler;
use PHPUnit\Util\ExcludeList;
use PHPUnit\Util\Printer;
use PHPUnit\Util\Test as TestUtil;
use ReflectionClass;
use ReflectionException;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Exception as OriginalCodeCoverageException;
use SebastianBergmann\CodeCoverage\UnintentionallyCoveredCodeException;
use SebastianBergmann\Invoker\Invoker;
use SebastianBergmann\Invoker\TimeoutException;
use SebastianBergmann\ResourceOperations\ResourceOperations;
use SebastianBergmann\Timer\Timer;
use Throwable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestResult implements Countable
{
    private array $passed = [];

    /**
     * @var array<string>
     */
    private array $passedTestClasses = [];

    private bool $currentTestSuiteFailed = false;

    /**
     * @var TestFailure[]
     */
    private array $errors = [];

    /**
     * @var TestFailure[]
     */
    private array $failures = [];

    /**
     * @var TestFailure[]
     */
    private array $warnings = [];

    /**
     * @var TestFailure[]
     */
    private array $notImplemented = [];

    /**
     * @var TestFailure[]
     */
    private array $risky = [];

    /**
     * @var TestFailure[]
     */
    private array $skipped = [];

    /**
     * @deprecated Use the `TestHook` interfaces instead
     *
     * @var TestListener[]
     */
    private array $listeners = [];

    private int $runTests = 0;

    private float $time = 0;

    private ?CodeCoverage $codeCoverage = null;

    private bool $convertDeprecationsToExceptions = true;

    private bool $convertErrorsToExceptions = true;

    private bool $convertNoticesToExceptions = true;

    private bool $convertWarningsToExceptions = true;

    private bool $stop = false;

    private bool $stopOnError = false;

    private bool $stopOnFailure = false;

    private bool $stopOnWarning = false;

    private bool $beStrictAboutTestsThatDoNotTestAnything = true;

    private bool $beStrictAboutOutputDuringTests = false;

    private bool $beStrictAboutTodoAnnotatedTests = false;

    private bool $beStrictAboutResourceUsageDuringSmallTests = false;

    private bool $enforceTimeLimit = false;

    private bool $forceCoversAnnotation = false;

    private int $timeoutForSmallTests = 1;

    private int $timeoutForMediumTests = 10;

    private int $timeoutForLargeTests = 60;

    private bool $stopOnRisky = false;

    private bool $stopOnIncomplete = false;

    private bool $stopOnSkipped = false;

    private bool $lastTestFailed = false;

    private int $defaultTimeLimit = 0;

    private bool $stopOnDefect = false;

    private bool $registerMockObjectsFromTestArgumentsRecursively = false;

    /**
     * @deprecated Use the `TestHook` interfaces instead
     *
     * @codeCoverageIgnore
     */
    public function addListener(TestListener $listener): void
    {
        $this->listeners[] = $listener;
    }

    /**
     * @deprecated Use the `TestHook` interfaces instead
     *
     * @codeCoverageIgnore
     */
    public function flushListeners(): void
    {
        foreach ($this->listeners as $listener) {
            if ($listener instanceof Printer) {
                $listener->flush();
            }
        }
    }

    public function addError(Test $test, Throwable $t, float $time): void
    {
        if ($t instanceof RiskyTestError) {
            $this->recordRisky($test, $t);

            $notifyMethod = 'addRiskyTest';

            if ($test instanceof TestCase) {
                $test->markAsRisky();
            }

            if ($this->stopOnRisky || $this->stopOnDefect) {
                $this->stop();
            }
        } elseif ($t instanceof IncompleteTest) {
            $this->recordNotImplemented($test, $t);

            $notifyMethod = 'addIncompleteTest';

            if ($this->stopOnIncomplete) {
                $this->stop();
            }
        } elseif ($t instanceof SkippedTest) {
            $this->recordSkipped($test, $t);

            $notifyMethod = 'addSkippedTest';

            if ($this->stopOnSkipped) {
                $this->stop();
            }
        } else {
            $this->recordError($test, $t);

            $notifyMethod = 'addError';

            if ($this->stopOnError || $this->stopOnFailure) {
                $this->stop();
            }
        }

        // @see https://github.com/sebastianbergmann/phpunit/issues/1953
        if ($t instanceof Error) {
            $t = new ExceptionWrapper($t);
        }

        foreach ($this->listeners as $listener) {
            $listener->{$notifyMethod}($test, $t, $time);
        }

        $this->lastTestFailed = true;
        $this->time += $time;
    }

    public function addWarning(Test $test, Warning $e, float $time): void
    {
        if ($this->stopOnWarning || $this->stopOnDefect) {
            $this->stop();
        }

        $this->recordWarning($test, $e);

        foreach ($this->listeners as $listener) {
            $listener->addWarning($test, $e, $time);
        }

        $this->time += $time;
    }

    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        if ($e instanceof RiskyTestError || $e instanceof OutputError) {
            $this->recordRisky($test, $e);

            $notifyMethod = 'addRiskyTest';

            if ($test instanceof TestCase) {
                $test->markAsRisky();
            }

            if ($this->stopOnRisky || $this->stopOnDefect) {
                $this->stop();
            }
        } elseif ($e instanceof IncompleteTest) {
            $this->recordNotImplemented($test, $e);

            $notifyMethod = 'addIncompleteTest';

            if ($this->stopOnIncomplete) {
                $this->stop();
            }
        } elseif ($e instanceof SkippedTest) {
            $this->recordSkipped($test, $e);

            $notifyMethod = 'addSkippedTest';

            if ($this->stopOnSkipped) {
                $this->stop();
            }
        } else {
            $this->failures[] = new TestFailure($test, $e);
            $notifyMethod     = 'addFailure';

            if ($this->stopOnFailure || $this->stopOnDefect) {
                $this->stop();
            }
        }

        foreach ($this->listeners as $listener) {
            $listener->{$notifyMethod}($test, $e, $time);
        }

        $this->lastTestFailed = true;
        $this->time += $time;
    }

    public function startTestSuite(TestSuite $suite): void
    {
        $this->currentTestSuiteFailed = false;

        foreach ($this->listeners as $listener) {
            $listener->startTestSuite($suite);
        }
    }

    public function endTestSuite(TestSuite $suite): void
    {
        if (!$this->currentTestSuiteFailed) {
            $this->passedTestClasses[] = $suite->getName();
        }

        foreach ($this->listeners as $listener) {
            $listener->endTestSuite($suite);
        }
    }

    public function startTest(Test $test): void
    {
        $this->lastTestFailed = false;
        $this->runTests += count($test);

        foreach ($this->listeners as $listener) {
            $listener->startTest($test);
        }
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function endTest(Test $test, float $time): void
    {
        foreach ($this->listeners as $listener) {
            $listener->endTest($test, $time);
        }

        if (!$this->lastTestFailed && $test instanceof TestCase) {
            $class = get_class($test);
            $key   = $class . '::' . $test->getName();

            $this->passed[$key] = [
                'result' => $test->getResult(),
                'size'   => \PHPUnit\Util\Test::getSize(
                    $class,
                    $test->getName(false)
                ),
            ];

            $this->time += $time;
        }

        if ($this->lastTestFailed && $test instanceof TestCase) {
            $this->currentTestSuiteFailed = true;
        }
    }

    public function allHarmless(): bool
    {
        return $this->riskyCount() === 0;
    }

    public function riskyCount(): int
    {
        return count($this->risky);
    }

    public function allCompletelyImplemented(): bool
    {
        return $this->notImplementedCount() === 0;
    }

    public function notImplementedCount(): int
    {
        return count($this->notImplemented);
    }

    /**
     * @return TestFailure[]
     */
    public function risky(): array
    {
        return $this->risky;
    }

    /**
     * @return TestFailure[]
     */
    public function notImplemented(): array
    {
        return $this->notImplemented;
    }

    public function noneSkipped(): bool
    {
        return $this->skippedCount() === 0;
    }

    public function skippedCount(): int
    {
        return count($this->skipped);
    }

    /**
     * @return TestFailure[]
     */
    public function skipped(): array
    {
        return $this->skipped;
    }

    public function errorCount(): int
    {
        return count($this->errors);
    }

    /**
     * @return TestFailure[]
     */
    public function errors(): array
    {
        return $this->errors;
    }

    public function failureCount(): int
    {
        return count($this->failures);
    }

    /**
     * @return TestFailure[]
     */
    public function failures(): array
    {
        return $this->failures;
    }

    public function warningCount(): int
    {
        return count($this->warnings);
    }

    /**
     * @return TestFailure[]
     */
    public function warnings(): array
    {
        return $this->warnings;
    }

    public function passed(): array
    {
        return $this->passed;
    }

    public function passedClasses(): array
    {
        return $this->passedTestClasses;
    }

    public function getCollectCodeCoverageInformation(): bool
    {
        return $this->codeCoverage !== null;
    }

    /**
     * @throws CodeCoverageException
     * @throws UnintentionallyCoveredCodeException
     * @throws \SebastianBergmann\CodeCoverage\InvalidArgumentException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function run(Test $test): void
    {
        Assert::resetCount();

        if ($test instanceof TestCase) {
            $test->setRegisterMockObjectsFromTestArgumentsRecursively(
                $this->registerMockObjectsFromTestArgumentsRecursively
            );

            $isAnyCoverageRequired = TestUtil::requiresCodeCoverageDataCollection($test);
        }

        $error      = false;
        $failure    = false;
        $warning    = false;
        $incomplete = false;
        $risky      = false;
        $skipped    = false;

        $this->startTest($test);

        if ($this->convertDeprecationsToExceptions || $this->convertErrorsToExceptions || $this->convertNoticesToExceptions || $this->convertWarningsToExceptions) {
            $errorHandler = new Handler(
                $this->convertDeprecationsToExceptions,
                $this->convertErrorsToExceptions,
                $this->convertNoticesToExceptions,
                $this->convertWarningsToExceptions
            );

            $errorHandler->register();
        }

        $collectCodeCoverage = $this->codeCoverage !== null &&
                               !$test instanceof ErrorTestCase &&
                               !$test instanceof WarningTestCase &&
                               $isAnyCoverageRequired;

        if ($collectCodeCoverage) {
            $this->codeCoverage->start($test);
        }

        $monitorFunctions = $this->beStrictAboutResourceUsageDuringSmallTests &&
                            !$test instanceof ErrorTestCase &&
                            !$test instanceof WarningTestCase &&
                            $test->getSize() === \PHPUnit\Util\Test::SMALL &&
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
                $this->enforceTimeLimit &&
                ($this->defaultTimeLimit || $test->getSize() != \PHPUnit\Util\Test::UNKNOWN) &&
                $invoker->canInvokeWithTimeout()) {
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

                    case \PHPUnit\Util\Test::UNKNOWN:
                        $_timeout = $this->defaultTimeLimit;

                        break;
                }

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
                    $this->addFailure(
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

        if ($this->beStrictAboutTestsThatDoNotTestAnything &&
            $test->getNumAssertions() === 0) {
            $risky = true;
        }

        if ($this->forceCoversAnnotation && !$error && !$failure && !$warning && !$incomplete && !$skipped && !$risky) {
            $annotations = TestUtil::parseTestMethodAnnotations(
                get_class($test),
                $test->getName(false)
            );

            if (!isset($annotations['class']['covers']) &&
                !isset($annotations['method']['covers']) &&
                !isset($annotations['class']['coversNothing']) &&
                !isset($annotations['method']['coversNothing'])) {
                $this->addFailure(
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

            if ($append && $test instanceof TestCase) {
                try {
                    $linesToBeCovered = \PHPUnit\Util\Test::getLinesToBeCovered(
                        get_class($test),
                        $test->getName(false)
                    );

                    $linesToBeUsed = \PHPUnit\Util\Test::getLinesToBeUsed(
                        get_class($test),
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
            $this->addError($test, $e, $time);
        } elseif ($failure) {
            $this->addFailure($test, $e, $time);
        } elseif ($warning) {
            $this->addWarning($test, $e, $time);
        } elseif (isset($unintentionallyCoveredCodeError)) {
            $this->addFailure(
                $test,
                $unintentionallyCoveredCodeError,
                $time
            );
        } elseif ($this->beStrictAboutTestsThatDoNotTestAnything &&
            !$test->doesNotPerformAssertions() &&
            $test->getNumAssertions() === 0) {
            try {
                $reflected = new ReflectionClass($test);
                // @codeCoverageIgnoreStart
            } catch (ReflectionException $e) {
                throw new Exception(
                    $e->getMessage(),
                    (int) $e->getCode(),
                    $e
                );
            }
            // @codeCoverageIgnoreEnd

            $name = $test->getName(false);

            if ($name && $reflected->hasMethod($name)) {
                try {
                    $reflected = $reflected->getMethod($name);
                    // @codeCoverageIgnoreStart
                } catch (ReflectionException $e) {
                    throw new Exception(
                        $e->getMessage(),
                        (int) $e->getCode(),
                        $e
                    );
                }
                // @codeCoverageIgnoreEnd
            }

            $this->addFailure(
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
        } elseif ($this->beStrictAboutTestsThatDoNotTestAnything &&
            $test->doesNotPerformAssertions() &&
            $test->getNumAssertions() > 0) {
            $this->addFailure(
                $test,
                new RiskyTestError(
                    sprintf(
                        'This test is annotated with "@doesNotPerformAssertions" but performed %d assertions',
                        $test->getNumAssertions()
                    )
                ),
                $time
            );
        } elseif ($this->beStrictAboutOutputDuringTests && $test->hasOutput()) {
            $this->addFailure(
                $test,
                new OutputError(
                    sprintf(
                        'This test printed output: %s',
                        $test->getActualOutput()
                    )
                ),
                $time
            );
        } elseif ($this->beStrictAboutTodoAnnotatedTests && $test instanceof TestCase) {
            $annotations = TestUtil::parseTestMethodAnnotations(
                get_class($test),
                $test->getName(false)
            );

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

    public function count(): int
    {
        return $this->runTests;
    }

    public function shouldStop(): bool
    {
        return $this->stop;
    }

    public function stop(): void
    {
        $this->stop = true;
    }

    public function getCodeCoverage(): ?CodeCoverage
    {
        return $this->codeCoverage;
    }

    public function setCodeCoverage(CodeCoverage $codeCoverage): void
    {
        $this->codeCoverage = $codeCoverage;
    }

    public function convertDeprecationsToExceptions(bool $flag): void
    {
        $this->convertDeprecationsToExceptions = $flag;
    }

    public function convertErrorsToExceptions(bool $flag): void
    {
        $this->convertErrorsToExceptions = $flag;
    }

    public function convertNoticesToExceptions(bool $flag): void
    {
        $this->convertNoticesToExceptions = $flag;
    }

    public function convertWarningsToExceptions(bool $flag): void
    {
        $this->convertWarningsToExceptions = $flag;
    }

    public function stopOnError(bool $flag): void
    {
        $this->stopOnError = $flag;
    }

    public function stopOnFailure(bool $flag): void
    {
        $this->stopOnFailure = $flag;
    }

    public function stopOnWarning(bool $flag): void
    {
        $this->stopOnWarning = $flag;
    }

    public function beStrictAboutTestsThatDoNotTestAnything(bool $flag): void
    {
        $this->beStrictAboutTestsThatDoNotTestAnything = $flag;
    }

    public function isStrictAboutTestsThatDoNotTestAnything(): bool
    {
        return $this->beStrictAboutTestsThatDoNotTestAnything;
    }

    public function beStrictAboutOutputDuringTests(bool $flag): void
    {
        $this->beStrictAboutOutputDuringTests = $flag;
    }

    public function isStrictAboutOutputDuringTests(): bool
    {
        return $this->beStrictAboutOutputDuringTests;
    }

    public function beStrictAboutResourceUsageDuringSmallTests(bool $flag): void
    {
        $this->beStrictAboutResourceUsageDuringSmallTests = $flag;
    }

    public function isStrictAboutResourceUsageDuringSmallTests(): bool
    {
        return $this->beStrictAboutResourceUsageDuringSmallTests;
    }

    public function enforceTimeLimit(bool $flag): void
    {
        $this->enforceTimeLimit = $flag;
    }

    public function enforcesTimeLimit(): bool
    {
        return $this->enforceTimeLimit;
    }

    public function beStrictAboutTodoAnnotatedTests(bool $flag): void
    {
        $this->beStrictAboutTodoAnnotatedTests = $flag;
    }

    public function isStrictAboutTodoAnnotatedTests(): bool
    {
        return $this->beStrictAboutTodoAnnotatedTests;
    }

    public function forceCoversAnnotation(): void
    {
        $this->forceCoversAnnotation = true;
    }

    public function stopOnRisky(bool $flag): void
    {
        $this->stopOnRisky = $flag;
    }

    public function stopOnIncomplete(bool $flag): void
    {
        $this->stopOnIncomplete = $flag;
    }

    public function stopOnSkipped(bool $flag): void
    {
        $this->stopOnSkipped = $flag;
    }

    public function stopOnDefect(bool $flag): void
    {
        $this->stopOnDefect = $flag;
    }

    public function time(): float
    {
        return $this->time;
    }

    public function wasSuccessful(): bool
    {
        return $this->wasSuccessfulIgnoringWarnings() && empty($this->warnings);
    }

    public function wasSuccessfulIgnoringWarnings(): bool
    {
        return empty($this->errors) && empty($this->failures);
    }

    public function wasSuccessfulAndNoTestIsRiskyOrSkippedOrIncomplete(): bool
    {
        return $this->wasSuccessful() && $this->allHarmless() && $this->allCompletelyImplemented() && $this->noneSkipped();
    }

    public function setDefaultTimeLimit(int $timeout): void
    {
        $this->defaultTimeLimit = $timeout;
    }

    public function setTimeoutForSmallTests(int $timeout): void
    {
        $this->timeoutForSmallTests = $timeout;
    }

    public function setTimeoutForMediumTests(int $timeout): void
    {
        $this->timeoutForMediumTests = $timeout;
    }

    public function setTimeoutForLargeTests(int $timeout): void
    {
        $this->timeoutForLargeTests = $timeout;
    }

    public function getTimeoutForLargeTests(): int
    {
        return $this->timeoutForLargeTests;
    }

    public function setRegisterMockObjectsFromTestArgumentsRecursively(bool $flag): void
    {
        $this->registerMockObjectsFromTestArgumentsRecursively = $flag;
    }

    private function recordError(Test $test, Throwable $t): void
    {
        $this->errors[] = new TestFailure($test, $t);
    }

    private function recordNotImplemented(Test $test, Throwable $t): void
    {
        $this->notImplemented[] = new TestFailure($test, $t);
    }

    private function recordRisky(Test $test, Throwable $t): void
    {
        $this->risky[] = new TestFailure($test, $t);
    }

    private function recordSkipped(Test $test, Throwable $t): void
    {
        $this->skipped[] = new TestFailure($test, $t);
    }

    private function recordWarning(Test $test, Throwable $t): void
    {
        $this->warnings[] = new TestFailure($test, $t);
    }
}
