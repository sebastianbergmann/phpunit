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
 * A TestListener that generates a logfile of the
 * test execution using the Test Anything Protocol (TAP).
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
class PHPUnit_Util_Log_TAP extends PHPUnit_Util_Printer implements PHPUnit_Framework_TestListener
{
    /**
     * @var    integer
     * @access private
     */
    private $testNumber = 0;

    /**
     * @var    boolean
     * @access private
     */
    private $testSuccessful = TRUE;

    /**
     * Flush buffer and close output.
     *
     * @access public
     */
    public function flush()
    {
        $this->write(
          sprintf(
            "1..%s\n",

            $this->testNumber
          )
        );

        parent::flush();
    }

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
        $this->writeNotOk($test, 'Error');
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
        $this->writeNotOk($test, 'Failure');
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
        $this->writeNotOk($test, '', 'TODO Incomplete Test');
    }

    /**
     * Skipped test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->write(
          sprintf(
            "ok %s - # SKIP%s\n",

            $this->testNumber,
            $e->getMessage() != '' ? ' ' . $e->getMessage() : ''
          )
        );
    }

    /**
     * A testsuite started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @access public
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->write(
          sprintf(
            "# TestSuite \"%s\" started.\n",

            $suite->getName()
          )
        );
    }

    /**
     * A testsuite ended.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @access public
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->write(
          sprintf(
            "# TestSuite \"%s\" ended.\n",

            $suite->getName()
          )
        );
    }

    /**
     * A test started.
     *
     * @param  PHPUnit_Framework_Test $test
     * @access public
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
     * @access public
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        if ($this->testSuccessful === TRUE) {
            $this->write(
              sprintf(
                "ok %s - %s\n",

                $this->testNumber,
                PHPUnit_Util_Test::describe($test)
              )
            );
        }
    }

    /**
     * @param  PHPUnit_Framework_Test $test
     * @param  string                  $prefix
     * @param  string                  $directive
     * @access private
     */
    private function writeNotOk(PHPUnit_Framework_Test $test, $prefix = '', $directive = '')
    {
        $this->write(
          sprintf(
            "not ok %s - %s%s%s\n",

            $this->testNumber,
            $prefix != '' ? $prefix . ': ' : '',
            PHPUnit_Util_Test::describe($test),
            $directive != '' ? ' # ' . $directive : ''
          )
        );

        $this->testSuccessful = FALSE;
    }
}
?>
