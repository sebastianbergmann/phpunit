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
use Throwable;

/**
 * @deprecated
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestResult implements Countable
{
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

    public function addError(Test $test, Throwable $t): void
    {
        $this->recordError($test, $t);
    }

    public function addWarning(Test $test, Warning $e): void
    {
        $this->recordWarning($test, $e);
    }

    public function addFailure(Test $test, AssertionFailedError $e): void
    {
        if ($e instanceof RiskyTest) {
            $this->recordRisky($test, $e);

            if ($test instanceof TestCase) {
                $test->markAsRisky();
            }
        } elseif ($e instanceof IncompleteTest) {
            $this->recordNotImplemented($test, $e);
        } elseif ($e instanceof SkippedTest) {
            $this->recordSkipped($test, $e);
        } else {
            $this->failures[] = new TestFailure($test, $e);
        }
    }

    public function startTest(Test $test): void
    {
        $this->runTests += count($test);
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

    /**
     * @psalm-return list<TestFailure>
     */
    public function skipped(): array
    {
        return $this->skipped;
    }

    /**
     * @psalm-return list<TestFailure>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * @psalm-return list<TestFailure>
     */
    public function failures(): array
    {
        return $this->failures;
    }

    /**
     * @psalm-return list<TestFailure>
     */
    public function warnings(): array
    {
        return $this->warnings;
    }

    public function count(): int
    {
        return $this->runTests;
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
