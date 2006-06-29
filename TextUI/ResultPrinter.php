<?php
//
// +------------------------------------------------------------------------+
// | PEAR :: PHPUnit2                                                       |
// +------------------------------------------------------------------------+
// | Copyright (c) 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>. |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
//
// $Id: ResultPrinter.php 539 2006-02-13 16:08:42Z sb $
//

require_once 'PHPUnit2/Framework/AssertionFailedError.php';
require_once 'PHPUnit2/Framework/Test.php';
require_once 'PHPUnit2/Framework/TestFailure.php';
require_once 'PHPUnit2/Framework/TestListener.php';
require_once 'PHPUnit2/Framework/TestResult.php';
require_once 'PHPUnit2/Framework/TestSuite.php';
require_once 'PHPUnit2/Util/Filter.php';
require_once 'PHPUnit2/Util/Printer.php';

/**
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2
 * @subpackage  TextUI
 */
class PHPUnit2_TextUI_ResultPrinter extends PHPUnit2_Util_Printer implements PHPUnit2_Framework_TestListener {
    // {{{ Instance Variables

    /**
    * @var    integer
    * @access private
    */
    private $column = 0;

    /**
    * @var    boolean
    * @access private
    */
    private $lastTestFailed = false;

    // }}}
    // {{{ public function printResult(PHPUnit2_Framework_TestResult $result, $timeElapsed)

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

    // }}}
    // {{{ protected function printDefects($defects, $count, $type)

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

    // }}}
    // {{{ protected function printDefect(PHPUnit2_Framework_TestFailure $defect, $count)

    /**
    * @param  PHPUnit2_Framework_TestFailure $defect
    * @param  integer                        $count
    * @access protected
    */
    protected function printDefect(PHPUnit2_Framework_TestFailure $defect, $count) {
        $this->printDefectHeader($defect, $count);
        $this->printDefectTrace($defect);
    }

    // }}}
    // {{{ protected function printDefectHeader(PHPUnit2_Framework_TestFailure $defect, $count)

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

    // }}}
    // {{{ protected function printDefectTrace(PHPUnit2_Framework_TestFailure $defect)

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

    // }}}
    // {{{ protected function printErrors(PHPUnit2_Framework_TestResult $result)

    /**
    * @param  PHPUnit2_Framework_TestResult  $result
    * @access protected
    */
    protected function printErrors(PHPUnit2_Framework_TestResult $result) {
        $this->printDefects($result->errors(), $result->errorCount(), 'error');
    }

    // }}}
    // {{{ protected function printFailures(PHPUnit2_Framework_TestResult $result)

    /**
    * @param  PHPUnit2_Framework_TestResult  $result
    * @access protected
    */
    protected function printFailures(PHPUnit2_Framework_TestResult $result) {
        $this->printDefects($result->failures(), $result->failureCount(), 'failure');
    }

    // }}}
    // {{{ protected function printIncompletes(PHPUnit2_Framework_TestResult $result)

    /**
    * @param  PHPUnit2_Framework_TestResult  $result
    * @access protected
    */
    protected function printIncompletes(PHPUnit2_Framework_TestResult $result) {
        $this->printDefects($result->notImplemented(), $result->notImplementedCount(), 'incomplete test case');
    }

    // }}}
    // {{{ protected function printHeader($timeElapsed)

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

    // }}}
    // {{{ protected function printFooter(PHPUnit2_Framework_TestResult $result)

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

    // }}}
    // {{{ public function printWaitPrompt()

    /**
    * @access public
    */
    public function printWaitPrompt() {
        $this->write("\n<RETURN> to continue\n");
    }

    // }}}
    // {{{ public function addError(PHPUnit2_Framework_Test $test, Exception $e)

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

        $this->lastTestFailed = true;
    }

    // }}}
    // {{{ public function addFailure(PHPUnit2_Framework_Test $test, PHPUnit2_Framework_AssertionFailedError $e)

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

        $this->lastTestFailed = true;
    }

    // }}}
    // {{{ public function addIncompleteTest(PHPUnit2_Framework_Test $test, Exception $e)

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

        $this->lastTestFailed = true;
    }

    // }}}
    // {{{ public function startTestSuite(PHPUnit2_Framework_TestSuite $suite)

    /**
    * A testsuite started.
    *
    * @param  PHPUnit2_Framework_TestSuite $suite
    * @access public
    * @since  2.2.0
    */
    public function startTestSuite(PHPUnit2_Framework_TestSuite $suite) {
    }

    // }}}
    // {{{ public function endTestSuite(PHPUnit2_Framework_TestSuite $suite)

    /**
    * A testsuite ended.
    *
    * @param  PHPUnit2_Framework_TestSuite $suite
    * @access public
    * @since  2.2.0
    */
    public function endTestSuite(PHPUnit2_Framework_TestSuite $suite) {
    }

    // }}}
    // {{{ public function startTest(PHPUnit2_Framework_Test $test)

    /**
    * A test started.
    *
    * @param  PHPUnit2_Framework_Test $test
    * @access public
    */
    public function startTest(PHPUnit2_Framework_Test $test) {
    }

    // }}}
    // {{{ public function endTest(PHPUnit2_Framework_Test $test)

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

        $this->lastTestFailed = false;
    }

    // }}}
    // {{{ private function nextColumn()

    /**
    * @access private
    */
    private function nextColumn() {
        if ($this->column++ >= 40) {
            $this->column = 0;
            $this->write("\n");
        }
    }

    // }}}
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
