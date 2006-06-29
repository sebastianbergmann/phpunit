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
// $Id: ComparisonFailure.php 539 2006-02-13 16:08:42Z sb $
//

require_once 'PHPUnit2/Framework/Assert.php';
require_once 'PHPUnit2/Framework/AssertionFailedError.php';

/**
 * Thrown when an assertion for string equality failed.
 *
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2
 * @subpackage  Framework
 */
class PHPUnit2_Framework_ComparisonFailure extends PHPUnit2_Framework_AssertionFailedError {
    // {{{ Members

    /**
    * @var    string
    * @access private
    */
    private $expected = '';

    /**
    * @var    string
    * @access private
    */
    private $actual = '';

    // }}}
    // {{{ public function __construct($expected, $actual, $message = '')

    /**
    * Constructs a comparison failure.
    *
    * @param  string $expected
    * @param  string $actual
    * @param  string $message
    * @access public
    */
    public function __construct($expected, $actual, $message = '') {
        parent::__construct($message);

        $this->expected = ($expected === NULL) ? 'NULL' : $expected;
        $this->actual   = ($actual   === NULL) ? 'NULL' : $actual;
    }

    // }}}
    // {{{ public function toString()

    /**
    * Returns "..." in place of common prefix and "..." in
    * place of common suffix between expected and actual.
    *
    * @return string
    * @access public
    */
    public function toString() {
        $end = min(strlen($this->expected), strlen($this->actual));
        $i   = 0;
        $j   = strlen($this->expected) - 1;
        $k   = strlen($this->actual)   - 1;

        for (; $i < $end; $i++) {
            if ($this->expected[$i] != $this->actual[$i]) {
                break;
            }
        }

        for (; $k >= $i && $j >= $i; $k--,$j--) {
            if ($this->expected[$j] != $this->actual[$k]) {
                break;
            }
        }

        if ($j < $i && $k < $i) {
            $expected = $this->expected;
            $actual   = $this->actual;
        } else {
            $expected = substr($this->expected, $i, ($j + 1 - $i));
            $actual   = substr($this->actual,   $i, ($k + 1 - $i));;

            if ($i <= $end && $i > 0) {
                $expected = '...' . $expected;
                $actual   = '...' . $actual;
            }
      
            if ($j < strlen($this->expected) - 1) {
                $expected .= '...';
            }

            if ($k < strlen($this->actual) - 1) {
                $actual .= '...';
            }
        }

        return PHPUnit2_Framework_Assert::format(
            $expected,
            $actual,
            parent::getMessage()
        );
    }

    // }}}
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
