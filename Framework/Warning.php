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
// $Id: Warning.php 539 2006-02-13 16:08:42Z sb $
//

/**
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2004 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    PHP
 * @package     PHPUnit2
 * @subpackage  Framework
 */
class PHPUnit2_Framework_Warning extends PHPUnit2_Framework_TestCase {
    // {{{ Members

    /**
    * @var    string
    * @access private
    */
    private $message = '';

    // }}}
    // {{{ public function __construct($message = '')

    /**
    * @param  string  $message
    * @access protected
    */
    public function __construct($message = '') {
        $this->message = $message;
        parent::__construct('Warning');
    }

    // }}}
    // {{{ protected function runTest()

    /**
    * @access protected
    */
    protected function runTest() {
        $this->fail($this->message);
    }

    // }}}
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
