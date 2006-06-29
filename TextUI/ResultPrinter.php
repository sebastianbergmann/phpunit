<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHP Version 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id: ResultPrinter.php 539 2006-02-13 16:08:42Z sb $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit2/Framework/TestListener.php';
require_once 'PHPUnit2/Framework/TestFailure.php';
require_once 'PHPUnit2/Util/Filter.php';
require_once 'PHPUnit2/Util/Printer.php';

/**
 * Prints the result of a TextUI TestRunner run.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      Class available since Release 2.0.0
 */
class PHPUnit2_TextUI_ResultPrinter extends PHPUnit2_Util_Printer implements PHPUnit2_Framework_TestListener {
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
     * @param  PHPUnit2_Framework_TestResult $result
     * @param  float                         $runTime
     * @access public
     */
    public function printResult(PHPUnit2_Framework_TestResult $result, $timeElapsed) {
        $this->printHeader($timeElapsed);
        $this->printErrors($result);
        $this->printFailures($result);
        $this->printIncompletes($result);
        $this->printFooter($result);
    }

    /**
     * @param  array   $defects
     * @param  integer $count
     * @param  string  $type
     * @access protected
     */
    protected function printDefects($defects, $count, $type) {
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
     * @param  PHPUnit2_Framework_TestFailure $defect
     * @param  integer                        $count
     * @access protected
     */
    protected function printDefect(PHPUnit2_Framework_TestFailure $defect, $count) {
        $this->printDefectHeader($defect, $count);
        $this->printDefectTrace($defect);
    }

    /**
     * @param  PHPUnit2_Framework_TestFailure $defect
     * @param  integer                        $count
     * @access protected
     */
    protected function printDefectHeader(PHPUnit2_Framework_TestFailure $defect, $count) {
        $this->write(
          sprintf(
            "%d) %s\n",

            $count,
            $defect->failedTest()->toString()
          )
        );
    }

    /**
     * @param  PHPUnit2_Framework_TestFailure $defect
     * @access protected
     */
    protected function printDefectTrace(PHPUnit2_Framework_TestFailure $defect) {
        $e       = $defect->thrownException();
        $message = method_exists($e, 'toString') ? $e->toString() : $e->getMessage();

        $this->write($message . "\n");

        $this->write(
          PHPUnit2_Util_Filter::getFilteredStacktrace(
            $defect->thrownException()
          )
        );
    }

    /**
     * @param  PHPUnit2_Framework_TestResult  $result
     * @access protected
     */
    protected function printErrors(PHPUnit2_Framework_TestResult $result) {
        $this->printDefects($result->errors(), $result->errorCount(), 'error');
    }

    /**
     * @param  PHPUnit2_Framework_TestResult  $result
     * @access protected
     */
    protected function printFailures(PHPUnit2_Framework_TestResult $result) {
        $this->printDefects($result->failures(), $result->failureCount(), 'failure');
    }

    /**
     * @param  PHPUnit2_Framework_TestResult  $result
     * @access protected
     */
    protected function printIncompletes(PHPUnit2_Framework_TestResult $result) {
        $this->printDefects($result->notImplemented(), $result->notImplementedCount(), 'incomplete test case');
    }

    /**
     * @param  float   $timeElapsed
     * @access protected
     */
    protected function printHeader($timeElapsed) {
        $this->write(
          sprintf(
            "\n\nTime: %s\n",

            $timeElapsed
          )
        );
    }

    /**
     * @param  PHPUnit2_Framework_TestResult  $result
     * @access protected
     */
    protected function printFooter(PHPUnit2_Framework_TestResult $result) {
        if ($result->allCompletlyImplemented() &&
            $result->wasSuccessful()) {
            $this->write(
              sprintf(
                "\nOK (%d test%s)\n",

                $result->runCount(),
                ($result->runCount() == 1) ? '' : 's'
              )
            );
        }
        
        else if (!$result->allCompletlyImplemented() &&
                 $result->wasSuccessful()) {
            $this->write(
              sprintf(
                "\nOK, but incomplete test cases!!!\nTests run: %d, incomplete test cases: %d.\n",

                $result->runCount(),
                $result->notImplementedCount()
              )
            );
        }        
        
        else {
            $this->write(
              sprintf(
                "\nFAILURES!!!\nTests run: %d, Failures: %d, Errors: %d, Incomplete Tests: %d.\n",

                $result->runCount(),
                $result->failureCount(),
                $result->errorCount(),
                $result->notImplementedCount()
              )
            );
        }
    }

    /**
     * @access public
     */
    public function printWaitPrompt() {
        $this->write("\n<RETURN> to continue\n");
    }

    /**
     * An error occurred.
     *
     * @param  PHPUnit2_Framework_Test $test
     * @param  Exception               $e
     * @access public
     */
    public function addError(PHPUnit2_Framework_Test $test, Exception $e) {
        $this->write('E');
        $this->nextColumn();

        $this->lastTestFailed = TRUE;
    }

    /**
     * A failure occurred.
     *
     * @param  PHPUnit2_Framework_Test                 $test
     * @param  PHPUnit2_Framework_AssertionFailedError $e
     * @access public
     */
    public function addFailure(PHPUnit2_Framework_Test $test, PHPUnit2_Framework_AssertionFailedError $e) {
        $this->write('F');
        $this->nextColumn();

        $this->lastTestFailed = TRUE;
    }

    /**
     * Incomplete test.
     *
     * @param  PHPUnit2_Framework_Test $test
     * @param  Exception               $e
     * @access public
     */
    public function addIncompleteTest(PHPUnit2_Framework_Test $test, Exception $e) {
        $this->write('I');
        $this->nextColumn();

        $this->lastTestFailed = TRUE;
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
     * @param  PHPUnit2_Framework_Test $test
     * @access public
     */
    public function startTest(PHPUnit2_Framework_Test $test) {
    }

    /**
     * A test ended.
     *
     * @param  PHPUnit2_Framework_Test $test
     * @access public
     */
    public function endTest(PHPUnit2_Framework_Test $test) {
        if (!$this->lastTestFailed) {
            $this->write('.');
            $this->nextColumn();
        }

        $this->lastTestFailed = FALSE;
    }

    /**
     * @access protected
     */
    protected function nextColumn() {
        if ($this->column++ >= 40) {
            $this->column = 0;
            $this->write("\n");
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
