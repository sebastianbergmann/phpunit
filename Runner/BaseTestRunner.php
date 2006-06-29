<?php
//
// +------------------------------------------------------------------------+
// | PEAR :: PHPUnit2                                                       |
// +------------------------------------------------------------------------+
// | Copyright (c) 2002-2004 Sebastian Bergmann <sb@sebastian-bergmann.de>. |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
//
// $Id: BaseTestRunner.php 539 2006-02-13 16:08:42Z sb $
//

require_once 'PHPUnit2/Framework/AssertionFailedError.php';
require_once 'PHPUnit2/Framework/TestListener.php';
require_once 'PHPUnit2/Framework/TestSuite.php';
require_once 'PHPUnit2/Runner/StandardTestSuiteLoader.php';
require_once 'PHPUnit2/Runner/TestRunListener.php';

/**
 * Base class for all test runners.
 *
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2004 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2
 * @subpackage  Runner
 * @abstract
 */
abstract class PHPUnit2_Runner_BaseTestRunner implements PHPUnit2_Framework_TestListener {
    // {{{ Constants

    const SUITE_METHODNAME = 'suite';

    // }}}
    // {{{ public function addError(PHPUnit2_Framework_Test $test, Exception $e)

    /**
    * An error occurred.
    *
    * @param  PHPUnit2_Framework_Test $test
    * @param  Exception               $e
    * @access public
    */
    public function addError(PHPUnit2_Framework_Test $test, Exception $e) {
        $this->testFailed(PHPUnit2_Runner_TestRunListener::STATUS_ERROR, $test, $e);
    }

    // }}}
    // {{{ public function addFailure(PHPUnit2_Framework_Test $test, PHPUnit2_Framework_AssertionFailedError $e)

    /**
    * A failure occurred.
    *
    * @param  PHPUnit2_Framework_Test                 $test
    * @param  PHPUnit2_Framework_AssertionFailedError $e
    * @access public
    */
    public function addFailure(PHPUnit2_Framework_Test $test, PHPUnit2_Framework_AssertionFailedError $e) {
        $this->testFailed(PHPUnit2_Runner_TestRunListener::STATUS_FAILURE, $test, $e);
    }

    // }}}
    // {{{ public function addIncompleteTest(PHPUnit2_Framework_Test $test, Exception $e)

    /**
    * Incomplete test.
    *
    * @param  PHPUnit2_Framework_Test $test
    * @param  Exception               $e
    * @access public
    */
    public function addIncompleteTest(PHPUnit2_Framework_Test $test, Exception $e) {
        $this->testFailed(PHPUnit2_Runner_TestRunListener::STATUS_INCOMPLETE, $test, $e);
    }

    // }}}
    // {{{ public function endTest(PHPUnit2_Framework_Test $test)

    /**
    * A test ended.
    *
    * @param  PHPUnit2_Framework_Test  $test
    * @access public
    */
    public function endTest(PHPUnit2_Framework_Test $test) {
        $this->testEnded($test->getName());
    }

    // }}}
    // {{{ public function getLoader()

    /**
    * Returns the loader to be used.
    *
    * @return PHPUnit2_Runner_TestSuiteLoader
    * @access protected
    */
    public function getLoader() {
        return new PHPUnit2_Runner_StandardTestSuiteLoader;
    }

    // }}}
    // {{{ public function getTest($suiteClassName)

    /**
    * Returns the Test corresponding to the given suite.
    * This is a template method, subclasses override
    * the runFailed() and clearStatus() methods.
    *
    * @param  string  $suiteClassName
    * @return PHPUnit2_Framework_Test
    * @access public
    */
    public function getTest($suiteClassName) {
        try {
            $testClass = $this->loadSuiteClass($suiteClassName);
        }

        catch (Exception $e) {
            $this->runFailed($e->getMessage());
            return NULL;
        }

        try {
            $suiteMethod = $testClass->getMethod(self::SUITE_METHODNAME);

            if (!$suiteMethod->isStatic()) {
                $this->runFailed(
                  'suite() method must be static.'
                );

                return NULL;
            }

            try {
                $test = $suiteMethod->invoke(NULL);
            }

            catch (ReflectionException $e) {
                $this->runFailed(
                  sprintf(
                    "Failed to invoke suite() method.\n%s",

                    $e->getMessage()
                  )
                );

                return NULL;
            }
        }

        catch (ReflectionException $e) {
            $test = new PHPUnit2_Framework_TestSuite($testClass);
        }

        $this->clearStatus();

        return $test;
    }

    // }}}
    // {{{ public function startTest(PHPUnit2_Framework_Test $test)

    /**
    * A test started.
    *
    * @param  PHPUnit2_Framework_Test  $test
    * @access public
    */
    public function startTest(PHPUnit2_Framework_Test $test) {
        $this->testStarted($test->getName());
    }

    // }}}
    // {{{ public abstract function testEnded($testName)

    /**
    * A test ended.
    *
    * @param  string  $testName
    * @access public
    * @abstract
    */
    public abstract function testEnded($testName);

    // }}}
    // {{{ public abstract function testFailed($status, PHPUnit2_Framework_Test $test, PHPUnit2_Framework_AssertionFailedError $e)

    /**
    * A test failed.
    *
    * @param  integer                                 $status
    * @param  PHPUnit2_Framework_Test                 $test
    * @param  PHPUnit2_Framework_AssertionFailedError $e
    * @access public
    * @abstract
    */
    public abstract function testFailed($status, PHPUnit2_Framework_Test $test, PHPUnit2_Framework_AssertionFailedError $e);

    // }}}
    // {{{ public abstract function testStarted($testName)

    /**
    * A test started.
    *
    * @param  string  $testName
    * @access public
    * @abstract
    */
    public abstract function testStarted($testName);

    // }}}
    // {{{ protected abstract function runFailed($message)

    /**
    * Override to define how to handle a failed loading of
    * a test suite.
    *
    * @param  string  $message
    * @access protected
    * @abstract
    */
    protected abstract function runFailed($message);

    // }}}
    // {{{ protected function loadSuiteClass($suiteClassName)

    /**
    * Returns the loaded ReflectionClass for a suite name.
    *
    * @param  string  $suiteClassName
    * @return ReflectionClass
    * @access protected
    */
    protected function loadSuiteClass($suiteClassName) {
        return $this->getLoader()->load($suiteClassName);
    }

    // }}}
    // {{{ protected function clearStatus()

    /**
    * Clears the status message.
    *
    * @access protected
    */
    protected function clearStatus() {
    }

    // }}}
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
