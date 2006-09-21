<?php
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
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/Printer.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Prints the result of a TextUI TestRunner run.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.0.0
 */
class PHPUnit_TextUI_ResultPrinter extends PHPUnit_Util_Printer implements PHPUnit_Framework_TestListener
{
    /**
     * @var    integer
     * @access private
     */
    private $column = 0;

    /**
     * @var    boolean
     * @access private
     */
    private $lastTestFailed = FALSE;

    /**
     * @var    boolean
     * @access private
     */
    private $verbose = FALSE;

    /**
     * Constructor.
     *
     * @param  mixed   $out
     * @param  boolean $verbose
     * @throws InvalidArgumentException
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function __construct($out = NULL, $verbose = FALSE)
    {
        parent::__construct($out);

        if (is_bool($verbose)) {
            $this->verbose = $verbose;
        } else {
            throw new InvalidArgumentException;
        }
    }

    /**
     * @param  PHPUnit_Framework_TestResult $result
     * @param  float                        $timeElapsed
     * @access public
     */
    public function printResult(PHPUnit_Framework_TestResult $result, $timeElapsed)
    {
        $this->printHeader($timeElapsed);
        $this->printErrors($result);
        $this->printFailures($result);

        if ($this->verbose) {
            $this->printIncompletes($result);
            $this->printSkipped($result);
        }

        $this->printFooter($result);
    }

    /**
     * @param  array   $defects
     * @param  integer $count
     * @param  string  $type
     * @access protected
     */
    protected function printDefects(Array $defects, $count, $type)
    {
        if ($count == 0) {
            return;
        }

        $this->write(
          sprintf(
            "There %s %d %s%s:\n",

            ($count == 1) ? 'was' : 'were',
            $count,
            $type,
            ($count == 1) ? '' : 's'
          )
        );

        $i = 1;

        foreach ($defects as $defect) {
            $this->printDefect($defect, $i++);
        }
    }

    /**
     * @param  PHPUnit_Framework_TestFailure $defect
     * @param  integer                        $count
     * @access protected
     */
    protected function printDefect(PHPUnit_Framework_TestFailure $defect, $count)
    {
        $this->printDefectHeader($defect, $count);
        $this->printDefectTrace($defect);
    }

    /**
     * @param  PHPUnit_Framework_TestFailure $defect
     * @param  integer                        $count
     * @access protected
     */
    protected function printDefectHeader(PHPUnit_Framework_TestFailure $defect, $count)
    {
        $failedTest = $defect->failedTest();
        $message    = '';

        if ($failedTest instanceof PHPUnit_Framework_SelfDescribing) {
            $message = $failedTest->toString();
        }

        $this->write(
          sprintf(
            "\n%d) %s\n",

            $count,
            $message
          )
        );
    }

    /**
     * @param  PHPUnit_Framework_TestFailure $defect
     * @access protected
     */
    protected function printDefectTrace(PHPUnit_Framework_TestFailure $defect)
    {
        $e = $defect->thrownException();

        if ($e instanceof PHPUnit_Framework_SelfDescribing) {
            $this->write($e->toString() . "\n");

            if ($e instanceof PHPUnit_Framework_ExpectationFailedException) {
                $comparisonFailure = $e->getComparisonFailure();

                $string = '';

                if ($this->verbose ||
                    $comparisonFailure instanceof PHPUnit_Framework_ComparisonFailure_String) {
                    $string = $comparisonFailure->toString();
                }

                if (!$this->verbose &&
                    $comparisonFailure instanceof PHPUnit_Framework_ComparisonFailure_String &&
                    strpos($string, 'expected string <') !== FALSE) {
                    $string = '';
                }

                if (!empty($string)) {
                    $this->write($string . "\n");
                }
            }
        }

        else if ($e instanceof PHPUnit_Framework_Error) {
            $this->write($e->getMessage() . "\n");
        }

        else {
            $this->write(get_class($e) . ': ' . $e->getMessage() . "\n");
        }

        $this->write(
          PHPUnit_Util_Filter::getFilteredStacktrace(
            $defect->thrownException(),
            FALSE
          )
        );
    }

    /**
     * @param  PHPUnit_Framework_TestResult  $result
     * @access protected
     */
    protected function printErrors(PHPUnit_Framework_TestResult $result)
    {
        $this->printDefects($result->errors(), $result->errorCount(), 'error');
    }

    /**
     * @param  PHPUnit_Framework_TestResult  $result
     * @access protected
     */
    protected function printFailures(PHPUnit_Framework_TestResult $result)
    {
        $this->printDefects($result->failures(), $result->failureCount(), 'failure');
    }

