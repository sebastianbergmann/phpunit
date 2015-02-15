<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2007, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://pear.php.net/package/PHPUnit
 * @since      File available since Release 1.0.0
 */

require_once 'PHPUnit/Assert.php';
require_once 'PHPUnit/TestResult.php';

/**
 * A TestCase defines the fixture to run multiple tests.
 *
 * To define a TestCase
 *
 *   1) Implement a subclass of PHPUnit_TestCase.
 *   2) Define instance variables that store the state of the fixture.
 *   3) Initialize the fixture state by overriding setUp().
 *   4) Clean-up after a test by overriding tearDown().
 *
 * Each test runs in its own fixture so there can be no side effects
 * among test runs.
 *
 * Here is an example:
 *
 * <code>
 * <?php
 * class MathTest extends PHPUnit_TestCase {
 *     var $fValue1;
 *     var $fValue2;
 *
 *     function MathTest($name) {
 *         $this->PHPUnit_TestCase($name);
 *     }
 *
 *     function setUp() {
 *         $this->fValue1 = 2;
 *         $this->fValue2 = 3;
 *     }
 * }
 * ?>
 * </code>
 *
 * For each test implement a method which interacts with the fixture.
 * Verify the expected results with assertions specified by calling
 * assert with a boolean.
 *
 * <code>
 * <?php
 * function testPass() {
 *     $this->assertTrue($this->fValue1 + $this->fValue2 == 5);
 * }
 * ?>
 * </code>
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PHPUnit
 * @since      Class available since Release 1.0.0
 */
class PHPUnit_TestCase extends PHPUnit_Assert {
    /**
     * @var    boolean
     * @access private
     */
    var $_failed = FALSE;

    /**
     * The name of the test case.
     *
     * @var    string
     * @access private
     */
    var $_name = '';

    /**
     * PHPUnit_TestResult object
     *
     * @var    object
     * @access private
     */
    var $_result;

    /**
     * Constructs a test case with the given name.
     *
     * @param  string
     * @access public
     */
    function PHPUnit_TestCase($name = FALSE) {
        if ($name !== FALSE) {
            $this->setName($name);
        }
    }

    /**
     * Counts the number of test cases executed by run(TestResult result).
     *
     * @return integer
     * @access public
     */
    function countTestCases() {
        return 1;
    }

    /**
     * Gets the name of a TestCase.
     *
     * @return string
     * @access public
     */
    function getName() {
        return $this->_name;
    }

    /**
     * Runs the test case and collects the results in a given TestResult object.
     *
     * @param  object
     * @return object
     * @access public
     */
    function run(&$result) {
        $this->_result = &$result;
        $this->_result->run($this);

        return $this->_result;
    }

    /**
     * Runs the bare test sequence.
     *
     * @access public
     */
    function runBare() {
        $this->setUp();
        $this->runTest();
        $this->tearDown();
        $this->pass();
    }

    /**
     * Override to run the test and assert its state.
     *
     * @access protected
     */
    function runTest() {
        call_user_func(
          array(
            &$this,
            $this->_name
          )
        );
    }

    /**
     * Sets the name of a TestCase.
     *
     * @param  string
     * @access public
     */
    function setName($name) {
        $this->_name = $name;
    }

    /**
     * Returns a string representation of the test case.
     *
     * @return string
     * @access public
     */
    function toString() {
        return '';
    }

    /**
     * Creates a default TestResult object.
     *
     * @return object
     * @access protected
     */
    function &createResult() {
        return new PHPUnit_TestResult;
    }

    /**
     * Fails a test with the given message.
     *
     * @param  string
     * @access protected
     */
    function fail($message = '') {
        if (function_exists('debug_backtrace')) {
            $trace = debug_backtrace();

            if (isset($trace['1']['file'])) {
                $message = sprintf(
                  "%s in %s:%s",

                  $message,
                  $trace['1']['file'],
                  $trace['1']['line']
                );
            }
        }

        $this->_result->addFailure($this, $message);
        $this->_failed = TRUE;
    }

    /**
     * Passes a test.
     *
     * @access protected
     */
    function pass() {
        if (!$this->_failed) {
            $this->_result->addPassedTest($this);
        }
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     * @abstract
     */
    function setUp() { /* abstract */ }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     * @abstract
     */
    function tearDown() { /* abstract */ }
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
?>
