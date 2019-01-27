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

/**
 * @deprecated Use the `TestHook` interfaces instead
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
interface TestListener
{
    /**
     * An error occurred.
     */
    public function addError(Test $test, \Throwable $t, float $time): void;

    /**
     * A warning occurred.
     */
    public function addWarning(Test $test, Warning $e, float $time): void;

    /**
     * A failure occurred.
     */
    public function addFailure(Test $test, AssertionFailedError $e, float $time): void;

    /**
     * Incomplete test.
     */
    public function addIncompleteTest(Test $test, \Throwable $t, float $time): void;

    /**
     * Risky test.
     */
    public function addRiskyTest(Test $test, \Throwable $t, float $time): void;

    /**
     * Skipped test.
     */
    public function addSkippedTest(Test $test, \Throwable $t, float $time): void;

    /**
     * A test suite started.
     */
    public function startTestSuite(TestSuite $suite): void;

    /**
     * A test suite ended.
     */
    public function endTestSuite(TestSuite $suite): void;

    /**
     * A test started.
     */
    public function startTest(Test $test): void;

    /**
     * A test ended.
     */
    public function endTest(Test $test, float $time): void;
}
