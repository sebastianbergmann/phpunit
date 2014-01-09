<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2014, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @author     Ralph Schindler <ralph.schindler@zend.com>
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.5.7
 */

/**
 * Test Listener that tracks the usage of deprecated features.
 *
 * @package    PHPUnit
 * @subpackage Framework
 * @author     Ralph Schindler <ralph.schindler@zend.com>
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.5.7
 */
class PHPUnit_Util_DeprecatedFeature_Logger implements PHPUnit_Framework_TestListener
{
    /**
     * @var PHPUnit_Framework_TestCase
     */
    protected static $currentTest = NULL;

    /**
     * This is the publically accessible API for notifying the system that a
     * deprecated feature has been used.
     *
     * If it is run via a TestRunner and the test extends
     * PHPUnit_Framework_TestCase, then this will inject the result into the
     * test runner for display, if not, it will throw the notice to STDERR.
     *
     * @param string $message
     * @param int|bool $backtraceDepth
     */
    public static function log($message, $backtraceDepth = 2)
    {
        if ($backtraceDepth !== FALSE) {
            $trace = debug_backtrace(FALSE);

            if (is_int($backtraceDepth)) {
                $traceItem = $trace[$backtraceDepth];
            }

            if (!isset($traceItem['file'])) {
                $reflectionClass   = new ReflectionClass($traceItem['class']);
                $traceItem['file'] = $reflectionClass->getFileName();
            }

            if (!isset($traceItem['line']) &&
                 isset($traceItem['class']) &&
                 isset($traceItem['function'])) {
                if (!isset($reflectionClass)) {
                    $reflectionClass = new ReflectionClass($traceItem['class']);
                }

                $method = $reflectionClass->getMethod($traceItem['function']);
                $traceItem['line'] = '(between ' . $method->getStartLine() .
                                     ' and ' . $method->getEndLine() . ')';
            }
        }

        $deprecatedFeature = new PHPUnit_Util_DeprecatedFeature(
          $message, $traceItem
        );

        if (self::$currentTest instanceof PHPUnit_Framework_TestCase) {
            $result = self::$currentTest->getTestResultObject();
            $result->addDeprecatedFeature($deprecatedFeature);
        } else {
            file_put_contents('php://stderr', $deprecatedFeature);
        }
    }

    /**
     * An error occurred.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
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
    }

    /**
     * A test suite started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    }

    /**
     * A test suite ended.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    }

    /**
     * A test started.
     *
     * @param  PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        self::$currentTest = $test;
    }

    /**
     * A test ended.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  float                  $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        self::$currentTest = NULL;
    }
}
