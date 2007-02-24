<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHP Version 5
 *
 * Copyright (c) 2002-2006, Sebastian Bergmann <sb@sebastian-bergmann.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 * 
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    CVS: $Id: ComparisonFailure.php,v 1.13.2.3 2005/12/17 16:04:56 sebastian Exp $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit2/Framework/Assert.php';
require_once 'PHPUnit2/Framework/AssertionFailedError.php';

/**
 * Thrown when an assertion for string equality failed.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      Class available since Release 2.0.0
 */
class PHPUnit2_Framework_ComparisonFailure extends PHPUnit2_Framework_AssertionFailedError {
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
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
?>
