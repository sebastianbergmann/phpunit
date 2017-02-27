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
use PHPUnit\Framework\Warning;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Test;
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
    private $isSummaryTestCountPrinted = false;

    /**
     * @var string
     */
    private $startedTestName;

    /**
     * @param string $progress
     */
    protected function writeProgress($progress)
    {
    }

    /**
     * @param TestResult $result
     */
    public function printResult(TestResult $result)
    {
        $this->printHeader();
        $this->printFooter($result);
    }

    /**
     * An error occurred.
     *
     * @param Test       $test
     * @param \Exception $e
     * @param float      $time
     */
    public function addError(Test $test, \Exception $e, $time)
    {
        $this->testFailed($test, $e);
    }

    /**
     * A warning occurred.
     *
     * @param Test    $test
     * @param Warning $e
     * @param float   $time
     */
    public function addWarning(Test $test, Warning $e, $time)
    {
        $this->testFailed($test, $e);
    }

    /**
     * A failure occurred.
     *
     * @param Test                 $test
     * @param AssertionFailedError $e
     * @param float                $time
     */
    public function addFailure(Test $test, AssertionFailedError $e, $time)
    {
        $this->testFailed($test, $e);
    }

    /**
     * Incomplete test.
     *
     * @param Test       $test
     * @param \Exception $e
     * @param float      $time
     */
    public function addIncompleteTest(Test $test, \Exception $e, $time)
    {
        $this->testIgnored($test->getName(), $e);
    }

    /**
     * Risky test.
     *
     * @param Test       $test
     * @param \Exception $e
     * @param float      $time
     */
    public function addRiskyTest(Test $test, \Exception $e, $time)
    {
        $this->addError($test, $e, $time);
    }

    /**
     * Skipped test.
     *
     * @param Test       $test
     * @param \Exception $e
     * @param float      $time
     */
    public function addSkippedTest(Test $test, \Exception $e, $time)
    {
        $testName = $test->getName();
        if ($this->startedTestName != $testName) {
            $this->startTest($test);
            $this->testIgnored($testName, $e);
            $this->endTest($test, $time);
        } else {
            $this->testIgnored($testName, $e);
        }
    }

    /**
     * A testsuite started.
     *
     * @param TestSuite $suite
     */
    public function startTestSuite(TestSuite $suite)
    {
        if (!$this->isSummaryTestCountPrinted) {
            $this->isSummaryTestCountPrinted = true;

            $this->message(
                'testCount',
                ['count' => count($suite)]
            );
        }

        $suiteName = $suite->getName();

        if (empty($suiteName)) {
            return;
        }

        $parameters = ['name' => $suiteName];

        if (class_exists($suiteName, false)) {
            $fileName                   = self::getFileName($suiteName);
            $parameters['locationHint'] = "php_qn://$fileName::\\$suiteName";
        } else {
            $split = preg_split('/::/', $suiteName);

            if (count($split) == 2 && method_exists($split[0], $split[1])) {
                $fileName                   = self::getFileName($split[0]);
                $parameters['locationHint'] = "php_qn://$fileName::\\$suiteName";
                $parameters['name']         = $split[1];
            }
        }

        $this->message('testSuiteStarted', $parameters);
    }

    /**
     * A testsuite ended.
     *
     * @param TestSuite $suite
     */
    public function endTestSuite(TestSuite $suite)
    {
        $suiteName = $suite->getName();

        if (empty($suiteName)) {
            return;
        }

        $parameters = ['name' => $suiteName];

        if (!class_exists($suiteName, false)) {
            $split = preg_split('/::/', $suiteName);

            if (count($split) == 2 && method_exists($split[0], $split[1])) {
                $parameters['name'] = $split[1];
            }
        }

        $this->message('testSuiteFinished', $parameters);
    }

    /**
     * A test started.
     *
     * @param Test $test
     */
    public function startTest(Test $test)
    {
        $testName              = $test->getName();
        $this->startedTestName = $testName;
        $params                = ['name' => $testName];

        if ($test instanceof TestCase) {
            $className              = get_class($test);
            $fileName               = self::getFileName($className);
            $params['locationHint'] = "php_qn://$fileName::\\$className::$testName";
        }

        $this->message('testStarted', $params);
    }

    /**
     * A test ended.
     *
     * @param Test  $test
     * @param float $time
     */
    public function endTest(Test $test, $time)
    {
        parent::endTest($test, $time);

        $this->message(
            'testFinished',
            [
                'name'     => $test->getName(),
                'duration' => (int) (round($time, 2) * 1000)
            ]
        );
    }
}
