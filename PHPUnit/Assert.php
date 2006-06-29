<?php
//
// +------------------------------------------------------------------------+
// | PEAR :: PHPUnit                                                        |
// +------------------------------------------------------------------------+
// | Copyright (c) 2002-2003 Sebastian Bergmann <sb@sebastian-bergmann.de>. |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
//
// $Id$
//

/**
 * A set of assert methods.
 *
 * @package PHPUnit
 * @author  Sebastian Bergmann <sb@sebastian-bergmann.de>
 *          Based upon JUnit, see http://www.junit.org/ for details.
 */
class PHPUnit_Assert {
    /**
    * @var    boolean
    * @access private
    */
    var $_looselyTyped = false;

    /**
    * Asserts that two variables are equal.
    *
    * @param  mixed
    * @param  mixed
    * @param  string
    * @param  mixed
    * @access public
    */
    function assertEquals($expected, $actual, $message = '', $delta = 0) {
        if ((is_array($actual)  && is_array($expected)) ||
            (is_object($actual) && is_object($expected))) {
            if (is_array($actual) && is_array($expected)) {
                ksort($actual);
                ksort($expected);
            }

            if ($this->_looselyTyped) {
                $actual   = $this->_convertToString($actual);
                $expected = $this->_convertToString($expected);
            }

            $actual   = serialize($actual);
            $expected = serialize($expected);

            $message = sprintf(
              '%sexpected %s, actual %s',

              !empty($message) ? $message . ' ' : '',
              $expected,
              $actual
            );

            if ($actual !== $expected) {
                return $this->fail($message);
            }
        }

        elseif (is_numeric($actual) && is_numeric($expected)) {
            $message = sprintf(
              '%sexpected %s%s, actual %s',

              !empty($message) ? $message . ' ' : '',
              $expected,
              ($delta != 0) ? ('+/- ' . $delta) : '',
              $actual
            );

            if (!($actual >= ($expected - $delta) && $actual <= ($expected + $delta))) {
                return $this->fail($message);
            }
        }

        else {
            $message = sprintf(
              '%sexpected %s, actual %s',

              !empty($message) ? $message . ' ' : '',
              $expected,
              $actual
            );

            if ($actual != $expected) {
                return $this->fail($message);
            }
        }
    }

    /**
    * Asserts that an object isn't null.
    *
    * @param  object
    * @param  string
    * @access public
    */
    function assertNotNull($object, $message = '') {
        $message = sprintf(
          '%sexpected NOT NULL, actual NULL',

          !empty($message) ? $message . ' ' : ''
        );

        if ($object === null) {
            return $this->fail($message);
        }
    }

    /**
    * Asserts that an object is null.
    *
    * @param  object
    * @param  string
    * @access public
    */
    function assertNull($object, $message = '') {
        $message = sprintf(
          '%sexpected NULL, actual NOT NULL',

          !empty($message) ? $message . ' ' : ''
        );

        if ($object !== null) {
            return $this->fail($message);
        }
    }

    /**
    * Asserts that two objects refer to the same object.
    * This requires the Zend Engine 2 (to work properly).
    *
    * @param  object
    * @param  object
    * @param  string
    * @access public
    */
    function assertSame($expected, $actual, $message = '') {
        $message = sprintf(
          '%sexpected two variables to refer to the same object',

          !empty($message) ? $message . ' ' : ''
        );

        if ($actual !== $expected) {
            return $this->fail($message);
        }
    }

    /**
    * Asserts that two objects refer not to the same object.
    * This requires the Zend Engine 2 (to work properly).
    *
    * @param  object
    * @param  object
    * @param  string
    * @access public
    */
    function assertNotSame($expected, $actual, $message = '') {
        $message = sprintf(
          '%sexpected two variables to refer to different objects',

          !empty($message) ? $message . ' ' : ''
        );

        if ($actual === $expected) {
            return $this->fail($message);
        }
    }

    /**
    * Asserts that a condition is true.
    *
    * @param  boolean
    * @param  string
    * @access public
    */
    function assertTrue($condition, $message = '') {
        $message = sprintf(
          '%sexpected true, actual false',

          !empty($message) ? $message . ' ' : ''
        );

        if (!$condition) {
            return $this->fail($message);
        }
    }

    /**
    * Asserts that a condition is false.
    *
    * @param  boolean
    * @param  string
    * @access public
    */
    function assertFalse($condition, $message = '') {
        $message = sprintf(
          '%sexpected false, actual true',

          !empty($message) ? $message . ' ' : ''
        );

        if ($condition) {
            return $this->fail($message);
        }
    }

    /**
    * Asserts that a string matches a given
    * regular expression.
    *
    * @param string
    * @param string
    * @param string
    * @access public
    * @author Sébastien Hordeaux <marms@marms.com>
    */
    function assertRegExp($expected, $actual, $message = '') {
        $message = sprintf(
          '%sexpected %s, actual %s',

          !empty($message) ? $message . ' ' : '',
          $expected,
          $actual
        );

        if (!preg_match($expected, $actual)) {
            return $this->fail($message);
        }
    }
        
    /**
    * Asserts that a variable is of a given type.
    *
    * @param  string          $expected
    * @param  mixed           $actual
    * @param  optional string $message
    * @access public
    * @static
    */
    function assertType($expected, $actual, $message = '') {
        return $this->assertEquals(
          $expected,
          gettype($actual),
          $message
        );
    }

    /**
    * Converts a value to a string.
    *
    * @param  mixed   $value
    * @access private
    * @static
    */
    function _convertToString($value) {
        foreach ($value as $k => $v) {
            if (is_array($v)) {
                $value[$k] = $this->_convertToString($value[$k]);
            } else {
                settype($value[$k], 'string');
            }
        }

        return $value;
    }

    /**
    * @param  boolean $looselyTyped
    * @access public
    * @static
    */
    function setLooselyTyped($looselyTyped) {
        if (is_bool($looselyTyped)) {
            $this->_looselyTyped = $looselyTyped;
        }
    }

    /**
    * Fails a test with the given message.
    *
    * @param  string
    * @access protected
    * @abstract
    */
    function fail($message = '') { /* abstract */ }
}
?>
