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
// $Id: TestFailure.php 539 2006-02-13 16:08:42Z sb $
//

require_once 'PHPUnit2/Framework/Test.php';

/**
 * A TestFailure collects a failed test together with the caught exception.
 *
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2004 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    PHP
 * @package     PHPUnit2
 * @subpackage  Framework
 */
class PHPUnit2_Framework_TestFailure {
    // {{{ Members

    /**
    * @var    PHPUnit2_Framework_Test
    * @access protected
    */
    protected $failedTest;

    /**
    * @var    Exception
    * @access protected
    */
    protected $thrownException;

    // }}}
    // {{{ public function __construct(PHPUnit2_Framework_Test $failedTest, Exception $thrownException)

    /**
    * Constructs a TestFailure with the given test and exception.
    *
    * @param  PHPUnit2_Framework_Test $failedTest
    * @param  Exception               $thrownException
    * @access public
    */
    public function __construct(PHPUnit2_Framework_Test $failedTest, Exception $thrownException) {
        $this->failedTest      = $failedTest;
        $this->thrownException = $thrownException;
    }

    // }}}
    // {{{ public function toString()

    /**
    * Returns a short description of the failure.
    *
    * @return string
    * @access public
    */
    public function toString() {
        return sprintf(
          '%s: %s',

          $this->failedTest,
          $this->thrownException->getMessage()
        );
    }

    // }}}
    // {{{ public function failedTest()

    /**
    * Gets the failed test.
    *
    * @return Test
    * @access public
    */
    public function failedTest() {
        return $this->failedTest;
    }

    // }}}
    // {{{ public function thrownException()

    /**
    * Gets the thrown exception.
    *
    * @return Exception
    * @access public
    */
    public function thrownException() {
        return $this->thrownException;
    }

    // }}}
    // {{{ public function exceptionMessage()

    /**
    * Returns the exception's message.
    *
    * @return string
    * @access public
    */
    public function exceptionMessage() {
        return $this->thrownException()->getMessage();
    }

    // }}}
    // {{{ public function isFailure()

    /**
    * Returns TRUE if the thrown exception
    * is of type AssertionFailedError.
    *
    * @return boolean
    * @access public
    */
    public function isFailure() {
        return ($this->thrownException() instanceof PHPUnit2_Framework_AssertionFailedError);
    }

    // }}}
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
