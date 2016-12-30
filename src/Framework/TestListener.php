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

use Exception;
use PHPUnit_Framework_Warning;

/**
 * A Listener for test progress.
 *
 * @since Interface available since Release 2.0.0
 */
interface TestListener
{
    /**
     * An error occurred.
     *
     * @param Test      $test
     * @param Exception $e
     * @param float     $time
     */
    public function addError(Test $test, Exception $e, $time);

    /**
     * A warning occurred.
     *
     * @param Test                      $test
     * @param PHPUnit_Framework_Warning $e
     * @param float                     $time
     *
     * @since Method available since Release 6.0.0
     */
    public function addWarning(Test $test, PHPUnit_Framework_Warning $e, $time);

    /**
     * A failure occurred.
     *
     * @param Test                 $test
     * @param AssertionFailedError $e
     * @param float                $time
     */
    public function addFailure(Test $test, AssertionFailedError $e, $time);

    /**
     * Incomplete test.
     *
     * @param Test      $test
     * @param Exception $e
     * @param float     $time
     */
    public function addIncompleteTest(Test $test, Exception $e, $time);

    /**
     * Risky test.
     *
     * @param Test      $test
     * @param Exception $e
     * @param float     $time
     *
     * @since Method available since Release 4.0.0
     */
    public function addRiskyTest(Test $test, Exception $e, $time);

    /**
     * Skipped test.
     *
     * @param Test      $test
     * @param Exception $e
     * @param float     $time
     *
     * @since Method available since Release 3.0.0
     */
    public function addSkippedTest(Test $test, Exception $e, $time);

    /**
     * A test suite started.
     *
     * @param TestSuite $suite
     *
     * @since Method available since Release 2.2.0
     */
    public function startTestSuite(TestSuite $suite);

    /**
     * A test suite ended.
     *
     * @param TestSuite $suite
     *
     * @since Method available since Release 2.2.0
     */
    public function endTestSuite(TestSuite $suite);

    /**
     * A test started.
     *
     * @param Test $test
     */
    public function startTest(Test $test);

    /**
     * A test ended.
     *
     * @param Test  $test
     * @param float $time
     */
    public function endTest(Test $test, $time);
}
