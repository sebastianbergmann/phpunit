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
 * @version    SVN: $Id$
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit2/Framework/AssertionFailedError.php';
require_once 'PHPUnit2/Framework/ComparisonFailure.php';

/**
 * A set of assert methods.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      Class available since Release 2.0.0
 * @static
 */
class PHPUnit2_Framework_Assert {
    /**
     * @var    boolean
     * @access private
     * @static
     */
    private static $looselyTyped = FALSE;

    /**
     * Protect constructor since it is a static only class.
     *
     * @access protected
     */
    protected function __construct() {
    }

    /**
     * Asserts that a haystack contains a needle.
     *
     * @param  mixed   $needle
     * @param  mixed   $haystack
     * @param  string  $message
     * @access public
     * @static
     * @since  Method available since Release 2.1.0
     */
    public static function assertContains($needle, $haystack, $message = '') {
        self::doAssertContains($needle, $haystack, TRUE, $message);
    }

    /**
     * Asserts that a haystack does not contain a needle.
     *
     * @param  mixed   $needle
     * @param  mixed   $haystack
     * @param  string  $message
     * @access public
     * @static
     * @since  Method available since Release 2.1.0
     */
    public static function assertNotContains($needle, $haystack, $message = '') {
        self::doAssertContains($needle, $haystack, FALSE, $message);
    }

    /**
     * @param  mixed   $needle
     * @param  mixed   $haystack
     * @param  boolean $condition
     * @param  string  $message
     * @throws Exception
     * @access private
     * @static
     * @since  Method available since Release 2.2.0
     */
    private static function doAssertContains($needle, $haystack, $condition, $message) {
        $found = FALSE;

        if (is_array($haystack) ||
           (is_object($haystack) && $haystack instanceof Iterator)) {
            foreach ($haystack as $straw) {
                if ($straw === $needle) {
                    $found = TRUE;
                    break;
                }
            }
        }

        else if (is_string($needle) && is_string($haystack)) {
            if (strpos($haystack, $needle) !== FALSE) {
                $found = TRUE;
            }
        }

        else {
            throw new Exception;
        }

        if ($condition && !$found) {
            self::fail(
              sprintf(
                '%s%s"%s" does not contain "%s"',

                $message,
                ($message != '') ? ' ' : '',
                self::objectToString($haystack),
                self::objectToString($needle)
              )
            );
        }

        else if (!$condition && $found) {
            self::fail(
              sprintf(
                '%s%s"%s" contains "%s"',

                $message,
                ($message != '') ? ' ' : '',
                self::objectToString($haystack),
                self::objectToString($needle)
              )
            );
        }
    }

    /**
     * Asserts that two variables are equal.
     *
     * @param  mixed  $expected
     * @param  mixed  $actual
     * @param  string $message
     * @param  mixed  $delta
     * @access public
     * @static
     */
    public static function assertEquals($expected, $actual, $message = '', $delta = 0) {
        self::doAssertEquals($expected, $actual, $delta, TRUE, $message);
    }

    /**
     * Asserts that two variables are not equal.
     *
     * @param  mixed  $expected
     * @param  mixed  $actual
     * @param  string $message
     * @param  mixed  $delta
     * @access public
     * @static
     * @since  Method available since Release 2.3.0
     */
    public static function assertNotEquals($expected, $actual, $message = '', $delta = 0) {
        self::doAssertEquals($expected, $actual, $delta, FALSE, $message);
    }

    /**
     * @param  mixed   $expected
     * @param  mixed   $actual
     * @param  mixed   $delta
     * @param  boolean $condition
     * @param  string  $message
     * @access private
     * @static
     * @since  Method available since Release 2.3.0
     */
    private static function doAssertEquals($expected, $actual, $delta, $condition, $message) {
        $equal = FALSE;

        if (is_array($expected)) {
            if (is_array($actual)) {
                self::sortArrayRecursively($actual);
                self::sortArrayRecursively($expected);

                if (self::$looselyTyped) {
                    $actual   = self::convertToString($actual);
                    $expected = self::convertToString($expected);
                }

                $equal = (serialize($expected) == serialize($actual));
            }
        }

        else if (is_float($expected) && is_float($actual) && is_float($delta)) {
            $equal = (abs($expected - $actual) <= $delta);
        }

        else {
            $equal = (serialize($expected) == serialize($actual));
        }

        if ($condition && !$equal) {
            self::failNotSame(
              $expected,
              $actual,
              $message
            );
        }

        else if (!$condition && $equal) {
            self::failSame(
              $expected,
              $actual,
              $message
            );
        }
    }

