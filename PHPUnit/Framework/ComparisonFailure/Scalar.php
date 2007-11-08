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
 * Thrown when an assertion for scalar equality failed.
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
class PHPUnit_Framework_ComparisonFailure_Scalar extends PHPUnit_Framework_ComparisonFailure
{
    /**
     * Returns a string describing the difference between the expected and the
     * actual scalar value.
     */
    public function toString()
    {
        if (is_int($this->expected) || is_float($this->expected)) {
            $type             = gettype($this->expected);
            $expectedString   = print_r($this->expected, TRUE);
            $actualString     = print_r($this->actual, TRUE);
            $differenceString = print_r(abs($this->actual - $this->expected), TRUE);

            $expectedLen      = strlen($expectedString);
            $actualLen        = strlen($actualString);
            $differenceLen    = strlen($differenceString);
            $maxLen           = max($expectedLen, $actualLen, $differenceLen);

            $expectedString   = str_pad($expectedString, $maxLen, ' ', STR_PAD_LEFT);
            $differenceString = str_pad($differenceString, $maxLen, ' ', STR_PAD_LEFT);
            $actualString     = str_pad($actualString, $maxLen, ' ', STR_PAD_LEFT);

            return sprintf(
              "%s%sexpected %s <%s>\n" .
              "%sdifference%s<%s>\n" .
              '%sgot %s      <%s>',

              $this->message,
              ($this->message != '') ? ' ' : '',
              $type,
              $expectedString,
              ($this->message != '') ? str_repeat(' ', strlen($this->message) + 1) : '',
              str_repeat(' ', strlen($type)),
              $differenceString,
              ($this->message != '') ? str_repeat(' ', strlen($this->message) + 1) : '',
              $type,
              $actualString
            );
        }
    }
}
?>
