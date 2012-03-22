<?php
/**
 * @author Alexander Ilyin
 * @todo Implement getName() for PHPUnit_Framework_TestListener
 * @todo Add "expected" and "actual" values to PHPUnit_Framework_AssertionFailedError
 * @url http://confluence.jetbrains.net/display/TCD7/Build+Script+Interaction+with+TeamCity#BuildScriptInteractionwithTeamCity-ReportingTests
 */
class PHPUnit_Framework_TeamCity_TestListener implements PHPUnit_Framework_TestListener
{

    /**
     * An error occurred.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        printf("##teamcity[testFailed name='%s' message='%s' details='%s']", $test->getName(), $e->getMessage(), $e->getTraceAsString());
    }

    /**
     * A failure occurred.
     *
     * @param  PHPUnit_Framework_Test                 $test
     * @param  PHPUnit_Framework_AssertionFailedError $e
     * @param  float                                  $time
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $expected = null;
        $actual = null;
        printf("##teamcity[testFailed type='comparisonFailure' name='%s' message='%s' details='%s' expected='%s' actual='%']",
            $test->getName(),
            $e->getMessage(),
            $e->getTraceAsString(),
            $expected,
            $actual
        );
    }

    /**
     * Incomplete test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        printf("##teamcity[testIgnored name='%s' message='%s']", $test->getName(), $e->getMessage());
    }

    /**
     * Skipped test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     * @since  Method available since Release 3.0.0
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        printf("##teamcity[testIgnored name='%s' message='%s']", $test->getName(), $e->getMessage());
    }

    /**
     * A test suite started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        printf("##teamcity[testSuiteStarted name='%s']", $suite->getName());
    }

    /**
     * A test suite ended.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        printf("##teamcity[testSuiteFinished name='%s']", $suite->getName());
    }

    /**
     * A test started.
     *
     * @param  PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        printf("##teamcity[testStarted name='%s' captureStandardOutput='%s']", $test->getName(), 'true');
    }

    /**
     * A test ended.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  float                  $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        printf("#teamcity[testFinished name='%s' duration='%s']", $test->getName(), $time);
    }
}