    /**
     * Asserts that a condition is true.
     *
     * @param  boolean $condition
     * @param  string  $message
     * @throws Exception
     * @access public
     * @static
     */
    public static function assertTrue($condition, $message = '') {
        if (is_bool($condition)) {
            if (!$condition) {
                self::fail($message);
            }
        } else {
            throw new Exception;
        }
    }

    /**
     * Asserts that a condition is false.
     *
     * @param  boolean  $condition
     * @param  string   $message
     * @throws Exception
     * @access public
     * @static
     */
    public static function assertFalse($condition, $message = '') {
        if (is_bool($condition)) {
            self::assertTrue(!$condition, $message);
        } else {
            throw new Exception;
        }
    }

    /**
     * Asserts that a variable is not NULL.
     *
     * @param  mixed  $actual
     * @param  string $message
     * @access public
     * @static
     */
    public static function assertNotNull($actual, $message = '') {
        if (is_null($actual)) {
            self::fail(self::format('NOT NULL', 'NULL', $message));
        }
    }

    /**
     * Asserts that a variable is NULL.
     *
     * @param  mixed  $actual
     * @param  string $message
     * @access public
     * @static
     */
    public static function assertNull($actual, $message = '') {
        if (!is_null($actual)) {
            self::fail(self::format('NULL', 'NOT NULL', $message));
        }
    }

    /**
     * Asserts that two variables have the same type and value.
     * Used on objects, it asserts that two variables reference
     * the same object.
     *
     * @param  mixed  $expected
     * @param  mixed  $actual
     * @param  string $message
     * @access public
     * @static
     */
    public static function assertSame($expected, $actual, $message = '') {
        if ($expected !== $actual) {
            self::failNotSame($expected, $actual, $message);
        }
    }

    /**
     * Asserts that two variables do not have the same type and value.
     * Used on objects, it asserts that two variables do not reference
     * the same object.
     *
     * @param  mixed  $expected
     * @param  mixed  $actual
     * @param  string $message
     * @access public
     * @static
     */
    public static function assertNotSame($expected, $actual, $message = '') {
        if ($expected === $actual) {
            self::failSame($expected, $actual, $message);
        }
    }

    /**
     * Asserts that a variable is of a given type.
     *
     * @param  string $expected
     * @param  mixed  $actual
     * @param  string $message
     * @access public
     * @static
     */
    public static function assertType($expected, $actual, $message = '') {
        self::doAssertType($expected, $actual, TRUE, $message);
    }

    /**
     * Asserts that a variable is not of a given type.
     *
     * @param  string $expected
     * @param  mixed  $actual
     * @param  string $message
     * @access public
     * @static
     * @since  Method available since Release 2.2.0
     */
    public static function assertNotType($expected, $actual, $message = '') {
        self::doAssertType($expected, $actual, FALSE, $message);
    }

    /**
     * @param  string  $expected
     * @param  mixed   $actual
     * @param  boolean $condition
     * @param  string  $message
     * @access private
     * @static
     * @since  Method available since Release 2.2.0
     */
    private static function doAssertType($expected, $actual, $condition, $message) {
        if (!is_string($expected)) {
            throw new Exception;
        }

        if (is_object($actual)) {
            $result = $actual instanceof $expected;
        } else {
            $result = (gettype($actual) == $expected);
        }

        if ($condition && !$result) {
            self::failNotSame(
              $expected,
              $actual,
              $message
            );
        }

        else if (!$condition && $result) {
            self::failSame(
              $expected,
              $actual,
              $message
            );
        }
    }

    /**
     * Asserts that a string matches a given regular expression.
     *
     * @param  string $pattern
     * @param  string $string
     * @param  string $message
     * @access public
     * @static
     */
    public static function assertRegExp($pattern, $string, $message = '') {
        self::doAssertRegExp($pattern, $string, TRUE, $message);
    }

