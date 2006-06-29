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
// $Id: ExceptionTestCase.php 539 2006-02-13 16:08:42Z sb $
//

require_once 'PHPUnit2/Framework/TestCase.php';

/**
 * A TestCase that expects an Exception of class
 * fExpected to be thrown.
 *
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2
 * @subpackage  Extensions
 */
class PHPUnit2_Extensions_ExceptionTestCase extends PHPUnit2_Framework_TestCase {
    // {{{ Instance Variables

    /**
    * The name of the expected Exception.
    *
    * @var    string
    * @access private
    */
    private $expectedException = '';

    // }}}
    // {{{ public function __construct($name, $exceptionName = '')

    /**
    * @param  string  $name
    * @param  string  $exceptionName
    * @access public
    */
    public function __construct($name, $exceptionName = '') {
        parent::__construct($name);

        if (!empty($exceptionName)) {
            $this->setExpectedException($exceptionName);
        }
    }

    // }}}
    // {{{ public function getExpectedException()

    /**
    * @return string
    * @access public
    * @since  2.2.0
    */
    public function getExpectedException() {
        return $this->expectedException;
    }

    // }}}
    // {{{ public function setExpectedException($exceptionName)

    /**
    * @param  string  $exceptionName
    * @access public
    * @since  2.2.0
    */
    public function setExpectedException($exceptionName) {
        if (is_string($exceptionName) && class_exists($exceptionName)) {
            $this->expectedException = $exceptionName;
        } else {
            throw new Exception(
              sprintf(
                'Exception %s does not exist.',
                $exceptionName
              )
            );
        }
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
            if ($e instanceof $this->expectedException) {
                return;
            } else {
                throw $e;
            }
        }

        $this->fail('Expected exception ' . $this->expectedException);
    }

    // }}}
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
