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
use Countable;
use PHPUnit\Event;
use PHPUnit\Framework\TestSize\TestSize;
use PHPUnit\Metadata\Api\Groups;
use PHPUnit\TextUI\Configuration\Registry;
use Throwable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestResult implements Countable
{
    /**
     * @psalm-var array<string,array{result: mixed, size: TestSize}>
     */
    private array $passed = [];

    /**
     * @psalm-var list<string>
     */
    private array $passedTestClasses     = [];
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
    private int $runTests  = 0;
    private float $time    = 0;
    private bool $stop     = false;
    private bool $stopOnError;
    private bool $stopOnFailure;
    private bool $stopOnWarning;
    private bool $stopOnRisky;
    private bool $stopOnIncomplete;
    private bool $stopOnSkipped;
    private bool $stopOnDefect;
    private bool $lastTestFailed = false;

    public function __construct()
    {
        $configuration = Registry::get();

        $this->stopOnError      = $configuration->stopOnError();
        $this->stopOnFailure    = $configuration->stopOnFailure();
        $this->stopOnWarning    = $configuration->stopOnWarning();
        $this->stopOnRisky      = $configuration->stopOnRisky();
        $this->stopOnIncomplete = $configuration->stopOnIncomplete();
        $this->stopOnSkipped    = $configuration->stopOnSkipped();
        $this->stopOnDefect     = $configuration->stopOnDefect();
    }

    public function addError(Test $test, Throwable $t, float $time): void
    {
        $this->recordError($test, $t);

        if ($this->stopOnError || $this->stopOnFailure) {
            $this->stop = true;
        }

        $this->lastTestFailed = true;
        $this->time += $time;
    }

    public function addWarning(Test $test, Warning $e, float $time): void
    {
        if ($this->stopOnWarning || $this->stopOnDefect) {
            $this->stop = true;
        }

        $this->recordWarning($test, $e);

        $this->time += $time;
    }

    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        if ($e instanceof RiskyTest) {
            $this->recordRisky($test, $e);

            if ($test instanceof TestCase) {
                $test->markAsRisky();

                Event\Facade::emitter()->testConsideredRisky(
                    $test->valueObjectForEvents(),
                    Event\Code\Throwable::from($e)
                );
            }

            if ($this->stopOnRisky || $this->stopOnDefect) {
                $this->stop = true;
            }
        } elseif ($e instanceof IncompleteTest) {
            $this->recordNotImplemented($test, $e);

            if ($this->stopOnIncomplete) {
                $this->stop = true;
            }
        } elseif ($e instanceof SkippedTest) {
            $this->recordSkipped($test, $e);

            if ($this->stopOnSkipped) {
                $this->stop = true;
            }
        } else {
            $this->failures[] = new TestFailure($test, $e);

            if ($this->stopOnFailure || $this->stopOnDefect) {
                $this->stop = true;
            }
        }

        $this->lastTestFailed = true;
        $this->time += $time;
    }

    public function startTestSuite(TestSuite $suite): void
    {
        $this->currentTestSuiteFailed = false;
    }

    public function endTestSuite(TestSuite $suite): void
    {
        if (!$this->currentTestSuiteFailed) {
            $this->passedTestClasses[] = $suite->getName();
        }
    }

    public function startTest(Test $test): void
    {
        $this->lastTestFailed = false;
        $this->runTests += count($test);
    }

    public function endTest(Test $test, float $time): void
    {
        if (!$this->lastTestFailed && $test instanceof TestCase) {
            $class = $test::class;
            $key   = $class . '::' . $test->getName();
            $size  = TestSize::unknown();

            if ($class !== WarningTestCase::class) {
                $size = (new Groups)->size(
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
     * @psalm-return array<string,array{result: mixed, size: TestSize}>
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
