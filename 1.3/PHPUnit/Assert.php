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
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://pear.php.net/package/PHPUnit
 * @since      File available since Release 1.0.0
 */

/**
 * A set of assert methods.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PHPUnit
 * @since      Class available since Release 1.0.0
 */
class PHPUnit_Assert {
    /**
     * @var    boolean
     * @access private
     */
    var $_looselyTyped = FALSE;

    /**
     * Asserts that a haystack contains a needle.
     *
     * @param  mixed
     * @param  mixed
     * @param  string
     * @access public
     * @since  Method available since Release 1.1.0
     */
    function assertContains($needle, $haystack, $message = '') {
        if (is_string($needle) && is_string($haystack)) {
            $this->assertTrue(strpos($haystack, $needle) !== FALSE, $message);
        }

        else if (is_array($haystack) && !is_object($needle)) {
            $this->assertTrue(in_array($needle, $haystack), $message);
        }

        else {
            $this->fail('Unsupported parameter passed to assertContains().');
        }
    }

    /**
     * Asserts that a haystack does not contain a needle.
     *
     * @param  mixed
     * @param  mixed
     * @param  string
     * @access public
     * @since  Method available since Release 1.1.0
     */
    function assertNotContains($needle, $haystack, $message = '') {
        if (is_string($needle) && is_string($haystack)) {
            $this->assertFalse(strpos($haystack, $needle) !== FALSE, $message);
        }

        else if (is_array($haystack) && !is_object($needle)) {
            $this->assertFalse(in_array($needle, $haystack), $message);
        }

        else {
            $this->fail('Unsupported parameter passed to assertNotContains().');
        }
    }

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

            if ($actual !== $expected) {
                return $this->fail($message);
            }
        }
    }

    /**
     * Asserts that two variables reference the same object.
     * This requires the Zend Engine 2 to work.
     *
     * @param  object
     * @param  object
     * @param  string
     * @access public
     * @deprecated
     */
    function assertSame($expected, $actual, $message = '') {
        if (!version_compare(phpversion(), '5.0.0', '>=')) {
            $this->fail('assertSame() only works with PHP >= 5.0.0.');
        }

        if ((is_object($expected) || is_null($expected)) &&
            (is_object($actual)   || is_null($actual))) {
            $message = sprintf(
              '%sexpected two variables to reference the same object',

              !empty($message) ? $message . ' ' : ''
            );

            if ($expected !== $actual) {
                return $this->fail($message);
            }
        } else {
            $this->fail('Unsupported parameter passed to assertSame().');
        }
    }

    /**
     * Asserts that two variables do not reference the same object.
     * This requires the Zend Engine 2 to work.
     *
     * @param  object
     * @param  object
     * @param  string
     * @access public
     * @deprecated
     */
    function assertNotSame($expected, $actual, $message = '') {
        if (!version_compare(phpversion(), '5.0.0', '>=')) {
            $this->fail('assertNotSame() only works with PHP >= 5.0.0.');
        }

        if ((is_object($expected) || is_null($expected)) &&
            (is_object($actual)   || is_null($actual))) {
            $message = sprintf(
              '%sexpected two variables to reference different objects',

              !empty($message) ? $message . ' ' : ''
            );

            if ($expected === $actual) {
                return $this->fail($message);
            }
        } else {
            $this->fail('Unsupported parameter passed to assertNotSame().');
        }
    }

    /**
     * Asserts that a variable is not NULL.
     *
     * @param  mixed
     * @param  string
     * @access public
     */
    function assertNotNull($actual, $message = '') {
        $message = sprintf(
          '%sexpected NOT NULL, actual NULL',

          !empty($message) ? $message . ' ' : ''
        );

        if (is_null($actual)) {
            return $this->fail($message);
        }
    }

    /**
     * Asserts that a variable is NULL.
     *
     * @param  mixed
     * @param  string
     * @access public
     */
    function assertNull($actual, $message = '') {
        $message = sprintf(
          '%sexpected NULL, actual NOT NULL',

          !empty($message) ? $message . ' ' : ''
        );

        if (!is_null($actual)) {
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
          '%sexpected TRUE, actual FALSE',

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
          '%sexpected FALSE, actual TRUE',

          !empty($message) ? $message . ' ' : ''
        );

        if ($condition) {
            return $this->fail($message);
        }
    }

    /**
     * Asserts that a string matches a given regular expression.
     *
     * @param  string
     * @param  string
     * @param  string
     * @access public
     */
    function assertRegExp($pattern, $string, $message = '') {
        $message = sprintf(
          '%s"%s" does not match pattern "%s"',

          !empty($message) ? $message . ' ' : '',
          $string,
          $pattern
        );

        if (!preg_match($pattern, $string)) {
            return $this->fail($message);
        }
    }

    /**
     * Asserts that a string does not match a given regular expression.
     *
     * @param  string
     * @param  string
     * @param  string
     * @access public
     * @since  Method available since Release 1.1.0
     */
    function assertNotRegExp($pattern, $string, $message = '') {
        $message = sprintf(
          '%s"%s" matches pattern "%s"',

          !empty($message) ? $message . ' ' : '',
          $string,
          $pattern
        );

        if (preg_match($pattern, $string)) {
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

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
?>
