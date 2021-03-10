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

use function count;
use function get_class;
use Countable;
use Error;
use PHPUnit\Framework\TestSize\TestSize;
use PHPUnit\Metadata\GroupsFacade;
use PHPUnit\Util\Printer;
use Throwable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestResult implements Countable
{
    /**
     * @psalm-var array<string,array{result: mixed, size: \PHPUnit\Framework\TestSize\TestSize}>
     */
    private array $passed = [];

    /**
     * @psalm-var list<string>
     */
    private array $passedTestClasses = [];

    private bool $currentTestSuiteFailed = false;

    /**
     * @psalm-var list<TestFailure>
     */
    private array $errors = [];

    /**
     * @psalm-var list<TestFailure>
     */
    private array $failures = [];

    /**
     * @psalm-var list<TestFailure>
     */
    private array $warnings = [];

    /**
     * @psalm-var list<TestFailure>
     */
    private array $notImplemented = [];

    /**
     * @psalm-var list<TestFailure>
     */
    private array $risky = [];

    /**
     * @psalm-var list<TestFailure>
     */
    private array $skipped = [];

    /**
     * @psalm-var list<TestListener>
     */
    private array $listeners = [];

    private int $runTests = 0;

    private float $time = 0;

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
        $this->recordError($test, $t);

        if ($this->stopOnError || $this->stopOnFailure) {
            $this->stop();
        }

        // @see https://github.com/sebastianbergmann/phpunit/issues/1953
        if ($t instanceof Error) {
            $t = new ExceptionWrapper($t);
        }

        foreach ($this->listeners as $listener) {
            $listener->addError($test, $t, $time);
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
            $size  = TestSize::unknown();

            if ($class !== WarningTestCase::class) {
                $size = (new GroupsFacade)->size(
                    $class,
                    $test->getName(false)
                );
            }

            $this->passed[$key] = [
                'result' => $test->result(),
                'size'   => $size,
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
     * @psalm-return list<TestFailure>
     */
    public function risky(): array
    {
        return $this->risky;
    }

    /**
     * @psalm-return list<TestFailure>
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
     * @psalm-return list<TestFailure>
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
     * @psalm-return list<TestFailure>
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
     * @psalm-return list<TestFailure>
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
     * @psalm-return list<TestFailure>
     */
    public function warnings(): array
    {
        return $this->warnings;
    }

    /**
     * @psalm-return array<string,array{result: mixed, size: \PHPUnit\Framework\TestSize\TestSize}>
     */
    public function passed(): array
    {
        return $this->passed;
    }

    public function passedClasses(): array
    {
        return $this->passedTestClasses;
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

    public function convertDeprecationsToExceptions(bool $flag): void
    {
        $this->convertDeprecationsToExceptions = $flag;
    }

    public function shouldDeprecationsBeConvertedToExceptions(): bool
    {
        return $this->convertDeprecationsToExceptions;
    }

    public function convertErrorsToExceptions(bool $flag): void
    {
        $this->convertErrorsToExceptions = $flag;
    }

    public function shouldErrorsBeConvertedToExceptions(): bool
    {
        return $this->convertErrorsToExceptions;
    }

    public function convertNoticesToExceptions(bool $flag): void
    {
        $this->convertNoticesToExceptions = $flag;
    }

    public function shouldNoticeBeConvertedToExceptions(): bool
    {
        return $this->convertNoticesToExceptions;
    }

    public function convertWarningsToExceptions(bool $flag): void
    {
        $this->convertWarningsToExceptions = $flag;
    }

    public function shouldWarningsBeConvertedToExceptions(): bool
    {
        return $this->convertWarningsToExceptions;
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

    public function enforcesCoversAnnotation(): bool
    {
        return $this->forceCoversAnnotation;
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

    public function defaultTimeLimit(): int
    {
        return $this->defaultTimeLimit;
    }

    public function setTimeoutForSmallTests(int $timeout): void
    {
        $this->timeoutForSmallTests = $timeout;
    }

    public function timeoutForSmallTests(): int
    {
        return $this->timeoutForSmallTests;
    }

    public function setTimeoutForMediumTests(int $timeout): void
    {
        $this->timeoutForMediumTests = $timeout;
    }

    public function timeoutForMediumTests(): int
    {
        return $this->timeoutForMediumTests;
    }

    public function setTimeoutForLargeTests(int $timeout): void
    {
        $this->timeoutForLargeTests = $timeout;
    }

    public function timeoutForLargeTests(): int
    {
        return $this->timeoutForLargeTests;
    }

    public function registerMockObjectsFromTestArgumentsRecursively(): void
    {
        $this->registerMockObjectsFromTestArgumentsRecursively = true;
    }

    public function shouldMockObjectsFromTestArgumentsBeRegisteredRecursively(): bool
    {
        return $this->registerMockObjectsFromTestArgumentsRecursively;
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
