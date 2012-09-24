<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2012, Sebastian Bergmann <sebastian@phpunit.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    PHPUnit
 * @subpackage Framework
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.0.0
 */

/**
 * A TestResult collects the results of executing a test case.
 *
 * @package    PHPUnit
 * @subpackage Framework
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.0.0
 */
class PHPUnit_Framework_TestResult implements Countable
{
    /**
     * @var boolean
     */
    protected static $xdebugLoaded = NULL;

    /**
     * @var boolean
     */
    protected static $useXdebug = NULL;

    /**
     * @var array
     */
    protected $passed = array();

    /**
     * @var array
     */
    protected $errors = array();

    /**
     * @var array
     */
    protected $deprecatedFeatures = array();

    /**
     * @var array
     */
    protected $failures = array();

    /**
     * @var array
     */
    protected $notImplemented = array();

    /**
     * @var array
     */
    protected $skipped = array();

    /**
     * @var array
     */
    protected $listeners = array();

    /**
     * @var integer
     */
    protected $runTests = 0;

    /**
     * @var float
     */
    protected $time = 0;

    /**
     * @var PHPUnit_Framework_TestSuite
     */
    protected $topTestSuite = NULL;

    /**
     * Code Coverage information.
     *
     * @var PHP_CodeCoverage
     */
    protected $codeCoverage;

    /**
     * @var boolean
     */
    protected $convertErrorsToExceptions = TRUE;

    /**
     * @var boolean
     */
    protected $stop = FALSE;

    /**
     * @var boolean
     */
    protected $stopOnError = FALSE;

    /**
     * @var boolean
     */
    protected $stopOnFailure = FALSE;

    /**
     * @var boolean
     */
    protected $strictMode = FALSE;

    /**
     * @var boolean
     */
    protected $stopOnIncomplete = FALSE;

    /**
     * @var boolean
     */
    protected $stopOnSkipped = FALSE;

    /**
     * @var boolean
     */
    protected $lastTestFailed = FALSE;

    /**
     * @var integer
     */
    protected $timeoutForSmallTests = 1;

    /**
     * @var integer
     */
    protected $timeoutForMediumTests = 10;

    /**
     * @var integer
     */
    protected $timeoutForLargeTests = 60;

    /**
     * Registers a TestListener.
     *
     * @param  PHPUnit_Framework_TestListener
     */
    public function addListener(PHPUnit_Framework_TestListener $listener)
    {
        $this->listeners[] = $listener;
    }

    /**
     * Unregisters a TestListener.
     *
     * @param  PHPUnit_Framework_TestListener $listener
     */
    public function removeListener(PHPUnit_Framework_TestListener $listener)
    {
        foreach ($this->listeners as $key => $_listener) {
            if ($listener === $_listener) {
                unset($this->listeners[$key]);
            }
        }
    }

    /**
     * Flushes all flushable TestListeners.
     *
     * @since  Method available since Release 3.0.0
     */
    public function flushListeners()
    {
        foreach ($this->listeners as $listener) {
            if ($listener instanceof PHPUnit_Util_Printer) {
                $listener->flush();
            }
        }
    }

    /**
     * Adds an error to the list of errors.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        if ($e instanceof PHPUnit_Framework_IncompleteTest) {
            $this->notImplemented[] = new PHPUnit_Framework_TestFailure(
              $test, $e
            );

            $notifyMethod = 'addIncompleteTest';

            if ($this->stopOnIncomplete) {
                $this->stop();
            }
        }

        else if ($e instanceof PHPUnit_Framework_SkippedTest) {
            $this->skipped[] = new PHPUnit_Framework_TestFailure($test, $e);
            $notifyMethod    = 'addSkippedTest';

            if ($this->stopOnSkipped) {
                $this->stop();
            }
        }

        else {
            $this->errors[] = new PHPUnit_Framework_TestFailure($test, $e);
            $notifyMethod   = 'addError';

            if ($this->stopOnError || $this->stopOnFailure) {
                $this->stop();
            }
        }

        foreach ($this->listeners as $listener) {
            $listener->$notifyMethod($test, $e, $time);
        }

        $this->lastTestFailed = TRUE;
        $this->time          += $time;
    }

    /**
     * Adds a failure to the list of failures.
     * The passed in exception caused the failure.
     *
     * @param  PHPUnit_Framework_Test                 $test
     * @param  PHPUnit_Framework_AssertionFailedError $e
     * @param  float                                  $time
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        if ($e instanceof PHPUnit_Framework_IncompleteTest) {
            $this->notImplemented[] = new PHPUnit_Framework_TestFailure(
              $test, $e
            );

            $notifyMethod = 'addIncompleteTest';

            if ($this->stopOnIncomplete) {
                $this->stop();
            }
        }

        else if ($e instanceof PHPUnit_Framework_SkippedTest) {
            $this->skipped[] = new PHPUnit_Framework_TestFailure($test, $e);
            $notifyMethod    = 'addSkippedTest';

            if ($this->stopOnSkipped) {
                $this->stop();
            }
        }

        else {
            $this->failures[] = new PHPUnit_Framework_TestFailure($test, $e);
            $notifyMethod     = 'addFailure';

            if ($this->stopOnFailure) {
                $this->stop();
            }
        }

        foreach ($this->listeners as $listener) {
            $listener->$notifyMethod($test, $e, $time);
        }

        $this->lastTestFailed = TRUE;
        $this->time          += $time;
    }

    /**
     * Adds a deprecated feature notice to the list of deprecated features used during run
     *
     * @param PHPUnit_Util_DeprecatedFeature $deprecatedFeature
     */
    public function addDeprecatedFeature(PHPUnit_Util_DeprecatedFeature $deprecatedFeature)
    {
        $this->deprecatedFeatures[] = $deprecatedFeature;
    }

