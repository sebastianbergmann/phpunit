<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2007, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @package    PHPUnit
 * @author     Jan Borsodi <jb@ez.no>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Thrown when an assertion for type equality failed.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Jan Borsodi <jb@ez.no>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class PHPUnit_Framework_ComparisonFailure_Type extends PHPUnit_Framework_ComparisonFailure
{
    private $expectedIsType;

    /**
     * Initialises with the expected value and the actual value.
     *
     * @param mixed $expected Expected value retrieved.
     * @param mixed $actual Actual value retrieved.
     * @param string $message A string which is prefixed on all returned lines
     *                       in the difference output.
     * @param boolean $expectedIsType
     */
    public function __construct($expected, $actual, $message = '', $expectedIsType = FALSE)
    {
        parent::__construct($expected, $actual, $message);

        $this->expectedIsType = $expectedIsType;
    }

    /**
     * Returns a string describing the type difference between the expected
     * and the actual value.
     */
    public function toString()
    {
        $actualType = gettype($this->actual);

        if ($this->expectedIsType) {
            $expectedType = $this->expected;
        } else {
            $expectedType = gettype($this->expected);
        }

        $expectedDiffLen = strlen($expectedType) - strlen($actualType);
        $actualDiffLen   = -$expectedDiffLen;

        if ($expectedDiffLen > 0) {
            $expectedType .= str_repeat(' ', $expectedDiffLen);
        }

        if ($actualDiffLen > 0) {
            $actualType .= str_repeat(' ', $actualDiffLen);
        }

        $actualValue = '';

        if (is_string($this->actual) || is_bool($this->actual) || is_int($this->actual) || is_float($this->actual)) {
            $actualValue = PHPUnit_Util_Type::toString($this->actual);
        }

        elseif (is_object($this->actual)) {
            $actualValue = '<' . get_class($this->actual) . '>';
        }

        $expectedValue = '';

        if ($this->expectedIsType) {
            $expectedValue = '<' . $this->expected . '>';
        } else {
            if (is_string($this->expected) || is_bool($this->expected) || is_int($this->expected) || is_float($this->expected)) {
                $expectedValue = PHPUnit_Util_Type::toString($this->expected);
            }

            elseif (is_object($this->expected)) {
                $expectedValue = '<' . get_class($this->expected) . '>';
            }
        }

        if ($this->expectedIsType) {
            return sprintf(
              "%s%sexpected type %s\n" .
              '%sgot      type %s %s',

              $this->message,
              ($this->message != '') ? ' ' : '',
              $expectedType,
              ($this->message != '') ? str_repeat(' ', strlen($this->message) + 1) : '',
              $actualType,
              $actualValue
            );
        } else {
            return sprintf(
              "%s%sexpected %s %s\n" .
              '%sgot      %s %s',

              $this->message,
              ($this->message != '') ? ' ' : '',
              $expectedType,
              $expectedValue,
              ($this->message != '') ? str_repeat(' ', strlen($this->message) + 1) : '',
              $actualType,
              $actualValue
            );
        }
    }
}
?>
