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
// $Id: ExceptionTestCase.php 539 2006-02-13 16:08:42Z sb $
//

require_once 'PHPUnit2/Framework/TestCase.php';

/**
 * A TestCase that expects an Exception of class
 * fExpected to be thrown.
 *
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2004 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2
 * @subpackage  Extensions
 */
class PHPUnit2_Extensions_ExceptionTestCase extends PHPUnit2_Framework_TestCase {
    // {{{ Members

    /**
    * The name of the expected Exception.
    *
    * @var    string
    * @access private
    */
    private $expected = '';

    // }}}
    // {{{ public function __construct($name, $exceptionName)

    /**
    * @param  string  $name
    * @param  string  $exceptionName
    * @access public
    */
    public function __construct($name, $exceptionName) {
        parent::__construct($name);
        $this->expected = $exceptionName;
    }

    // }}}
    // {{{ protected function runTest()

    /**
    * @access protected
    */
    protected function runTest() {
        try {
            parent::runTest();
        }

        catch (Exception $e) {
            if ($e instanceof $this->expected) {
                return;
            } else {
                throw $e;
            }
        }

        $this->fail('Expected exception ' . $this->expected);
    }

    // }}}
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
