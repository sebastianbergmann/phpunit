<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHP Version 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id: TestResult.php 539 2006-02-13 16:08:42Z sb $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit2/Framework/AssertionFailedError.php';
require_once 'PHPUnit2/Framework/IncompleteTest.php';
require_once 'PHPUnit2/Framework/TestListener.php';
require_once 'PHPUnit2/Util/ErrorHandler.php';
require_once 'PHPUnit2/Util/Filter.php';

/**
 * A TestResult collects the results of executing a test case.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      Class available since Release 2.0.0
 */
class PHPUnit2_Framework_TestResult {
    /**
     * @var    array
     * @access protected
     */
    protected $errors = array();

    /**
     * @var    array
     * @access protected
     */
    protected $failures = array();

    /**
     * @var    array
     * @access protected
     */
    protected $notImplemented = array();

    /**
     * @var    array
     * @access protected
     */
    protected $listeners = array();

    /**
     * @var    integer
     * @access protected
     */
    protected $runTests = 0;

    /**
     * Code Coverage information provided by Xdebug.
     *
     * @var    array
     * @access protected
     */
    protected $codeCoverageInformation = array();

    /**
     * @var    boolean
     * @access protected
     */
    protected $collectCodeCoverageInformation = FALSE;

    /**
     * @var    boolean
     * @access private
     */
    private $stop = FALSE;

    /**
     * Registers a TestListener.
     *
     * @param  PHPUnit2_Framework_TestListener
     * @access public
     */
    public function addListener(PHPUnit2_Framework_TestListener $listener) {
        $this->listeners[] = $listener;
    }

    /**
     * Unregisters a TestListener.
     *
     * @param  PHPUnit2_Framework_TestListener $listener
     * @access public
     */
    public function removeListener(PHPUnit2_Framework_TestListener $listener) {
        for ($i = 0; $i < sizeof($this->listeners); $i++) {
            if ($this->listeners[$i] === $listener) {
                unset($this->listeners[$i]);
            }
        }
    }

    /**
     * Adds an error to the list of errors.
     * The passed in exception caused the error.
     *
     * @param  PHPUnit2_Framework_Test $test
     * @param  Exception               $e
     * @access public
     */
    public function addError(PHPUnit2_Framework_Test $test, Exception $e) {
        if ($e instanceof PHPUnit2_Framework_IncompleteTest) {
            $this->notImplemented[] = new PHPUnit2_Framework_TestFailure($test, $e);

            foreach ($this->listeners as $listener) {
                $listener->addIncompleteTest($test, $e);
            }
        } else {
            $this->errors[] = new PHPUnit2_Framework_TestFailure($test, $e);

            foreach ($this->listeners as $listener) {
                $listener->addError($test, $e);
            }
        }
    }

    /**
     * Adds a failure to the list of failures.
     * The passed in exception caused the failure.
     *
     * @param  PHPUnit2_Framework_Test                  $test
     * @param  PHPUnit2_Framework_AssertionFailedError  $e
     * @access public
     */
    public function addFailure(PHPUnit2_Framework_Test $test, PHPUnit2_Framework_AssertionFailedError $e) {
        if ($e instanceof PHPUnit2_Framework_IncompleteTest) {
            $this->notImplemented[] = new PHPUnit2_Framework_TestFailure($test, $e);

            foreach ($this->listeners as $listener) {
                $listener->addIncompleteTest($test, $e);
            }
        } else {
            $this->failures[] = new PHPUnit2_Framework_TestFailure($test, $e);

            foreach ($this->listeners as $listener) {
                $listener->addFailure($test, $e);
            }
        }
    }

    /**
     * Informs the result that a testsuite will be started.
     *
     * @param  PHPUnit2_Framework_TestSuite $suite
     * @access public
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(PHPUnit2_Framework_TestSuite $suite) {
        foreach ($this->listeners as $listener) {
            $listener->startTestSuite($suite);
        }
    }

    /**
     * Informs the result that a testsuite was completed.
     *
     * @param  PHPUnit2_Framework_TestSuite $suite
     * @access public
     * @since  Method available since Release 2.2.0
     */
    public function endTestSuite(PHPUnit2_Framework_TestSuite $suite) {
        foreach ($this->listeners as $listener) {
            $listener->endTestSuite($suite);
        }
    }