    /**
     * @param  PHPUnit_Framework_TestResult  $result
     * @access protected
     */
    protected function printIncompletes(PHPUnit_Framework_TestResult $result)
    {
        $this->printDefects($result->notImplemented(), $result->notImplementedCount(), 'incomplete test');
    }

    /**
     * @param  PHPUnit_Framework_TestResult  $result
     * @access protected
     * @since  Method available since Release 3.0.0
     */
    protected function printSkipped(PHPUnit_Framework_TestResult $result)
    {
        $this->printDefects($result->skipped(), $result->skippedCount(), 'skipped test');
    }

    /**
     * @param  float   $timeElapsed
     * @access protected
     */
    protected function printHeader($timeElapsed)
    {
        $minutes = ($timeElapsed >= 60) ? floor($timeElapsed / 60) : 0;

        $this->write(
          sprintf(
            "\n\nTime: %02d:%02d\n\n",

            $minutes,
            $timeElapsed - $minutes * 60
          )
        );
    }

    /**
     * @param  PHPUnit_Framework_TestResult  $result
     * @access protected
     */
    protected function printFooter(PHPUnit_Framework_TestResult $result)
    {
        if ($result->wasSuccessful() &&
            $result->allCompletlyImplemented() &&
            $result->noneSkipped()) {
            $this->write(
              sprintf(
                "\nOK (%d test%s)\n",

                count($result),
                (count($result) == 1) ? '' : 's'
              )
            );
        }

        else if ((!$result->allCompletlyImplemented() ||
                  !$result->noneSkipped())&&
                 $result->wasSuccessful()) {
            $this->write(
              sprintf(
                "\nOK, but incomplete or skipped tests!\n" .
                "Tests: %d%s%s.\n",

                count($result),
                $this->getCountString($result->notImplementedCount(), 'Incomplete'),
                $this->getCountString($result->skippedCount(), 'Skipped')
              )
            );
        }

        else {
            $this->write(
              sprintf(
                "\nFAILURES!\n" .
                "Tests: %d%s%s%s%s.\n",

                count($result),
                $this->getCountString($result->failureCount(), 'Failures'),
                $this->getCountString($result->errorCount(), 'Errors'),
                $this->getCountString($result->notImplementedCount(), 'Incomplete'),
                $this->getCountString($result->skippedCount(), 'Skipped')
              )
            );
        }
    }

    /**
     * @param  integer $count
     * @param  string  $name
     * @return string
     * @access protected
     * @since  Method available since Release 3.0.0
     */
    protected function getCountString($count, $name)
    {
        $string = '';

        if ($count > 0) {
            $string = sprintf(
              ', %s: %d',

              $name,
              $count
            );
        }

        return $string;
    }

    /**
     * @access public
     */
    public function printWaitPrompt()
    {
        $this->write("\n<RETURN> to continue\n");
    }

    /**
     * An error occurred.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception               $e
     * @access public
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e)
    {
        $this->write('E');
        $this->nextColumn();

        $this->lastTestFailed = TRUE;
    }

    /**
     * A failure occurred.
     *
     * @param  PHPUnit_Framework_Test                 $test
     * @param  PHPUnit_Framework_AssertionFailedError $e
     * @access public
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e)
    {
        $this->write('F');
        $this->nextColumn();

        $this->lastTestFailed = TRUE;
    }

    /**
     * Incomplete test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception               $e
     * @access public
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e)
    {
        $this->write('I');
        $this->nextColumn();

        $this->lastTestFailed = TRUE;
    }

    /**
     * Skipped test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception               $e
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e)
    {
        $this->write('S');
        $this->nextColumn();

        $this->lastTestFailed = TRUE;
    }

    /**
     * A testsuite started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @access public
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    }

    /**
     * A testsuite ended.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @access public
     * @since  Method available since Release 2.2.0
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    }

    /**
     * A test started.
     *
     * @param  PHPUnit_Framework_Test $test
     * @access public
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
    }

    /**
     * A test ended.
     *
     * @param  PHPUnit_Framework_Test $test
     * @access public
     */
    public function endTest(PHPUnit_Framework_Test $test)
    {
        if (!$this->lastTestFailed) {
            $this->write('.');
            $this->nextColumn();
        }

        $this->lastTestFailed = FALSE;
    }

    /**
     * @access protected
     */
    protected function nextColumn()
    {
        if ($this->column++ >= 40) {
            $this->column = 0;
            $this->write("\n");
        }
    }
}
?>
