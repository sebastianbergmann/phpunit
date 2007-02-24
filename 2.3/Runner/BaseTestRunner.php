<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHP Version 5
 *
 * Copyright (c) 2002-2006, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    CVS: $Id: BaseTestRunner.php,v 1.18.2.3 2005/12/17 16:04:56 sebastian Exp $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit2/Framework/AssertionFailedError.php';
require_once 'PHPUnit2/Framework/TestListener.php';
require_once 'PHPUnit2/Runner/StandardTestSuiteLoader.php';

/**
 * Base class for all test runners.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      Class available since Release 2.0.0
 * @abstract
 */
abstract class PHPUnit2_Runner_BaseTestRunner implements PHPUnit2_Framework_TestListener {
    const STATUS_ERROR      = 1;
    const STATUS_FAILURE    = 2;
    const STATUS_INCOMPLETE = 3;
    const SUITE_METHODNAME  = 'suite';

    /**
     * An error occurred.
     *
     * @param  PHPUnit2_Framework_Test $test
     * @param  Exception               $e
     * @access public
     */
    public function addError(PHPUnit2_Framework_Test $test, Exception $e) {
        $this->testFailed(self::STATUS_ERROR, $test, $e);
    }

    /**
     * A failure occurred.
     *
     * @param  PHPUnit2_Framework_Test                 $test
     * @param  PHPUnit2_Framework_AssertionFailedError $e
     * @access public
     */
    public function addFailure(PHPUnit2_Framework_Test $test, PHPUnit2_Framework_AssertionFailedError $e) {
        $this->testFailed(self::STATUS_FAILURE, $test, $e);
    }

    /**
     * Incomplete test.
     *
     * @param  PHPUnit2_Framework_Test $test
     * @param  Exception               $e
     * @access public
     */
    public function addIncompleteTest(PHPUnit2_Framework_Test $test, Exception $e) {
        $this->testFailed(self::STATUS_INCOMPLETE, $test, $e);
    }

    /**
     * A testsuite started.
     *
     * @param  PHPUnit2_Framework_TestSuite $suite
     * @access public
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(PHPUnit2_Framework_TestSuite $suite) {
    }

    /**
     * A testsuite ended.
     *
     * @param  PHPUnit2_Framework_TestSuite $suite
     * @access public
     * @since  Method available since Release 2.2.0
     */
    public function endTestSuite(PHPUnit2_Framework_TestSuite $suite) {
    }

    /**
     * A test started.
     *
     * @param  PHPUnit2_Framework_Test  $test
     * @access public
     */
    public function startTest(PHPUnit2_Framework_Test $test) {
        $this->testStarted($test->getName());
    }

    /**
     * A test ended.
     *
     * @param  PHPUnit2_Framework_Test  $test
     * @access public
     */
    public function endTest(PHPUnit2_Framework_Test $test) {
        $this->testEnded($test->getName());
    }

    /**
     * Returns the loader to be used.
     *
     * @return PHPUnit2_Runner_TestSuiteLoader
     * @access public
     */
    public function getLoader() {
        return new PHPUnit2_Runner_StandardTestSuiteLoader;
    }

    /**
     * Returns the Test corresponding to the given suite.
     * This is a template method, subclasses override
     * the runFailed() and clearStatus() methods.
     *
     * @param  string  $suiteClassName
     * @param  string  $suiteClassFile
     * @return PHPUnit2_Framework_Test
     * @access public
     */
    public function getTest($suiteClassName, $suiteClassFile = '') {
        if ($suiteClassFile == $suiteClassName . '.php') {
            $suiteClassFile = '';
        }

        try {
            $testClass = $this->loadSuiteClass($suiteClassName, $suiteClassFile);
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

    /**
     * Override to define how to handle a failed loading of
     * a test suite.
     *
     * @param  string  $message
     * @access protected
     * @abstract
     */
    protected abstract function runFailed($message);

    /**
     * Returns the loaded ReflectionClass for a suite name.
     *
     * @param  string  $suiteClassName
     * @param  string  $suiteClassFile
     * @return ReflectionClass
     * @access protected
     */
    protected function loadSuiteClass($suiteClassName, $suiteClassFile = '') {
        return $this->getLoader()->load($suiteClassName, $suiteClassFile);
    }

    /**
     * Clears the status message.
     *
     * @access protected
     */
    protected function clearStatus() {
    }

    /**
     * A test started.
     *
     * @param  string  $testName
     * @access public
     * @abstract
     */
    public abstract function testStarted($testName);

    /**
     * A test ended.
     *
     * @param  string  $testName
     * @access public
     * @abstract
     */
    public abstract function testEnded($testName);

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
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
?>
