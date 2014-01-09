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
 * @subpackage Util_Log
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

/**
 * A TestListener that generates a logfile of the
 * test execution using the Test Anything Protocol (TAP).
 *
 * @package    PHPUnit
 * @subpackage Util_Log
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class PHPUnit_Util_Log_TAP extends PHPUnit_Util_Printer implements PHPUnit_Framework_TestListener
{
    /**
     * @var    integer
     */
    protected $testNumber = 0;

    /**
     * @var    integer
     */
    protected $testSuiteLevel = 0;

    /**
     * @var    boolean
     */
    protected $testSuccessful = TRUE;

    /**
     * Constructor.
     *
     * @param  mixed $out
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.3.4
     */
    public function __construct($out = NULL)
    {
        parent::__construct($out);
        $this->write("TAP version 13\n");
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
        $this->writeNotOk($test, 'Error');
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
        $this->writeNotOk($test, 'Failure');

        $message = explode(
          "\n", PHPUnit_Framework_TestFailure::exceptionToString($e)
        );

        $diagnostic = array(
          'message'  => $message[0],
          'severity' => 'fail'
        );

        if ($e instanceof PHPUnit_Framework_ExpectationFailedException) {
            $cf = $e->getComparisonFailure();

            if ($cf !== NULL) {
                $diagnostic['data'] = array(
                  'got'      => $cf->getActual(),
                  'expected' => $cf->getExpected()
                );
            }
        }

        $yaml = new Symfony\Component\Yaml\Dumper;

        $this->write(
          sprintf(
            "  ---\n%s  ...\n",
            $yaml->dump($diagnostic, 2, 2)
          )
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
        $this->writeNotOk($test, '', 'TODO Incomplete Test');
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
        $this->write(
          sprintf(
            "ok %d - # SKIP%s\n",

            $this->testNumber,
            $e->getMessage() != '' ? ' ' . $e->getMessage() : ''
          )
        );

        $this->testSuccessful = FALSE;
    }

    /**
     * A testsuite started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->testSuiteLevel++;
    }

    /**
     * A testsuite ended.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->testSuiteLevel--;

        if ($this->testSuiteLevel == 0) {
            $this->write(sprintf("1..%d\n", $this->testNumber));
        }
    }

    /**
     * A test started.
     *
     * @param  PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        $this->testNumber++;
        $this->testSuccessful = TRUE;
    }

    /**
     * A test ended.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  float                  $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        if ($this->testSuccessful === TRUE) {
            $this->write(
              sprintf(
                "ok %d - %s\n",

                $this->testNumber,
                PHPUnit_Util_Test::describe($test)
              )
            );
        }
    }

    /**
     * @param  PHPUnit_Framework_Test $test
     * @param  string                 $prefix
     * @param  string                 $directive
     */
    protected function writeNotOk(PHPUnit_Framework_Test $test, $prefix = '', $directive = '')
    {
        $this->write(
          sprintf(
            "not ok %d - %s%s%s\n",

            $this->testNumber,
            $prefix != '' ? $prefix . ': ' : '',
            PHPUnit_Util_Test::describe($test),
            $directive != '' ? ' # ' . $directive : ''
          )
        );

        $this->testSuccessful = FALSE;
    }
}
