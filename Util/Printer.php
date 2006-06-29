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
// $Id: Printer.php 539 2006-02-13 16:08:42Z sb $
//

/**
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2
 * @subpackage  Util
 * @abstract
 */
abstract class PHPUnit2_Util_Printer {
    // {{{ Instance Variables

    /**
    * @var    resource
    * @access private
    */
    private $out;

    // }}}
    // {{{ public function __construct($out = NULL)

    /**
    * Constructor.
    *
    * @param  mixed $out
    * @access public
    */
    public function __construct($out = NULL) {
        if ($out === NULL) {
            $this->out = fopen('php://stdout', 'r');
        }

        else if (is_string($out)) {
            $this->out = fopen($out, 'w');
        }

        else {
            $this->out = $out;
        }
    }

    // }}}
    // {{{ public function __destruct()

    /**
    * Destructor.
    *
    * @access public
    */
    public function __destruct() {
        fclose($this->out);
    }

    // }}}
    // {{{ public function write($buffer)

    /**
    * @param  string $buffer
    * @access public
    */
    public function write($buffer) {
        fputs($this->out, $buffer);
    }

    // }}}
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