    /**
     * Informs the result that a testsuite will be started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        if ($this->topTestSuite === NULL) {
            $this->topTestSuite = $suite;
        }

        foreach ($this->listeners as $listener) {
            $listener->startTestSuite($suite);
        }
    }

    /**
     * Informs the result that a testsuite was completed.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        foreach ($this->listeners as $listener) {
            $listener->endTestSuite($suite);
        }
    }

    /**
     * Informs the result that a test will be started.
     *
     * @param  PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        $this->lastTestFailed = FALSE;
        $this->runTests      += count($test);

        foreach ($this->listeners as $listener) {
            $listener->startTest($test);
        }
    }

    /**
     * Informs the result that a test was completed.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  float                  $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        foreach ($this->listeners as $listener) {
            $listener->endTest($test, $time);
        }

        if (!$this->lastTestFailed && $test instanceof PHPUnit_Framework_TestCase) {
            $class  = get_class($test);
            $key    =  $class . '::' . $test->getName();

            $this->passed[$key] = array(
              'result' => $test->getResult(),
              'size'   => PHPUnit_Util_Test::getSize(
                            $class, $test->getName(FALSE)
                          )
            );

            $this->time += $time;
        }
    }

    /**
     * Returns TRUE if no incomplete test occured.
     *
     * @return boolean
     */
    public function allCompletlyImplemented()
    {
        return $this->notImplementedCount() == 0;
    }

    /**
     * Gets the number of incomplete tests.
     *
     * @return integer
     */
    public function notImplementedCount()
    {
        return count($this->notImplemented);
    }

    /**
     * Returns an Enumeration for the incomplete tests.
     *
     * @return array
     */
    public function notImplemented()
    {
        return $this->notImplemented;
    }

    /**
     * Returns TRUE if no test has been skipped.
     *
     * @return boolean
     * @since  Method available since Release 3.0.0
     */
    public function noneSkipped()
    {
        return $this->skippedCount() == 0;
    }

    /**
     * Gets the number of skipped tests.
     *
     * @return integer
     * @since  Method available since Release 3.0.0
     */
    public function skippedCount()
    {
        return count($this->skipped);
    }

    /**
     * Returns an Enumeration for the skipped tests.
     *
     * @return array
     * @since  Method available since Release 3.0.0
     */
    public function skipped()
    {
        return $this->skipped;
    }

    /**
     * Gets the number of detected errors.
     *
     * @return integer
     */
    public function errorCount()
    {
        return count($this->errors);
    }

    /**
     * Returns an Enumeration for the errors.
     *
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Returns an Enumeration for the deprecated features used.
     *
     * @return array
     * @since  Method available since Release 3.5.7
     */
    public function deprecatedFeatures()
    {
        return $this->deprecatedFeatures;
    }

    /**
     * Returns an Enumeration for the deprecated features used.
     *
     * @return array
     * @since  Method available since Release 3.5.7
     */
    public function deprecatedFeaturesCount()
    {
        return count($this->deprecatedFeatures);
    }

    /**
     * Gets the number of detected failures.
     *
     * @return integer
     */
    public function failureCount()
    {
        return count($this->failures);
    }

    /**
     * Returns an Enumeration for the failures.
     *
     * @return array
     */
    public function failures()
    {
        return $this->failures;
    }

