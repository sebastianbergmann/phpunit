<?php
//
// +------------------------------------------------------------------------+
// | PEAR :: PHPUnit2                                                       |
// +------------------------------------------------------------------------+
// | Copyright (c) 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>. |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
//
// $Id: TestResult.php 539 2006-02-13 16:08:42Z sb $
//

require_once 'PHPUnit2/Framework/AssertionFailedError.php';
require_once 'PHPUnit2/Framework/Error.php';
require_once 'PHPUnit2/Framework/IncompleteTest.php';
require_once 'PHPUnit2/Framework/Test.php';
require_once 'PHPUnit2/Framework/TestFailure.php';
require_once 'PHPUnit2/Framework/TestListener.php';
require_once 'PHPUnit2/Framework/TestSuite.php';

/**
 * A TestResult collects the results of executing a test case.
 *
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2
 * @subpackage  Framework
 */
class PHPUnit2_Framework_TestResult {
    // {{{ Instance Variables

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
    * @access private
    */
    private $codeCoverageInformation = array();

    /**
    * @var    boolean
    * @access private
    */
    private $stop = FALSE;

    // }}}
    // {{{ public function addListener(PHPUnit2_Framework_TestListener $listener)

    /**
    * Registers a TestListener.
    *
    * @param  PHPUnit2_Framework_TestListener
    * @access public
    */
    public function addListener(PHPUnit2_Framework_TestListener $listener) {
        $this->listeners[] = $listener;
    }

    // }}}
    // {{{ public function removeListener(PHPUnit2_Framework_TestListener $listener)

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

    // }}}
    // {{{ public function addError(PHPUnit2_Framework_Test $test, Exception $e)

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

    // }}}
    // {{{ public function addFailure(PHPUnit2_Framework_Test $test, PHPUnit2_Framework_AssertionFailedError $e)

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

    // }}}
    // {{{ public function startTestSuite(PHPUnit2_Framework_TestSuite $suite)

    /**
    * Informs the result that a testsuite will be started.
    *
    * @param  PHPUnit2_Framework_TestSuite $suite
    * @access public
    * @since  2.2.0
    */
    public function startTestSuite(PHPUnit2_Framework_TestSuite $suite) {
        foreach ($this->listeners as $listener) {
            $listener->startTestSuite($suite);
        }
    }

    // }}}
    // {{{ public function endTestSuite(PHPUnit2_Framework_TestSuite $suite)

    /**
    * Informs the result that a testsuite was completed.
    *
    * @param  PHPUnit2_Framework_TestSuite $suite
    * @access public
    * @since  2.2.0
    */
    public function endTestSuite(PHPUnit2_Framework_TestSuite $suite) {
        foreach ($this->listeners as $listener) {
            $listener->endTestSuite($suite);
        }
    }

    // }}}
    // {{{ public function startTest(PHPUnit2_Framework_Test $test)

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

    // }}}
    // {{{ public function endTest(PHPUnit2_Framework_Test $test)

    /**
    * Informs the result that a test was completed.
    *
    * @param  PHPUnit2_Framework_Test
    * @access public
    */
    public function endTest(PHPUnit2_Framework_Test $test) {
        if ($test instanceof PHPUnit2_Framework_TestCase) {
            $this->codeCoverageInformation[$test->getName()] = $test->getCodeCoverageInformation();
        }

        foreach ($this->listeners as $listener) {
            $listener->endTest($test);
        }
    }

    // }}}
    // {{{ public function allCompletlyImplemented()

    /**
    * Returns TRUE if no incomplete test occured.
    *
    * @return boolean
    * @access public
    */
    public function allCompletlyImplemented() {
        return $this->notImplementedCount() == 0;
    }

    // }}}
    // {{{ public function notImplementedCount()

    /**
    * Gets the number of incomplete tests.
    *
    * @return integer
    * @access public
    */
    public function notImplementedCount() {
        return sizeof($this->notImplemented);
    }

    // }}}
    // {{{ public function notImplemented)

    /**
    * Returns an Enumeration for the incomplete tests.
    *
    * @return array
    * @access public
    */
    public function notImplemented() {
        return $this->notImplemented;
    }

    // }}}
    // {{{ public function errorCount()

    /**
    * Gets the number of detected errors.
    *
    * @return integer
    * @access public
    */
    public function errorCount() {
        return sizeof($this->errors);
    }

    // }}}
    // {{{ public function errors()

    /**
    * Returns an Enumeration for the errors.
    *
    * @return array
    * @access public
    */
    public function errors() {
        return $this->errors;
    }

    // }}}
    // {{{ public function failureCount()

    /**
    * Gets the number of detected failures.
    *
    * @return integer
    * @access public
    */
    public function failureCount() {
        return sizeof($this->failures);
    }

    // }}}
    // {{{ public function failures()

    /**
    * Returns an Enumeration for the failures.
    *
    * @return array
    * @access public
    */
    public function failures() {
        return $this->failures;
    }

    // }}}
    // {{{ public function getCodeCoverageInformation()

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

    // }}}
    // {{{ public function run(PHPUnit2_Framework_Test $test)

    /**
    * Runs a TestCase.
    *
    * @param  PHPUnit2_Framework_Test $test
    * @access public
    */
    public function run(PHPUnit2_Framework_Test $test) {
        $this->startTest($test);

        set_error_handler(array($this, 'errorHandler'));

        try {
            $test->runBare();
        }

        catch (PHPUnit2_Framework_AssertionFailedError $e) {
            $this->addFailure($test, $e);
        }

        catch (Exception $e) {
            $this->addError($test, $e);
        }

        restore_error_handler();

        $this->endTest($test);
    }

    // }}}
    // {{{ public function runCount()

    /**
    * Gets the number of run tests.
    *
    * @return integer
    * @access public
    */
    public function runCount() {
        return $this->runTests;
    }

    // }}}
    // {{{ public function shouldStop()

    /**
    * Checks whether the test run should stop.
    *
    * @return boolean
    * @access public
    */
    public function shouldStop() {
        return $this->stop;
    }

    // }}}
    // {{{ public function stop()

    /**
    * Marks that the test run should stop.
    *
    * @access public
    */
    public function stop() {
        $this->stop = TRUE;
    }

    // }}}
    // {{{ public function wasSuccessful()

    /**
    * Returns whether the entire test was successful or not.
    *
    * @return boolean
    * @access public
    */
    public function wasSuccessful() {
        if (empty($this->errors) && empty($this->failures)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    // }}}
    // {{{ public function errorHandler($errno, $errstr, $errfile, $errline)

    /**
    * @param  integer $errno
    * @param  string  $errstr
    * @param  string  $errfile
    * @param  integer $errline
    * @access public
    * @since  2.2.0
    */
    public function errorHandler($errno, $errstr, $errfile, $errline) {
        if ($errno == E_ERROR || $errno == E_USER_ERROR) {
            $trace = debug_backtrace();
            array_shift($trace);

            throw new PHPUnit2_Framework_Error(
              $errstr,
              $errno,
              $errfile,
              $errline,
              $trace
            );
        }
    }

    // }}}
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
