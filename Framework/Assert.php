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
// $Id: Assert.php 539 2006-02-13 16:08:42Z sb $
//

require_once 'PHPUnit2/Framework/AssertionFailedError.php';
require_once 'PHPUnit2/Framework/ComparisonFailure.php';

/**
 * A set of assert methods.
 *
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2
 * @subpackage  Framework
 * @static
 */
class PHPUnit2_Framework_Assert {
    // {{{ Static Members

    /**
    * @var    boolean
    * @access private
    * @static
    */
    private static $looselyTyped = FALSE;

    // }}}
    // {{{ protected function __construct()

    /**
    * Protect constructor since it is a static only class.
    *
    * @access protected
    */
    protected function __construct() {}

    // }}}
    // {{{ public static function assertContains($needle, $haystack, $message = '')

    /**
    * Asserts that a haystack contains a needle.
    *
    * @param  mixed   $needle
    * @param  mixed   $haystack
    * @param  string  $message
    * @access public
    * @static
    * @since  2.1.0
    */
    public static function assertContains($needle, $haystack, $message = '') {
        if (is_string($needle) && is_string($haystack)) {
            if (strpos($haystack, $needle) === FALSE) {
                self::failNotContains($needle, $haystack, $message);
            }
        }

        else if (is_array($haystack)) {
            if (is_object($needle)) {
                $found = FALSE;

                foreach ($haystack as $straw) {
                    if ($straw === $needle) {
                        $found = TRUE;
                        break;
                    }
                }

                if (!$found) {
                    self::failNotContains($needle, $haystack, $message);
                }
            } else {
                if (!in_array($needle, $haystack)) {
                    self::failNotContains($needle, $haystack, $message);
                }
            }
        }

        else {
            throw new Exception('Unsupported parameter passed to assertContains().');
        }
    }

    // }}}
    // {{{ public static function assertNotContains($needle, $haystack, $message = '')

    /**
    * Asserts that a haystack does not contain a needle.
    *
    * @param  mixed   $needle
    * @param  mixed   $haystack
    * @param  string  $message
    * @access public
    * @static
    * @since  2.1.0
    */
    public static function assertNotContains($needle, $haystack, $message = '') {
        if (is_string($needle) && is_string($haystack)) {
            if (strpos($haystack, $needle) !== FALSE) {
                self::failContains($needle, $haystack, $message);
            }
        }

        else if (is_array($haystack)) {
            if (is_object($needle)) {
                $found = FALSE;

                foreach ($haystack as $straw) {
                    if ($straw === $needle) {
                        $found = TRUE;
                        break;
                    }
                }

                if ($found) {
                    self::failContains($needle, $haystack, $message);
                }
            } else {
                if (in_array($needle, $haystack)) {
                    self::failContains($needle, $haystack, $message);
                }
            }
        }

        else {
            throw new Exception('Unsupported parameter passed to assertContains().');
        }
    }

    // }}}
    // {{{ public static function assertEquals($expected, $actual, $message = '', $delta = 0)

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
        if (is_null($expected) && is_null($actual)) {
            return;
        }

        if (is_object($expected)) {
            if (!is_object($actual) || (serialize($expected) != serialize($actual))) {
                self::failNotEquals($expected, $actual, $message);
            }

            return;
        }

        if (is_array($expected)) {
            if (!is_array($actual)) {
                self::failNotEquals($expected, $actual, $message);
            }

            self::sortArrayRecursively($actual);
            self::sortArrayRecursively($expected);

            if (self::$looselyTyped) {
                $actual   = self::convertToString($actual);
                $expected = self::convertToString($expected);
            }

            self::assertEquals(serialize($expected), serialize($actual), $message);

            return;
        }

        if ((is_double($expected) || is_float($expected)) &&
            (is_double($actual)   || is_float($actual)) &&
            (is_double($delta)    || is_float($delta))) {
            if (!(abs($expected - $actual) <= $delta)) {
                self::failNotEquals($expected, $actual, $message);
            }

            return;
        }

        if (self::$looselyTyped) {
            settype($actual, gettype($expected));
        }