    /**
     * Returns the names of the tests that have passed.
     *
     * @return array
     * @since  Method available since Release 3.4.0
     */
    public function passed()
    {
        return $this->passed;
    }

    /**
     * Returns the (top) test suite.
     *
     * @return PHPUnit_Framework_TestSuite
     * @since  Method available since Release 3.0.0
     */
    public function topTestSuite()
    {
        return $this->topTestSuite;
    }

    /**
     * Returns whether code coverage information should be collected.
     *
     * @return boolean If code coverage should be collected
     * @since  Method available since Release 3.2.0
     */
    public function getCollectCodeCoverageInformation()
    {
        return $this->codeCoverage !== NULL;
    }

    /**
     * Returns the strict mode configuration option
     *
     * @return boolean
     */
    public function isStrict()
    {
        return $this->strictMode;
    }

    /**
     * Runs a TestCase.
     *
     * @param  PHPUnit_Framework_Test $test
     */
    public function run(PHPUnit_Framework_Test $test)
    {
        PHPUnit_Framework_Assert::resetCount();

        $error      = FALSE;
        $failure    = FALSE;
        $incomplete = FALSE;
        $skipped    = FALSE;

        $this->startTest($test);

        $errorHandlerSet = FALSE;

        if ($this->convertErrorsToExceptions) {
            $oldErrorHandler = set_error_handler(
              array('PHPUnit_Util_ErrorHandler', 'handleError'),
              E_ALL | E_STRICT
            );

            if ($oldErrorHandler === NULL) {
                $errorHandlerSet = TRUE;
            } else {
                restore_error_handler();
            }
        }

        if (self::$xdebugLoaded === NULL) {
            self::$xdebugLoaded = extension_loaded('xdebug');
            self::$useXdebug    = self::$xdebugLoaded;
        }

        $useXdebug = self::$useXdebug &&
                     $this->codeCoverage !== NULL &&
                     !$test instanceof PHPUnit_Extensions_SeleniumTestCase &&
                     !$test instanceof PHPUnit_Framework_Warning;

        if ($useXdebug) {
            // We need to blacklist test source files when no whitelist is used.
            if (!$this->codeCoverage->filter()->hasWhitelist()) {
                $classes = PHPUnit_Util_Class::getHierarchy(
                  get_class($test), TRUE
                );

                foreach ($classes as $class) {
                    $this->codeCoverage->filter()->addFileToBlacklist(
                      $class->getFileName()
                    );
                }
            }

            $this->codeCoverage->start($test);
        }

        PHP_Timer::start();

        try {
            if (!$test instanceof PHPUnit_Framework_Warning &&
                $this->strictMode &&
                extension_loaded('pcntl') && class_exists('PHP_Invoker')) {
                switch ($test->getSize()) {
                    case PHPUnit_Util_Test::SMALL: {
                        $_timeout = $this->timeoutForSmallTests;
                    }
                    break;

                    case PHPUnit_Util_Test::MEDIUM: {
                        $_timeout = $this->timeoutForMediumTests;
                    }
                    break;

                    case PHPUnit_Util_Test::LARGE: {
                        $_timeout = $this->timeoutForLargeTests;
                    }
                    break;
                }

                $invoker = new PHP_Invoker;
                $invoker->invoke(array($test, 'runBare'), array(), $_timeout);
            } else {
                $test->runBare();
            }
        }

        catch (PHPUnit_Framework_AssertionFailedError $e) {
            $failure = TRUE;

            if ($e instanceof PHPUnit_Framework_IncompleteTestError) {
                $incomplete = TRUE;
            }

            else if ($e instanceof PHPUnit_Framework_SkippedTestError) {
                $skipped = TRUE;
            }
        }

        catch (Exception $e) {
            $error = TRUE;
        }

        $time = PHP_Timer::stop();
        $test->addToAssertionCount(PHPUnit_Framework_Assert::getCount());

        if ($this->strictMode && $test->getNumAssertions() == 0) {
            $incomplete = TRUE;
        }

        if ($useXdebug) {
            try {
                $this->codeCoverage->stop(!$incomplete && !$skipped);
            }

            catch (PHP_CodeCoverage_Exception $cce) {
                $error = TRUE;

                if (!isset($e)) {
                    $e = $cce;
                }
            }
        }

        if ($errorHandlerSet === TRUE) {
            restore_error_handler();
        }

        if ($error === TRUE) {
            $this->addError($test, $e, $time);
        }

        else if ($failure === TRUE) {
            $this->addFailure($test, $e, $time);
        }

        else if ($this->strictMode && $test->getNumAssertions() == 0) {
            $this->addFailure(
              $test,
              new PHPUnit_Framework_IncompleteTestError(
                'This test did not perform any assertions'
              ),
              $time
            );
        }

        else if ($this->strictMode && $test->hasOutput()) {
            $this->addFailure(
              $test,
              new PHPUnit_Framework_OutputError(
                sprintf(
                  'This test printed output: %s',
                  $test->getActualOutput()
                )
              ),
              $time
            );
        }

        $this->endTest($test, $time);
    }

