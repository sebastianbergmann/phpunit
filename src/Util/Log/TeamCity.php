<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Util\Log;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\TextUI\ResultPrinter;

/**
 * A TestListener that generates a logfile of the test execution using the
 * TeamCity format (for use with PhpStorm, for instance).
 */
class TeamCity extends ResultPrinter
{
    use TeamCityUtils;

    /**
     * @var bool
     */
    private $hasPrintedTestCount = false;

    public function addError(Test $test, \Exception $e, $time)
    {
        $this->testFailed($test, $e, $time);
    }

    public function addFailure(Test $test, AssertionFailedError $e, $time)
    {
        $this->testFailed($test, $e, $time);
    }

    public function addWarning(Test $test, Warning $e, $time)
    {
        $this->testFailed($test, $e, $time);
    }

    public function addIncompleteTest(Test $test, \Exception $e, $time)
    {
        $this->message('testIgnored', [
            'name'    => $this->getTestName($test),
            'message' => sprintf("%s::%s is marked as ignored: %s\n", get_class($test), $this->getTestName($test), $e->getMessage()),
            'duration'=> floor($time * 1000)
        ]);
    }

    public function addRiskyTest(Test $test, \Exception $e, $time)
    {
        $this->message('testIgnored', [
            'name'    => $this->getTestName($test),
            'message' => sprintf("%s::%s is marked as risky: %s\n", get_class($test), $this->getTestName($test), $e->getMessage()),
            'duration'=> floor($time * 1000)
        ]);
    }

    public function addSkippedTest(Test $test, \Exception $e, $time)
    {
        $this->message('testIgnored', [
            'name'    => $this->getTestName($test),
            'message' => sprintf("%s::%s is marked as skipped: %s\n", get_class($test), $this->getTestName($test), $e->getMessage()),
            'duration'=> floor($time * 1000)
        ]);
    }

    public function writeProgress($string)
    {
    }

    public function printResult(TestResult $result)
    {
        $this->printHeader();
        $this->printFooter($result);
    }

    public function startTestSuite(TestSuite $suite)
    {
        if (!$this->hasPrintedTestCount) {
            $this->message(
                'testCount',
                [
                    'count' => $suite->count()
                ]
            );

            $this->hasPrintedTestCount = true;
        }

        $this->message(
            'testSuiteStarted',
            [
                'name'         => $this->getTestName($suite),
                'locationHint' => $this->getTestClassLocationHint($suite)
            ]
        );
    }

    public function endTestSuite(TestSuite $suite)
    {
        $this->message('testSuiteFinished', [
            'name' => $this->getTestName($suite),
        ]);
    }

    /**
     * @param Test $test
     */
    public function startTest(Test $test)
    {
        $this->message(
            'testStarted',
            [
                'name'                  => $this->getTestName($test),
                'locationHint'          => $this->getTestMethodLocationHint($test)
            ]
        );
    }

    public function endTest(Test $test, $time)
    {
        $this->message(
            'testFinished',
            [
                'name'     => $this->getTestName($test),
                'duration' => floor($time * 1000)
            ]
        );

        parent::endTest($test, $time);
    }
}
