<?php

/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A TestListener that generates a logfile of the test execution using the
 * TeamCity format (for use with PhpStorm, for instance).
 *
 * @since Class available since Release 5.0
 */
class PHPUnit_Util_Log_TeamCity extends PHPUnit_TextUI_ResultPrinter
{
    private $isSummaryTestCountPrinted = false;
    private $startedTestName;

    protected function writeProgress($progress)
    {
        //ignore
    }

    /**
     * @param PHPUnit_Framework_TestResult $result
     */
    public function printResult(PHPUnit_Framework_TestResult $result)
    {
        $this->printHeader();
        $this->printFooter($result);
    }

    /**
     * An error occurred.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->printEvent('testFailed', array(
            'name' => $test->getName(),
            'message' => self::getMessage($e),
            'details' => self::getDetails($e),
        ));
    }

    /**
     * A failure occurred.
     *
     * @param PHPUnit_Framework_Test                 $test
     * @param PHPUnit_Framework_AssertionFailedError $e
     * @param float                                  $time
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $params = array(
            'name' => $test->getName(),
            'message' => self::getMessage($e),
            'details' => self::getDetails($e),
        );
        if ($e instanceof PHPUnit_Framework_ExpectationFailedException) {
            $comparisonFailure = $e->getComparisonFailure();
            if ($comparisonFailure instanceof \SebastianBergmann\Comparator\ComparisonFailure) {
                $expectedString = $comparisonFailure->getExpectedAsString();
                if (is_null($expectedString) || empty($expectedString)) {
                    $expectedString = self::getPrimitiveValueAsString($comparisonFailure->getExpected());
                }

                $actualString = $comparisonFailure->getActualAsString();
                if (is_null($actualString) || empty($actualString)) {
                    $actualString = self::getPrimitiveValueAsString($comparisonFailure->getActual());
                }

                if (!is_null($actualString) && !is_null($expectedString)) {
                    $params['actual'] = $actualString;
                    $params['expected'] = $expectedString;
                }
            }
        }
        $this->printEvent('testFailed', $params);
    }

    /**
     * Incomplete test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->printIgnoredTest($test->getName(), $e);
    }

    /**
     * Risky test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     *
     * @since  Method available since Release 4.0.0
     */
    public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->addError($test, $e, $time);
    }

    /**
     * Skipped test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     *
     * @since  Method available since Release 3.0.0
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $testName = $test->getName();
        if ($this->startedTestName != $testName) {
            $this->startTest($test);
            $this->printIgnoredTest($testName, $e);
            $this->endTest($test, $time);
        } else {
            $this->printIgnoredTest($testName, $e);
        }
    }

    public function printIgnoredTest($testName, Exception $e)
    {
        $this->printEvent('testIgnored', array(
            'name' => $testName,
            'message' => self::getMessage($e),
            'details' => self::getDetails($e),
        ));
    }

    /**
     * A testsuite started.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        if (!$this->isSummaryTestCountPrinted) {
            $this->isSummaryTestCountPrinted = true;
            //print tests count
            $this->printEvent('testCount', array(
                'count' => count($suite),
            ));
        }

        $suiteName = $suite->getName();
        if (empty($suiteName)) {
            return;
        }
        $params = array(
            'name' => $suiteName,
        );
        if (class_exists($suiteName, false)) {
            $fileName = self::getFileName($suiteName);
            $params['locationHint'] = "php_qn://$fileName::\\$suiteName";
        } else {
            $split = preg_split('/::/', $suiteName);
            if (sizeof($split) == 2 && method_exists($split[0], $split[1])) {
                $fileName = self::getFileName($split[0]);
                $params['locationHint'] = "php_qn://$fileName::\\$suiteName";
                $params['name'] = $split[1];
            }
        }
        $this->printEvent('testSuiteStarted', $params);
    }

    /**
     * A testsuite ended.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $suiteName = $suite->getName();
        if (empty($suiteName)) {
            return;
        }

        $params = array(
            'name' => $suiteName,
        );

        if (!class_exists($suiteName, false)) {
            $split = preg_split('/::/', $suiteName);
            if (sizeof($split) == 2 && method_exists($split[0], $split[1])) {
                $params['name'] = $split[1];
            }
        }
        $this->printEvent('testSuiteFinished', $params);
    }

    /**
     * A test started.
     *
     * @param PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        $testName = $test->getName();
        $this->startedTestName = $testName;
        $params = array(
            'name' => $testName,
        );
        if ($test instanceof PHPUnit_Framework_TestCase) {
            $className = get_class($test);
            $fileName = self::getFileName($className);
            $params['locationHint'] = "php_qn://$fileName::\\$className::$testName";
        }
        $this->printEvent('testStarted', $params);
    }

    /**
     * A test ended.
     *
     * @param PHPUnit_Framework_Test $test
     * @param float                  $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        parent::endTest($test, $time);
        $this->printEvent('testFinished', array(
            'name' => $test->getName(),
            'duration' => (int) (round($time, 2) * 1000),
        ));
    }

    private function printEvent($eventName, $params = array())
    {
        $this->write("\n##teamcity[$eventName");
        foreach ($params as $key => $value) {
            $escapedValue = self::escapeValue($value);
            $this->write(" $key='$escapedValue'");
        }
        $this->write("]\n");
    }

    private static function getMessage(Exception $e)
    {
        $message = '';
        if (!($e instanceof PHPUnit_Framework_Exception)) {
            if (strlen(get_class($e)) != 0) {
                $message = $message.get_class($e);
            }
            if (strlen($message) != 0 && strlen($e->getMessage()) != 0) {
                $message = $message.' : ';
            }
        }

        return $message.$e->getMessage();
    }

    private static function getDetails(Exception $e)
    {
        $stackTrace = PHPUnit_Util_Filter::getFilteredStacktrace($e);

        $previous = $e->getPrevious();
        while ($previous) {
            $stackTrace .= "\nCaused by\n".
                PHPUnit_Framework_TestFailure::exceptionToString($previous)."\n".
                PHPUnit_Util_Filter::getFilteredStacktrace($previous);
            $previous = $previous->getPrevious();
        }

        return ' '.str_replace("\n", "\n ", $stackTrace);
    }

    private static function getPrimitiveValueAsString($value)
    {
        if (is_null($value)) {
            return 'null';
        } elseif (is_bool($value)) {
            return $value == true ? 'true' : 'false';
        } elseif (is_scalar($value)) {
            return print_r($value, true);
        }

        return;
    }

    private static function escapeValue($text)
    {
        $text = str_replace('|', '||', $text);
        $text = str_replace("'", "|'", $text);
        $text = str_replace("\n", '|n', $text);
        $text = str_replace("\r", '|r', $text);
        $text = str_replace(']', '|]', $text);
        $text = str_replace('[', '|[', $text);

        return $text;
    }

    private static function getFileName($className)
    {
        $reflectionClass = new ReflectionClass($className);
        $fileName = $reflectionClass->getFileName();

        return $fileName;
    }
}
