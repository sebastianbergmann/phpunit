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
// $Id: PEAR.php 539 2006-02-13 16:08:42Z sb $
//

require_once 'PHPUnit2/Framework/AssertionFailedError.php';
require_once 'PHPUnit2/Framework/Test.php';
require_once 'PHPUnit2/Framework/TestFailure.php';
require_once 'PHPUnit2/Framework/TestListener.php';
require_once 'PHPUnit2/Framework/TestResult.php';
require_once 'PHPUnit2/Framework/TestSuite.php';

@include_once 'Log.php';

/**
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2
 * @subpackage  Extensions
 * @since       2.1.0
 */
class PHPUnit2_Extensions_Log_PEAR implements PHPUnit2_Framework_TestListener {
    // {{{ Instance Variables

    /**
    * Log.
    *
    * @var    Log
    * @access private
    */
    private $log;

    // }}}
    // {{{ public function __construct($type, $name = '', $ident = '', $conf = array(), $maxLevel = PEAR_LOG_DEBUG)

    /**
    * @param string $type      The type of concrete Log subclass to use.
    *                          Currently, valid values are 'console',
    *                          'syslog', 'sql', 'file', and 'mcal'.
    * @param string $name      The name of the actually log file, table, or
    *                          other specific store to use. Defaults to an
    *                          empty string, with which the subclass will
    *                          attempt to do something intelligent.
    * @param string $ident     The identity reported to the log system.
    * @param array  $conf      A hash containing any additional configuration
    *                          information that a subclass might need.
    * @param int $maxLevel     Maximum priority level at which to log.
    * @access public
    */
    public function __construct($type, $name = '', $ident = '', $conf = array(), $maxLevel = PEAR_LOG_DEBUG) {
        $this->log = Log::factory($type, $name, $ident, $conf, $maxLevel);
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
        $this->log->crit(
          sprintf(
            'Test "%s" failed: %s',

            $test->getName(),
            $e->getMessage()
          )
        );
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
        $this->log->err(
          sprintf(
            'Test "%s" failed: %s',

            $test->getName(),
            $e->getMessage()
          )
        );
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
        $this->log->info(
          sprintf(
            'Test "%s" incomplete: %s',

            $test->getName(),
            $e->getMessage()
          )
        );
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
        $this->log->info(
          sprintf(
            'TestSuite "%s" started.',

            $suite->getName()
          )
        );
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
        $this->log->info(
          sprintf(
            'TestSuite "%s" ended.',

            $suite->getName()
          )
        );
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
        $this->log->info(
          sprintf(
            'Test "%s" started.',

            $test->getName()
          )
        );
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
        $this->log->info(
          sprintf(
            'Test "%s" ended.',

            $test->getName()
          )
        );
    }

    // }}}
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