    /**
     * Gets the number of run tests.
     *
     * @return integer
     */
    public function count()
    {
        return $this->runTests;
    }

    /**
     * Checks whether the test run should stop.
     *
     * @return boolean
     */
    public function shouldStop()
    {
        return $this->stop;
    }

    /**
     * Marks that the test run should stop.
     *
     */
    public function stop()
    {
        $this->stop = TRUE;
    }

    /**
     * Returns the PHP_CodeCoverage object.
     *
     * @return PHP_CodeCoverage
     * @since  Method available since Release 3.5.0
     */
    public function getCodeCoverage()
    {
        return $this->codeCoverage;
    }

    /**
     * Returns the PHP_CodeCoverage object.
     *
     * @return PHP_CodeCoverage
     * @since  Method available since Release 3.6.0
     */
    public function setCodeCoverage(PHP_CodeCoverage $codeCoverage)
    {
        $this->codeCoverage = $codeCoverage;
    }

    /**
     * Enables or disables the error-to-exception conversion.
     *
     * @param  boolean $flag
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.2.14
     */
    public function convertErrorsToExceptions($flag)
    {
        if (is_bool($flag)) {
            $this->convertErrorsToExceptions = $flag;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
    }

    /**
     * Returns the error-to-exception conversion setting.
     *
     * @return boolean
     * @since  Method available since Release 3.4.0
     */
    public function getConvertErrorsToExceptions()
    {
        return $this->convertErrorsToExceptions;
    }

    /**
     * Enables or disables the stopping when an error occurs.
     *
     * @param  boolean $flag
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.5.0
     */
    public function stopOnError($flag)
    {
        if (is_bool($flag)) {
            $this->stopOnError = $flag;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
    }

    /**
     * Enables or disables the stopping when a failure occurs.
     *
     * @param  boolean $flag
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.1.0
     */
    public function stopOnFailure($flag)
    {
        if (is_bool($flag)) {
            $this->stopOnFailure = $flag;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
    }

    /**
     * Enables or disables the strict mode.
     *
     * When active
     *   * Tests that do not assert anything will be marked as incomplete.
     *   * Tests that are incomplete or skipped yield no code coverage.
     *
     * @param  boolean $flag
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.5.2
     */
    public function strictMode($flag)
    {
        if (is_bool($flag)) {
            $this->strictMode = $flag;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
    }

    /**
     * Enables or disables the stopping for incomplete tests.
     *
     * @param  boolean $flag
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.5.0
     */
    public function stopOnIncomplete($flag)
    {
        if (is_bool($flag)) {
            $this->stopOnIncomplete = $flag;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
    }

    /**
     * Enables or disables the stopping for skipped tests.
     *
     * @param  boolean $flag
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.1.0
     */
    public function stopOnSkipped($flag)
    {
        if (is_bool($flag)) {
            $this->stopOnSkipped = $flag;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }
    }

    /**
     * Returns the time spent running the tests.
     *
     * @return float
     */
    public function time()
    {
        return $this->time;
    }

    /**
     * Returns whether the entire test was successful or not.
     *
     * @return boolean
     */
    public function wasSuccessful()
    {
        return empty($this->errors) && empty($this->failures);
    }

    /**
     * Sets the timeout for small tests.
     *
     * @param  integer $timeout
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.6.0
     */
    public function setTimeoutForSmallTests($timeout)
    {
        if (is_integer($timeout)) {
            $this->timeoutForSmallTests = $timeout;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'integer');
        }
    }

    /**
     * Sets the timeout for medium tests.
     *
     * @param  integer $timeout
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.6.0
     */
    public function setTimeoutForMediumTests($timeout)
    {
        if (is_integer($timeout)) {
            $this->timeoutForMediumTests = $timeout;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'integer');
        }
    }

    /**
     * Sets the timeout for large tests.
     *
     * @param  integer $timeout
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.6.0
     */
    public function setTimeoutForLargeTests($timeout)
    {
        if (is_integer($timeout)) {
            $this->timeoutForLargeTests = $timeout;
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'integer');
        }
    }
}