    /**
     * Asserts that a string does not match a given regular expression.
     *
     * @param  string $pattern
     * @param  string $string
     * @param  string $message
     * @access public
     * @static
     * @since  Method available since Release 2.1.0
     */
    public static function assertNotRegExp($pattern, $string, $message = '') {
        self::doAssertRegExp($pattern, $string, FALSE, $message);
    }

    /**
     * @param  mixed   $pattern
     * @param  mixed   $string
     * @param  boolean $condition
     * @param  string  $message
     * @access private
     * @static
     * @since  Method available since Release 2.2.0
     */
    private static function doAssertRegExp($pattern, $string, $condition, $message) {
        if (!is_string($pattern) || !is_string($string)) {
            throw new Exception;
        }

        $result = preg_match($pattern, $string);

        if ($condition && !$result) {
            self::fail(
              sprintf(
                '%s%s"%s" does not match pattern "%s"',

                $message,
                ($message != '') ? ' ' : '',
                $string,
                $pattern
              )
            );
        }

        else if (!$condition && $result) {
            self::fail(
              sprintf(
                '%s%s"%s" matches pattern "%s"',

                $message,
                ($message != '') ? ' ' : '',
                $string,
                $pattern
              )
            );
        }
    }

    /**
     * Fails a test with the given message.
     *
     * @param  string $message
     * @throws PHPUnit2_Framework_AssertionFailedError
     * @access public
     * @static
     */
    public static function fail($message = '') {
        throw new PHPUnit2_Framework_AssertionFailedError($message);
    }

    /**
     * @param  string  $message
     * @throws PHPUnit2_Framework_AssertionFailedError
     * @access private
     * @static
     */
    private static function failSame($message) {
        self::fail(
          sprintf(
            '%s%sexpected not same',

            $message,
            ($message != '') ? ' ' : ''
          )
        );
    }

    /**
     * @param  mixed   $expected
     * @param  mixed   $actual
     * @param  string  $message
     * @throws PHPUnit2_Framework_AssertionFailedError
     * @access private
     * @static
     */
    private static function failNotSame($expected, $actual, $message) {
        if (is_string($expected) && is_string($actual)) {
            throw new PHPUnit2_Framework_ComparisonFailure($expected, $actual, $message);
        }

        self::fail(
          sprintf(
            '%s%sexpected same: <%s> was not: <%s>',

            $message,
            ($message != '') ? ' ' : '',
            self::objectToString($expected),
            self::objectToString($actual)
          )
        );
    }

    /**
     * @param  mixed   $expected
     * @param  mixed   $actual
     * @param  string  $message
     * @access public
     * @static
     */
    public static function format($expected, $actual, $message) {
        return sprintf(
          '%s%sexpected: <%s> but was: <%s>',

          $message,
          ($message != '') ? ' ' : '',
          self::objectToString($expected),
          self::objectToString($actual)
        );
    }

    /**
     * @param  boolean $looselyTyped
     * @access public
     * @static
     */
    public static function setLooselyTyped($looselyTyped) {
        if (is_bool($looselyTyped)) {
            self::$looselyTyped = $looselyTyped;
        }
    }

    /**
     * Converts a value to a string.
     *
     * @param  mixed   $value
     * @access private
     * @static
     */
    private static function convertToString($value) {
        foreach ($value as $k => $v) {
            if (is_array($v)) {
                $value[$k] = self::convertToString($value[$k]);
            } else if (is_object($v)) {
                $value[$k] = self::objectToString($value[$k]);
            } else {
                settype($value[$k], 'string');
            }
        }

        return $value;
    }

    /**
     * @param  mixed   $object
     * @return string
     * @access private
     * @static
     */
    private static function objectToString($object) {
        if (is_array($object) || is_object($object)) {
            $object = serialize($object);
        }

        return $object;
    }

    /**
     * Sorts an array recursively by its keys.
     *
     * @param  array $array
     * @access private
     * @static
     * @author Adam Maccabee Trachtenberg <adam@trachtenberg.com>
     */
    private static function sortArrayRecursively(&$array) {
        ksort($array);

        foreach($array as $k => $v) {
            if (is_array($v)) {
                self::sortArrayRecursively($array[$k]);
            }
        }
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