        self::assertSame($expected, $actual, $message);
    }

    // }}}
    // {{{ public static function assertTrue($condition, $message = '')

    /**
    * Asserts that a condition is true.
    *
    * @param  boolean $condition
    * @param  string  $message
    * @access public
    * @static
    */
    public static function assertTrue($condition, $message = '') {
        if (!$condition) {
            self::fail($message);
        }
    }

    // }}}
    // {{{ public static function assertFalse($condition, $message = '')

    /**
    * Asserts that a condition is false.
    *
    * @param  boolean  $condition
    * @param  string   $message
    * @access public
    * @static
    */
    public static function assertFalse($condition, $message = '') {
        self::assertTrue(!$condition, $message);
    }

    // }}}
    // {{{ public static function assertNotNull($object, $message = '')

    /**
    * Asserts that an object isn't null.
    *
    * @param  object $object
    * @param  string $message
    * @access public
    * @static
    */
    public static function assertNotNull($object, $message = '') {
        self::assertTrue($object !== NULL, $message);
    }

    // }}}
    // {{{ public static function assertNull($object, $message = '')

    /**
    * Asserts that an object is null.
    *
    * @param  object $object
    * @param  string $message
    * @access public
    * @static
    */
    public static function assertNull($object, $message = '') {
        self::assertTrue($object === NULL, $message);
    }

    // }}}
    // {{{ public static function assertSame($expected, $actual, $message = '')

    /**
    * Asserts that two objects refer to the same object.
    *
    * @param  object $object
    * @param  object $object
    * @param  string $message
    * @access public
    * @static
    */
    public static function assertSame($expected, $actual, $message = '') {
        if ($expected !== $actual) {
            self::failNotSame($expected, $actual, $message);
        }
    }

    // }}}
    // {{{ public static function assertNotSame($expected, $actual, $message = '')

    /**
    * Asserts that two objects refer not to the same object.
    *
    * @param  object $object
    * @param  object $object
    * @param  string $message
    * @access public
    * @static
    */
    public static function assertNotSame($expected, $actual, $message = '') {
        if ($expected === $actual) {
            self::failSame($message);
        }
    }

    // }}}
    // {{{ public static function assertType($expected, $actual, $message = '')

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
        if (is_object($actual)) {
            $actual = get_class($actual);
        } else {
            $actual = gettype($actual);
        }

        if ($expected != $actual) {
            self::failNotEquals(
              $expected,
              $actual,
              $message
            );
        }
    }

    // }}}
    // {{{ public static function assertRegExp($pattern, $string, $message = '')

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
        if (!preg_match($pattern, $string)) {
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
    }

    // }}}
    // {{{ public static function assertNotRegExp($pattern, $string, $message = '')

    /**
    * Asserts that a string does not match a given regular expression.
    *
    * @param  string $pattern
    * @param  string $string
    * @param  string $message
    * @access public
    * @static
    * @since  2.1.0
    */
    public static function assertNotRegExp($pattern, $string, $message = '') {
        if (preg_match($pattern, $string)) {
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

    // }}}
    // {{{ public static function fail($message = '')

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

    // }}}
    // {{{ public static function format($expected, $actual, $message)

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

    // }}}
    // {{{ public static function setLooselyTyped($looselyTyped)

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

    // }}}
    // {{{ private static function convertToString($value)

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

    // }}}
    // {{{ private static function failSame($message)

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

    // }}}
    // {{{ private static function failNotSame($expected, $actual, $message)

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

    // }}}
    // {{{ private static function failNotEquals($expected, $actual, $message)

    /**
    * @param  mixed   $expected
    * @param  mixed   $actual
    * @param  string  $message
    * @throws PHPUnit2_Framework_AssertionFailedError
    * @access private
    * @static
    */
    private static function failNotEquals($expected, $actual, $message) {
        self::fail(self::format($expected, $actual, $message));
    }

    // }}}
    // {{{ private static function failContains($needle, $haystack, $message)

    /**
    * @param  mixed   $needle
    * @param  mixed   $haystack
    * @param  string  $message
    * @throws PHPUnit2_Framework_AssertionFailedError
    * @access private
    * @static
    * @since  2.1.2
    */
    private static function failContains($needle, $haystack, $message) {
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

    // }}}
    // {{{ private static function failNotContains($needle, $haystack, $message)

    /**
    * @param  mixed   $needle
    * @param  mixed   $haystack
    * @param  string  $message
    * @throws PHPUnit2_Framework_AssertionFailedError
    * @access private
    * @static
    * @since  2.1.2
    */
    private static function failNotContains($needle, $haystack, $message) {
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

    // }}}
    // {{{ private static function objectToString($object)

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

    // }}}
    // {{{ private static function sortArrayRecursively(&$array) {

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

    // }}}
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
