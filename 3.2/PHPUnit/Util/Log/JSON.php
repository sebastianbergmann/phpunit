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
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/Printer.php';
require_once 'PHPUnit/Util/Test.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * A TestListener that generates JSON messages.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class PHPUnit_Util_Log_JSON extends PHPUnit_Util_Printer implements PHPUnit_Framework_TestListener
{
    /**
     * @var    string
     * @access private
     */
    private $currentTestSuiteName = '';

    /**
     * @var    string
     * @access private
     */
    private $currentTestName = '';

    /**
     * @var     boolean
     * @access  private
     */
    private $currentTestPass = TRUE;

    /**
     * An error occurred.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     * @access public
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->writeCase(
          'error',
          $time,
          PHPUnit_Util_Filter::getFilteredStacktrace(
            $e,
            TRUE,
            TRUE,
            FALSE
          ),
          $e->getMessage()
        );

        $this->currentTestPass = FALSE;
    }

    /**
     * A failure occurred.
     *
     * @param  PHPUnit_Framework_Test                 $test
     * @param  PHPUnit_Framework_AssertionFailedError $e
     * @param  float                                  $time
     * @access public
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->writeCase(
          'fail',
          $time,
          PHPUnit_Util_Filter::getFilteredStacktrace(
            $e,
            TRUE,
            TRUE,
            FALSE
          ),
          $e->getMessage()
        );

        $this->currentTestPass = FALSE;
    }

    /**
     * Incomplete test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     * @access public
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->writeCase('error', $time, 'Incomplete Test');

        $this->currentTestPass = FALSE;
    }

    /**
     * Skipped test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     * @access public
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->writeCase('error', $time, 'Skipped Test');

        $this->currentTestPass = FALSE;
    }

    /**
     * A testsuite started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @access public
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->currentTestSuiteName = $suite->getName();
        $this->currentTestName      = '';

        $message = array(
          'event' => 'suiteStart',
          'suite' => $this->currentTestSuiteName,
          'tests' => count($suite)
        );

        $this->write($this->encode($message));
    }

    /**
     * A testsuite ended.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @access public
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->currentTestSuiteName = '';
        $this->currentTestName      = '';
    }

    /**
     * A test started.
     *
     * @param  PHPUnit_Framework_Test $test
     * @access public
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        $this->currentTestName = PHPUnit_Util_Test::describe($test);
        $this->currentTestPass = TRUE;
    }

    /**
     * A test ended.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  float                  $time
     * @access public
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        if ($this->currentTestPass) {
            $this->writeCase('pass', $time);
        }
    }

    /**
     * @param string $status
     * @param float  $time
     * @param array  $trace
     * @param string $message
     * @access private
     */
    private function writeCase($status, $time, array $trace = array(), $message = '')
    {
        $message = array(
          'event'   => 'test',
          'suite'   => $this->currentTestSuiteName,
          'test'    => $this->currentTestName,
          'status'  => $status,
          'time'    => $time,
          'trace'   => $trace,
          'message' => $message
        );

        $this->write($this->encode($message));
    }

    /**
     * @param  array $message
     * @return string
     * @access private
     */
    private function encode($message)
    {
        if (function_exists('json_encode')) {
            return json_encode($message);
        }

        $first  = TRUE;
        $result = '';

        if (is_scalar($message)) {
            $message = array ($message);
        }

        foreach ($message as $key => $value) {
            if (!$first) {
                $result .= ',';
            } else {
                $first = FALSE;
            }

            $result .= sprintf('"%s":', $this->escape($key));

            if (is_array($value) || is_object($value)) {
                $result .= sprintf('%s', $this->encode($value));
            } else {
                $result .= sprintf('"%s"', $this->escape($value));
            }
        }

        return '{' . $result . '}';
    }

    /**
     * @param  string $value
     * @return string
     * @access private
     */
    private function escape($value)
    {
        return str_replace(
          array("\\",   "\"", "/",  "\b", "\f", "\n", "\r", "\t"),
          array('\\\\', '\"', '\/', '\b', '\f', '\n', '\r', '\t'),
          $value
        );
    }
}
?>
