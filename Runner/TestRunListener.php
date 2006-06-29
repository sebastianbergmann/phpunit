<?php
//
// +------------------------------------------------------------------------+
// | PEAR :: PHPUnit2                                                       |
// +------------------------------------------------------------------------+
// | Copyright (c) 2002-2004 Sebastian Bergmann <sb@sebastian-bergmann.de>. |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
//
// $Id: TestRunListener.php 539 2006-02-13 16:08:42Z sb $
//

/**
 * A listener interface for observing the
 * execution of a test run. Unlike TestListener,
 * this interface using only primitive objects,
 * making it suitable for remote test execution.
 *
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2004 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    PHP
 * @package     PHPUnit2
 * @subpackage  Runner
 */
interface PHPUnit2_Runner_TestRunListener {
    // {{{ Constants

    const STATUS_ERROR      = 1;
    const STATUS_FAILURE    = 2;
    const STATUS_INCOMPLETE = 3;

    // }}}
    // {{{ public function testRunStarted($testSuiteName, $testCount)

    /**
    * @param  string  $testSuiteName
    * @param  integer $testCount
    * @access public
    */
    public function testRunStarted($testSuiteName, $testCount);

    // }}}
    // {{{ public function testRunEnded($elapsedTime)

    /**
    * @param  integer $elapsedTime
    */
    public function testRunEnded($elapsedTime);

    // }}}
    // {{{ public function testRunStopped($elapsedTime)

    /**
    * @param  integer $elapsedTime
    * @access public
    */
    public function testRunStopped($elapsedTime);

    // }}}
    // {{{ public function testStarted($testName)

    /**
    * @param  string $testName
    * @access public
    */
    public function testStarted($testName);

    // }}}
    // {{{ public function testEnded($testName)

    /**
    * @param  string $testName
    * @access public
    */
    public function testEnded($testName);

    // }}}
    // {{{ public function testFailed($status, $testName, $trace)

    /**
    * @param  integer $status
    * @param  string  $testName
    * @param  string  $trace
    * @access public
    */
    public function testFailed($status, $testName, $trace);

    // }}}
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
