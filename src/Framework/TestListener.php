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

/**
 * A Listener for test progress.
 */
interface TestListener
{
    /**
     * An error occurred.
     *
     * @param Test       $test
     * @param \Throwable $t
     * @param float      $time
     */
    public function addError(Test $test, \Throwable $t, float $time): void;

    /**
     * A warning occurred.
     *
     * @param Test    $test
     * @param Warning $e
     * @param float   $time
     */
    public function addWarning(Test $test, Warning $e, float $time): void;

    /**
     * A failure occurred.
     *
     * @param Test                 $test
     * @param AssertionFailedError $e
     * @param float                $time
     */
    public function addFailure(Test $test, AssertionFailedError $e, float $time): void;

    /**
     * Incomplete test.
     *
     * @param Test       $test
     * @param \Throwable $t
     * @param float      $time
     */
    public function addIncompleteTest(Test $test, \Throwable $t, float $time): void;

    /**
     * Risky test.
     *
     * @param Test       $test
     * @param \Throwable $t
     * @param float      $time
     */
    public function addRiskyTest(Test $test, \Throwable $t, float $time): void;

    /**
     * Skipped test.
     *
     * @param Test       $test
     * @param \Throwable $t
     * @param float      $time
     */
    public function addSkippedTest(Test $test, \Throwable $t, float $time): void;

    /**
     * A test suite started.
     *
     * @param TestSuite $suite
     */
    public function startTestSuite(TestSuite $suite): void;

    /**
     * A test suite ended.
     *
     * @param TestSuite $suite
     */
    public function endTestSuite(TestSuite $suite): void;

    /**
     * A test started.
     *
     * @param Test $test
     */
    public function startTest(Test $test): void;

    /**
     * A test ended.
     *
     * @param Test  $test
     * @param float $time
     */
    public function endTest(Test $test, float $time): void;
}
