<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2013, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @subpackage Util_Log
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

/**
 * A TestListener that generates JSON messages.
 *
 * @package    PHPUnit
 * @subpackage Util_Log
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class PHPUnit_Util_Log_JSON extends PHPUnit_Util_Printer implements PHPUnit_Framework_TestListener
{
    /**
     * @var    string
     */
    protected $currentTestSuiteName = '';

    /**
     * @var    string
     */
    protected $currentTestName = '';

    /**
     * @var     boolean
     * @access  private
     */
    protected $currentTestPass = TRUE;

    /**
     * An error occurred.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->writeCase(
          'error',
          $time,
          PHPUnit_Util_Filter::getFilteredStacktrace($e, FALSE),
          $e->getMessage(),
          $test
        );

        $this->currentTestPass = FALSE;
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
        $this->writeCase(
          'fail',
          $time,
          PHPUnit_Util_Filter::getFilteredStacktrace($e, FALSE),
          $e->getMessage(),
          $test
        );

        $this->currentTestPass = FALSE;
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
        $this->writeCase(
          'error',
          $time,
          PHPUnit_Util_Filter::getFilteredStacktrace($e, FALSE),
          'Incomplete Test: ' . $e->getMessage(),
          $test
        );

        $this->currentTestPass = FALSE;
    }

    /**
     * Skipped test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->writeCase(
          'error',
          $time,
          PHPUnit_Util_Filter::getFilteredStacktrace($e, FALSE),
          'Skipped Test: ' . $e->getMessage(),
          $test
        );

        $this->currentTestPass = FALSE;
    }

    /**
     * A testsuite started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->currentTestSuiteName = $suite->getName();
        $this->currentTestName      = '';

        $this->write(
          array(
            'event' => 'suiteStart',
            'suite' => $this->currentTestSuiteName,
            'tests' => count($suite)
          )
        );
    }

    /**
     * A testsuite ended.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
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
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        $this->currentTestName = PHPUnit_Util_Test::describe($test);
        $this->currentTestPass = TRUE;

        $this->write(
          array(
            'event' => 'testStart',
            'suite' => $this->currentTestSuiteName,
            'test'  => $this->currentTestName
          )
        );
    }

    /**
     * A test ended.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  float                  $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        if ($this->currentTestPass) {
            $this->writeCase('pass', $time, array(), '', $test);
        }
    }

    /**
     * @param string $status
     * @param float  $time
     * @param array  $trace
     * @param string $message
     */
    protected function writeCase($status, $time, array $trace = array(), $message = '', $test = NULL)
    {
        $output = '';
        if ($test !== NULL && $test->hasOutput()) {
            $output = $test->getActualOutput();
        }
        $this->write(
          array(
            'event'   => 'test',
            'suite'   => $this->currentTestSuiteName,
            'test'    => $this->currentTestName,
            'status'  => $status,
            'time'    => $time,
            'trace'   => $trace,
            'message' => PHPUnit_Util_String::convertToUtf8($message),
            'output'  => $output,
          )
        );
    }

    /**
     * @param string $buffer
     */
    public function write($buffer)
    {
        array_walk_recursive($buffer, function(&$input) {
            if (is_string($input)) {
                $input = PHPUnit_Util_String::convertToUtf8($input);
            }
        });

        parent::write(json_encode($buffer, JSON_PRETTY_PRINT));
    }
}
