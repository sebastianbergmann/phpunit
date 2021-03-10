<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\SelfDescribing;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use Throwable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @deprecated
 */
final class TestListenerAdapter implements TestListener
{
    /**
     * @var TestHook[]
     */
    private array $hooks = [];

    private ?bool $lastTestWasNotSuccessful = null;

    public function add(TestHook $hook): void
    {
        $this->hooks[] = $hook;
    }

    public function startTest(Test $test): void
    {
        foreach ($this->hooks as $hook) {
            if ($hook instanceof BeforeTestHook) {
                $hook->executeBeforeTest($this->describe($test));
            }
        }

        $this->lastTestWasNotSuccessful = false;
    }

    public function addError(Test $test, Throwable $t, float $time): void
    {
        foreach ($this->hooks as $hook) {
            if ($hook instanceof AfterTestErrorHook) {
                $hook->executeAfterTestError($this->describe($test), $t->getMessage(), $time);
            }
        }

        $this->lastTestWasNotSuccessful = true;
    }

    public function addWarning(Test $test, Warning $e, float $time): void
    {
        foreach ($this->hooks as $hook) {
            if ($hook instanceof AfterTestWarningHook) {
                $hook->executeAfterTestWarning($this->describe($test), $e->getMessage(), $time);
            }
        }

        $this->lastTestWasNotSuccessful = true;
    }

    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        foreach ($this->hooks as $hook) {
            if ($hook instanceof AfterTestFailureHook) {
                $hook->executeAfterTestFailure($this->describe($test), $e->getMessage(), $time);
            }
        }

        $this->lastTestWasNotSuccessful = true;
    }

    public function addIncompleteTest(Test $test, Throwable $t, float $time): void
    {
        foreach ($this->hooks as $hook) {
            if ($hook instanceof AfterIncompleteTestHook) {
                $hook->executeAfterIncompleteTest($this->describe($test), $t->getMessage(), $time);
            }
        }

        $this->lastTestWasNotSuccessful = true;
    }

    public function addRiskyTest(Test $test, Throwable $t, float $time): void
    {
        foreach ($this->hooks as $hook) {
            if ($hook instanceof AfterRiskyTestHook) {
                $hook->executeAfterRiskyTest($this->describe($test), $t->getMessage(), $time);
            }
        }

        $this->lastTestWasNotSuccessful = true;
    }

    public function addSkippedTest(Test $test, Throwable $t, float $time): void
    {
        foreach ($this->hooks as $hook) {
            if ($hook instanceof AfterSkippedTestHook) {
                $hook->executeAfterSkippedTest($this->describe($test), $t->getMessage(), $time);
            }
        }

        $this->lastTestWasNotSuccessful = true;
    }

    public function endTest(Test $test, float $time): void
    {
        if (!$this->lastTestWasNotSuccessful) {
            foreach ($this->hooks as $hook) {
                if ($hook instanceof AfterSuccessfulTestHook) {
                    $hook->executeAfterSuccessfulTest($this->describe($test), $time);
                }
            }
        }

        foreach ($this->hooks as $hook) {
            if ($hook instanceof AfterTestHook) {
                $hook->executeAfterTest($this->describe($test), $time);
            }
        }
    }

    public function startTestSuite(TestSuite $suite): void
    {
    }

    public function endTestSuite(TestSuite $suite): void
    {
    }

    private function describe(Test $test): string
    {
        if ($test instanceof SelfDescribing) {
            return $test->toString();
        }

        return get_class($test);
    }
}
