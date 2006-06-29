<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHPUnit
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
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 3.0.0
 */

require_once 'PHPUnit2/Framework.php';
require_once 'PHPUnit2/Util/Filter.php';
require_once 'PHPUnit2/Util/Printer.php';
require_once 'PHPUnit2/Util/Timer.php';

PHPUnit2_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * A TestListener that passes a log of the test execution as a JSON string
 * to Eclipse via a socket connection.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Steven Balthazor <stevenbalthazor@gmail.com>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      Class available since Release 3.0.0
 */
class PHPUnit2_Util_Log_Eclipse extends PHPUnit2_Util_Printer implements PHPUnit2_Framework_TestListener
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
    private $currentTestCaseName = '';

    /**
     * @var    string
     * @access private
     */
    private $currentTestMethodName = '';

    /**
     * @var     boolean
     * @access  private
     */
    private $currentTestCasePass = TRUE;

    /**
     * Constructor.
     *
     * @param  mixed $out
     * @access public
     */
    public function __construct($out = NULL)
    {
        $this->out = @fsockopen('127.0.0.1', $out, $errnum, $error, 10);

        if (!$this->out){
            throw new RuntimeException(
              sprintf(
                'Error opening socket %s : %s (%s)',

                $out,
                $error,
                $errnum
              )
            );
        }
    }

    /**
     * @param  string $buffer
     * @access public
     */
    public function write($buffer)
    {
        if ($this->out !== NULL) {
            fwrite($this->out, $buffer);
        }
    }

    /**
     * @param string $status
     * @param string $message
     * @access private
     */
    private function writeCase($status, $message = '')
    {
        $this->write(
          sprintf(
            '{status:"%s",message:"%s",group:"%s",case:"%s",method:"%s"}',

            $status,
            $this->escapeValue($message),
            $this->currentTestSuiteName,
            $this->currentTestCaseName,
            $this->currentTestMethodName
          )
        );
    }

    /**
     * @param  string $value
     * @return string
     * @access private
     */
    private function escapeValue($value)
    {
        return str_replace(
          array("\\","\"","/","\b","\f","\n","\r","\t"),
          array('\\\\','\"','\/','\b','\f','\n','\r','\t'),
          $value
        );
    }

    /**
     * An error occurred.
     *
     * @param  PHPUnit2_Framework_Test $test
     * @param  Exception               $e
     * @access public
     */
    public function addError(PHPUnit2_Framework_Test $test, Exception $e)
    {
        $this->writeCase(
          'error',
          sprintf(
            '%s:%s',
            get_class($e),
            $e->getMessage()
          )
        );

        $this->currentTestCasePass = FALSE;
    }

    /**
     * A failure occurred.
     *
     * @param  PHPUnit2_Framework_Test                 $test
     * @param  PHPUnit2_Framework_AssertionFailedError $e
     * @access public
     */
    public function addFailure(PHPUnit2_Framework_Test $test, PHPUnit2_Framework_AssertionFailedError $e)
    {
        $location = $e->getLocation();

        $this->writeCase(
          'fail',
          sprintf(
            '%s[%s line %s]',

            $e->getMessage(),
            $location['file'],
            $location['line']
          )
        );

        $this->currentTestCasePass = FALSE;
    }

    /**
     * Incomplete test.
     *
     * @param  PHPUnit2_Framework_Test $test
     * @param  Exception               $e
     * @access public
     */
    public function addIncompleteTest(PHPUnit2_Framework_Test $test, Exception $e)
    {
        $this->writeCase('error', 'Incomplete Test');

        $this->currentTestCasePass = FALSE;
    }

    /**
     * Skipped test.
     *
     * @param  PHPUnit2_Framework_Test $test
     * @param  Exception               $e
     * @access public
     */
    public function addSkippedTest(PHPUnit2_Framework_Test $test, Exception $e)
    {
        $this->writeCase('error', 'Skipped Test');

        $this->currentTestCasePass = FALSE;
    }

    /**
     * A testsuite started.
     *
     * @param  PHPUnit2_Framework_TestSuite $suite
     * @access public
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(PHPUnit2_Framework_TestSuite $suite)
    {
        $this->currentTestSuiteName  = $this->escapeValue($suite->getName());
        $this->currentTestCaseName   = '';
        $this->currentTestMethodName = '';
    }

    /**
     * A testsuite ended.
     *
     * @param  PHPUnit2_Framework_TestSuite $suite
     * @access public
     * @since  Method available since Release 2.2.0
     */
    public function endTestSuite(PHPUnit2_Framework_TestSuite $suite)
    {
        $this->currentTestCaseName   = '';
        $this->currentTestMethodName = '';
        $this->currentTestSuiteName  = '';
    }

    /**
     * A test started.
     *
     * @param  PHPUnit2_Framework_Test $test
     * @access public
     */
    public function startTest(PHPUnit2_Framework_Test $test)
    {
        $this->currentTestCaseName   = $this->escapeValue(get_class($test));
        $this->currentTestMethodName = $this->escapeValue($test->getName());
        $this->currentTestCasePass   = TRUE;
    }

    /**
     * A test ended.
     *
     * @param  PHPUnit2_Framework_Test $test
     * @access public
     */
    public function endTest(PHPUnit2_Framework_Test $test)
    {
        if ($this->currentTestCasePass) {
            $this->writeCase('pass');
        }
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