    /**
     * Informs the result that a test will be started.
     *
     * @param  PHPUnit2_Framework_Test $test
     * @access public
     */
    public function startTest(PHPUnit2_Framework_Test $test) {
        $this->runTests += $test->countTestCases();

        foreach ($this->listeners as $listener) {
            $listener->startTest($test);
        }
    }

    /**
     * Informs the result that a test was completed.
     *
     * @param  PHPUnit2_Framework_Test
     * @access public
     */
    public function endTest(PHPUnit2_Framework_Test $test) {
        foreach ($this->listeners as $listener) {
            $listener->endTest($test);
        }
    }

    /**
     * Returns TRUE if no incomplete test occured.
     *
     * @return boolean
     * @access public
     */
    public function allCompletlyImplemented() {
        return $this->notImplementedCount() == 0;
    }

    /**
     * Gets the number of incomplete tests.
     *
     * @return integer
     * @access public
     */
    public function notImplementedCount() {
        return sizeof($this->notImplemented);
    }

    /**
     * Returns an Enumeration for the incomplete tests.
     *
     * @return array
     * @access public
     */
    public function notImplemented() {
        return $this->notImplemented;
    }

    /**
     * Gets the number of detected errors.
     *
     * @return integer
     * @access public
     */
    public function errorCount() {
        return sizeof($this->errors);
    }

    /**
     * Returns an Enumeration for the errors.
     *
     * @return array
     * @access public
     */
    public function errors() {
        return $this->errors;
    }

    /**
     * Gets the number of detected failures.
     *
     * @return integer
     * @access public
     */
    public function failureCount() {
        return sizeof($this->failures);
    }

    /**
     * Returns an Enumeration for the failures.
     *
     * @return array
     * @access public
     */
    public function failures() {
        return $this->failures;
    }

    /**
     * Enables or disables the collection of Code Coverage information.
     *
     * @param  boolean $flag
     * @throws Exception
     * @access public
     * @since  Method available since Release 2.3.0
     */
    public function collectCodeCoverageInformation($flag) {
        if (is_bool($flag)) {
            $this->collectCodeCoverageInformation = $flag;
        } else {
            throw new Exception;
        }
    }

    /**
     * Returns Code Coverage data per test case.
     *
     * Format of the result array:
     *
     * <code>
     * array(
     *   "testCase" => array(
     *     "/tested/code.php" => array(
     *       linenumber => numberOfExecutions
     *     )
     *   )
     * )
     * </code>
     *
     * @return array
     * @access public
     */
    public function getCodeCoverageInformation() {
        return $this->codeCoverageInformation;
    }

    /**
     * Runs a TestCase.
     *
     * @param  PHPUnit2_Framework_Test $test
     * @access public
     */
    public function run(PHPUnit2_Framework_Test $test) {
        $this->startTest($test);

        set_error_handler('PHPUnit2_Util_ErrorHandler', E_USER_ERROR);

        $useXdebug = (extension_loaded('xdebug') && $this->collectCodeCoverageInformation);

        if ($useXdebug) {
            xdebug_start_code_coverage(XDEBUG_CC_UNUSED);
        }

        $globalsBackup = $GLOBALS;

        try {
            $test->runBare();
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            $this->addFailure($test, $e);
        }

        catch (Exception $e) {
            $this->addError($test, $e);
        }

        $GLOBALS = $globalsBackup;

        if ($useXdebug) {
            $this->codeCoverageInformation[$test->getName()] = PHPUnit2_Util_Filter::getFilteredCodeCoverage(
              xdebug_get_code_coverage()
            );

            xdebug_stop_code_coverage();
        }

        restore_error_handler();

        $this->endTest($test);
    }

    /**
     * Gets the number of run tests.
     *
     * @return integer
     * @access public
     */
    public function runCount() {
        return $this->runTests;
    }

    /**
     * Checks whether the test run should stop.
     *
     * @return boolean
     * @access public
     */
    public function shouldStop() {
        return $this->stop;
    }

    /**
     * Marks that the test run should stop.
     *
     * @access public
     */
    public function stop() {
        $this->stop = TRUE;
    }

    /**
     * Returns whether the entire test was successful or not.
     *
     * @return boolean
     * @access public
     */
    public function wasSuccessful() {
        return empty($this->errors) && empty($this->failures);
    }
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
?>
