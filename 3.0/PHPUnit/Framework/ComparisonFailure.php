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
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/Type.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

if (!class_exists('PHPUnit_Framework_ComparisonFailure', FALSE)) {

/**
 * Thrown when an assertion for string equality failed.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Jan Borsodi <jb@ez.no>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.0.0
 */
abstract class PHPUnit_Framework_ComparisonFailure extends PHPUnit_Framework_AssertionFailedError
{
    /**
     * Expected value of the retrieval which does not match $actual.
     * @var mixed
     */
    protected $expected;

    /**
     * Actually retrieved value which does not match $expected.
     * @var mixed
     */
    protected $actual;

    /**
     * Optional message which is placed in front of the first line
     * returned by toString().
     * @var string
     */
    protected $message;

    /**
     * Initialises with the expected value and the actual value.
     *
     * @param mixed $expected Expected value retrieved.
     * @param mixed $actual Actual value retrieved.
     * @param string $message A string which is prefixed on all returned lines
     *                       in the difference output.
     */
    public function __construct($expected, $actual, $message = '')
    {
        $this->expected = $expected;
        $this->actual   = $actual;
        $this->message  = $message;
    }

    /**
     * Figures out which diff class to use for the input types then
     * instantiates that class and returns the object.
     * @note The diff is type sensitive, if the type differs only the types
     *       are shown.
     *
     * @param mixed $expected Expected value retrieved.
     * @param mixed $actual Actual value retrieved.
     * @param string $message A string which is prefixed on all returned lines
     *                       in the difference output.
     * @return PHPUnit_Framework_ComparisonFailure
     */
    public static function diffIdentical($expected, $actual, $message = '')
    {
        if (gettype($expected) !== gettype($actual)) {
            return new PHPUnit_Framework_ComparisonFailure_Type($expected, $actual, $message);
        }

        elseif (is_string($expected)) {
            return new PHPUnit_Framework_ComparisonFailure_String($expected, $actual, $message);
        }

        elseif (is_bool($expected) || is_int($expected) || is_float($expected)) {
            return new PHPUnit_Framework_ComparisonFailure_Scalar($expected, $actual, $message);
        }

        elseif (is_array($expected)) {
            return new PHPUnit_Framework_ComparisonFailure_Array($expected, $actual, $message);
        }

        elseif (is_object($expected)) {
            return new PHPUnit_Framework_ComparisonFailure_Object($expected, $actual, $message);
        }
    }

    /**
     * Figures out which diff class to use for the input types then
     * instantiates that class and returns the object.
     * @note The diff is not type sensitive, if the type differs the $actual
     *       value will be converted to the same type as the $expected.
     *
     * @param mixed $expected Expected value retrieved.
     * @param mixed $actual Actual value retrieved.
     * @param string $message A string which is prefixed on all returned lines
     *                       in the difference output.
     * @return PHPUnit_Framework_ComparisonFailure
     */
    public static function diffEqual($expected, $actual, $message = '')
    {
        if (gettype($expected) !== gettype($actual)) {
            $expected = (string)$expected;
            $actual = (string)$actual;
        }

        if (is_string($expected)) {
            return new PHPUnit_Framework_ComparisonFailure_String($expected, $actual, $message);
        }

        elseif (is_bool($expected) || is_int($expected) || is_float($expected)) {
            return new PHPUnit_Framework_ComparisonFailure_Scalar($expected, $actual, $message);
        }

        elseif (is_array($expected)) {
            return new PHPUnit_Framework_ComparisonFailure_Array($expected, $actual, $message);
        }

        elseif (is_object($expected)) {
            return new PHPUnit_Framework_ComparisonFailure_Object($expected, $actual, $message);
        }
    }

    /**
     * Exports the value $value to a string but in a shortened form.
     *
     * @param mixed $value The value to export as string.
     * @return string
     */
    public static function shortenedExport($value)
    {
        if (is_string($value)) {
            return self::shortenedString($value);
        }

        elseif (is_array($value)) {
            if (count($value) == 0) {
                return 'array()';
            }

            $a1 = array_slice($value, 0, 1, TRUE);
            $k1 = key($a1);
            $v1 = $a1[$k1];

            if (is_string($v1)) {
                $v1 = self::shortenedString($v1);
            }

            elseif (is_array($v1)) {
                $v1 = 'array(...)';
            } else {
                $v1 = PHPUnit_Util_Type::toString($v1);
            }

            $a2 = false;

            if (count($value) > 1) {
                $a2 = array_slice($value, -1, 1, TRUE);
                $k2 = key($a2);
                $v2 = $a2[$k2];

                if (is_string($v2)) {
                    $v2 = self::shortenedString($v2);
                }

                elseif (is_array($v2)) {
                    $v2 = 'array(...)';
                } else {
                    $v2 = PHPUnit_Util_Type::toString($v2);
                }
            }

            $text = 'array( ' . PHPUnit_Util_Type::toString($k1) . ' => ' . $v1;

            if ($a2 !== FALSE) {
                $text .= ', ..., ' . PHPUnit_Util_Type::toString($k2) . ' => ' . $v2 . ' )';
            } else {
                $text .= ' )';
            }

            return $text;
        }

        elseif (is_object($value)) {
            return 'class ' . get_class($value) . '(...)';
        }

        return PHPUnit_Util_Type::toString($value);
    }

    /**
     * Shortens the string $string and returns it. If the string is already short
     * enough it is returned as it was.
     *
     * @param string $string The string value which must be shortened.
     * @return string
     */
    private static function shortenedString($string)
    {
        $string = preg_replace('#\n|\r\n|\r#', ' ', $string);

        if (strlen($string) > 14) {
            return PHPUnit_Util_Type::toString(substr($string, 0, 7) . '...' . substr($string, -7));
        } else {
            return PHPUnit_Util_Type::toString($string);
        }
    }
}

}

require_once 'PHPUnit/Framework/ComparisonFailure/Array.php';
require_once 'PHPUnit/Framework/ComparisonFailure/Object.php';
require_once 'PHPUnit/Framework/ComparisonFailure/Scalar.php';
require_once 'PHPUnit/Framework/ComparisonFailure/String.php';
require_once 'PHPUnit/Framework/ComparisonFailure/Type.php';
?>
