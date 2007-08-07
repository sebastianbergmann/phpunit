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
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/Type.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

if (!class_exists('PHPUnit_Framework_Assert', FALSE)) {

/**
 * A set of assert methods.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.0.0
 * @static
 */
class PHPUnit_Framework_Assert
{
    /**
     * Protect constructor since it is a static only class.
     *
     * @access protected
     */
    protected function __construct()
    {
    }

    /**
     * Asserts that an array has a specified key.
     *
     * @param  mixed  $key
     * @param  array  $array
     * @param  string $message
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function assertArrayHasKey($key, array $array, $message = '')
    {
        if (!(is_integer($key) || is_string($key))) {
            throw new InvalidArgumentException;
        }

        $constraint = new PHPUnit_Framework_Constraint_ArrayHasKey($key);

        self::assertThat($array, $constraint, $message);
    }

    /**
     * Asserts that an array does not have a specified key.
     *
     * @param  mixed  $key
     * @param  array  $array
     * @param  string $message
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function assertArrayNotHasKey($key, array $array, $message = '')
    {
        if (!(is_integer($key) || is_string($key))) {
            throw new InvalidArgumentException;
        }

        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_ArrayHasKey($key)
        );

        self::assertThat($array, $constraint, $message);
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
    public static function assertContains($needle, $haystack, $message = '')
    {
        if (is_array($haystack) ||
            is_object($haystack) && $haystack instanceof Iterator) {
            $constraint = new PHPUnit_Framework_Constraint_TraversableContains($needle);
        }

        else if (is_string($haystack)) {
            $constraint = new PHPUnit_Framework_Constraint_StringContains($needle);
        }

        else {
            throw new InvalidArgumentException;
        }

        self::assertThat($haystack, $constraint, $message);
    }

    /**
     * Asserts that a haystack that is stored in a static attribute of a class
     * or an attribute of an object contains a needle.
     *
     * @param  mixed   $needle
     * @param  string  $haystackAttributeName
     * @param  mixed   $haystackClassOrObject
     * @param  string  $message
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function assertAttributeContains($needle, $haystackAttributeName, $haystackClassOrObject, $message = '')
    {
        self::assertContains(
          $needle,
          self::readAttribute($haystackClassOrObject, $haystackAttributeName),
          $message
        );
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
    public static function assertNotContains($needle, $haystack, $message = '')
    {
        if (is_array($haystack) ||
            is_object($haystack) && $haystack instanceof Iterator) {
            $constraint = new PHPUnit_Framework_Constraint_Not(
              new PHPUnit_Framework_Constraint_TraversableContains($needle)
            );
        }

        else if (is_string($haystack)) {
            $constraint = new PHPUnit_Framework_Constraint_Not(
              new PHPUnit_Framework_Constraint_StringContains($needle)
            );
        }

        else {
            throw new InvalidArgumentException;
        }

        self::assertThat($haystack, $constraint, $message);
    }

    /**
     * Asserts that a haystack that is stored in a static attribute of a class
     * or an attribute of an object does not contain a needle.
     *
     * @param  mixed   $needle
     * @param  string  $haystackAttributeName
     * @param  mixed   $haystackClassOrObject
     * @param  string  $message
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function assertAttributeNotContains($needle, $haystackAttributeName, $haystackClassOrObject, $message = '')
    {
        self::assertNotContains(
          $needle,
          self::readAttribute($haystackClassOrObject, $haystackAttributeName),
          $message
        );
    }

    /**
     * Asserts that a haystack contains only values of a given type.
     *
     * @param  string  $type
     * @param  mixed   $haystack
     * @param  boolean $isNativeType
     * @param  string  $message
     * @access public
     * @static
     * @since  Method available since Release 3.1.4
     */
    public static function assertContainsOnly($type, $haystack, $isNativeType = TRUE, $message = '')
    {
        if (!(is_array($haystack) ||
            is_object($haystack) && $haystack instanceof Iterator)) {
            throw new InvalidArgumentException;
        }

        self::assertThat(
          $haystack,
          new PHPUnit_Framework_Constraint_TraversableContainsOnly(
            $type, $isNativeType
          ),
          $message
        );
    }

    /**
     * Asserts that a haystack that is stored in a static attribute of a class
     * or an attribute of an object contains only values of a given type.
     *
     * @param  string  $type
     * @param  string  $haystackAttributeName
     * @param  mixed   $haystackClassOrObject
     * @param  boolean $isNativeType
     * @param  string  $message
     * @access public
     * @static
     * @since  Method available since Release 3.1.4
     */
    public static function assertAttributeContainsOnly($type, $haystackAttributeName, $haystackClassOrObject, $isNativeType = TRUE, $message = '')
    {
        self::assertContainsOnly(
          $type,
          self::readAttribute($haystackClassOrObject, $haystackAttributeName),
          $isNativeType,
          $message
        );
    }

    /**
     * Asserts that a haystack does not contain only values of a given type.
     *
     * @param  string  $type
     * @param  mixed   $haystack
     * @param  boolean $isNativeType
     * @param  string  $message
     * @access public
     * @static
     * @since  Method available since Release 3.1.4
     */
    public static function assertNotContainsOnly($type, $haystack, $isNativeType = TRUE, $message = '')
    {
        if (!(is_array($haystack) ||
            is_object($haystack) && $haystack instanceof Iterator)) {
            throw new InvalidArgumentException;
        }

        self::assertThat(
          $haystack,
          new PHPUnit_Framework_Constraint_Not(
            new PHPUnit_Framework_Constraint_TraversableContainsOnly(
              $type, $isNativeType
            )
          ),
          $message
        );
    }

    /**
     * Asserts that a haystack that is stored in a static attribute of a class
     * or an attribute of an object does not contain only values of a given type.
     *
     * @param  string  $type
     * @param  string  $haystackAttributeName
     * @param  mixed   $haystackClassOrObject
     * @param  boolean $isNativeType
     * @param  string  $message
     * @access public
     * @static
     * @since  Method available since Release 3.1.4
     */
    public static function assertAttributeNotContainsOnly($type, $haystackAttributeName, $haystackClassOrObject, $isNativeType = TRUE, $message = '')
    {
        self::assertNotContainsOnly(
          $type,
          self::readAttribute($haystackClassOrObject, $haystackAttributeName),
          $isNativeType,
          $message
        );
    }

    /**
     * Asserts that two variables are equal.
     *
     * @param  mixed   $expected
     * @param  mixed   $actual
     * @param  string  $message
     * @param  float   $delta
     * @param  integer $maxDepth
     * @access public
     * @static
     */
    public static function assertEquals($expected, $actual, $message = '', $delta = 0, $maxDepth = 10)
    {
        $constraint = new PHPUnit_Framework_Constraint_IsEqual(
          $expected,
          $delta,
          $maxDepth
        );

        self::assertThat($actual, $constraint, $message);
    }

    /**
     * Asserts that a variable is equal to an attribute of an object.
     *
     * @param  mixed   $expected
     * @param  string  $actualAttributeName
     * @param  string  $actualClassOrObject
     * @param  string  $message
     * @param  float   $delta
     * @param  integer $maxDepth
     * @access public
     * @static
     */
    public static function assertAttributeEquals($expected, $actualAttributeName, $actualClassOrObject, $message = '', $delta = 0, $maxDepth = 10)
    {
        self::assertEquals(
          $expected,
          self::readAttribute($actualClassOrObject, $actualAttributeName),
          $message,
          $delta,
          $maxDepth
        );
    }

    /**
     * Asserts that two variables are not equal.
     *
     * @param  mixed   $expected
     * @param  mixed   $actual
     * @param  string  $message
     * @param  float   $delta
     * @param  integer $maxDepth
     * @access public
     * @static
     * @since  Method available since Release 2.3.0
     */
    public static function assertNotEquals($expected, $actual, $message = '', $delta = 0, $maxDepth = 10)
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_IsEqual(
            $expected,
            $delta,
            $maxDepth
          )
        );

        self::assertThat($actual, $constraint, $message);
    }

    /**
     * Asserts that a variable is not equal to an attribute of an object.
     *
     * @param  mixed   $expected
     * @param  string  $actualAttributeName
     * @param  string  $actualClassOrObject
     * @param  string  $message
     * @param  float   $delta
     * @param  integer $maxDepth
     * @access public
     * @static
     */
    public static function assertAttributeNotEquals($expected, $actualAttributeName, $actualClassOrObject, $message = '', $delta = 0, $maxDepth = 10)
    {
        self::assertNotEquals(
          $expected,
          self::readAttribute($actualClassOrObject, $actualAttributeName),
          $message,
          $delta,
          $maxDepth
        );
    }

    /**
     * Asserts that a value is greater than another value.
     *
     * @param  mixed   $expected
     * @param  mixed   $actual
     * @param  string  $message
     * @access public
     * @static
     * @since  Method available since Release 3.1.0
     */
    public static function assertGreaterThan($expected, $actual, $message = '')
    {
        self::assertThat($actual, self::greaterThan($expected), $message);
    }

    /**
     * Asserts that an attribute is greater than another value.
     *
     * @param  mixed   $expected
     * @param  string  $actualAttributeName
     * @param  string  $actualClassOrObject
     * @param  string  $message
     * @access public
     * @static
     * @since  Method available since Release 3.1.0
     */
    public static function assertAttributeGreaterThan($expected, $actualAttributeName, $actualClassOrObject, $message = '')
    {
        self::assertGreaterThan(
          $expected,
          self::readAttribute($actualClassOrObject, $actualAttributeName),
          $message
        );
    }

    /**
     * Asserts that a value is greater than or equal to another value.
     *
     * @param  mixed   $expected
     * @param  mixed   $actual
     * @param  string  $message
     * @access public
     * @static
     * @since  Method available since Release 3.1.0
     */
    public static function assertGreaterThanOrEqual($expected, $actual, $message = '')
    {
        self::assertThat($actual, self::greaterThanOrEqual($expected), $message);
    }

    /**
     * Asserts that an attribute is greater than or equal to another value.
     *
     * @param  mixed   $expected
     * @param  string  $actualAttributeName
     * @param  string  $actualClassOrObject
     * @param  string  $message
     * @access public
     * @static
     * @since  Method available since Release 3.1.0
     */
    public static function assertAttributeGreaterThanOrEqual($expected, $actualAttributeName, $actualClassOrObject, $message = '')
    {
        self::assertGreaterThanOrEqual(
          $expected,
          self::readAttribute($actualClassOrObject, $actualAttributeName),
          $message
        );
    }

    /**
     * Asserts that a value is smaller than another value.
     *
     * @param  mixed   $expected
     * @param  mixed   $actual
     * @param  string  $message
     * @access public
     * @static
     * @since  Method available since Release 3.1.0
     */
    public static function assertLessThan($expected, $actual, $message = '')
    {
        self::assertThat($actual, self::lessThan($expected), $message);
    }

    /**
     * Asserts that an attribute is smaller than another value.
     *
     * @param  mixed   $expected
     * @param  string  $actualAttributeName
     * @param  string  $actualClassOrObject
     * @param  string  $message
     * @access public
     * @static
     * @since  Method available since Release 3.1.0
     */
    public static function assertAttributeLessThan($expected, $actualAttributeName, $actualClassOrObject, $message = '')
    {
        self::assertLessThan(
          $expected,
          self::readAttribute($actualClassOrObject, $actualAttributeName),
          $message
        );
    }

    /**
     * Asserts that a value is smaller than or equal to another value.
     *
     * @param  mixed   $expected
     * @param  mixed   $actual
     * @param  string  $message
     * @access public
     * @static
     * @since  Method available since Release 3.1.0
     */
    public static function assertLessThanOrEqual($expected, $actual, $message = '')
    {
        self::assertThat($actual, self::lessThanOrEqual($expected), $message);
    }

    /**
     * Asserts that an attribute is smaller than or equal to another value.
     *
     * @param  mixed   $expected
     * @param  string  $actualAttributeName
     * @param  string  $actualClassOrObject
     * @param  string  $message
     * @access public
     * @static
     * @since  Method available since Release 3.1.0
     */
    public static function assertAttributeLessThanOrEqual($expected, $actualAttributeName, $actualClassOrObject, $message = '')
    {
        self::assertLessThanOrEqual(
          $expected,
          self::readAttribute($actualClassOrObject, $actualAttributeName),
          $message
        );
    }

    /**
     * Asserts that a file exists.
     *
     * @param  string $filename
     * @param  string $message
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function assertFileExists($filename, $message = '')
    {
        if (!is_string($filename)) {
            throw new InvalidArgumentException;
        }

        $constraint = new PHPUnit_Framework_Constraint_FileExists;

        self::assertThat($filename, $constraint, $message);
    }

    /**
     * Asserts that a file does not exist.
     *
     * @param  string $filename
     * @param  string $message
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function assertFileNotExists($filename, $message = '')
    {
        if (!is_string($filename)) {
            throw new InvalidArgumentException;
        }

        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_FileExists
        );

        self::assertThat($filename, $constraint, $message);
    }

    /**
     * Asserts that a condition is true.
     *
     * @param  boolean $condition
     * @param  string  $message
     * @throws PHPUnit_Framework_AssertionFailedError
     * @access public
     * @static
     */
    public static function assertTrue($condition, $message = '')
    {
        if ($condition !== TRUE) {
            throw new PHPUnit_Framework_AssertionFailedError(
              sprintf(
                '%sFailed asserting that %s is true.',

                $message != '' ? $message . "\n" : '',
                PHPUnit_Util_Type::toString($condition)
              )
            );
        }
    }

    /**
     * Asserts that a condition is false.
     *
     * @param  boolean  $condition
     * @param  string   $message
     * @throws PHPUnit_Framework_AssertionFailedError
     * @access public
     * @static
     */
    public static function assertFalse($condition, $message = '')
    {
        if ($condition !== FALSE) {
            throw new PHPUnit_Framework_AssertionFailedError(
              sprintf(
                '%sFailed asserting that %s is false.',

                $message != '' ? $message . "\n" : '',
                PHPUnit_Util_Type::toString($condition)
              )
            );
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
    public static function assertNotNull($actual, $message = '')
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_IsIdentical(NULL)
        );

        self::assertThat($actual, $constraint, $message);
    }

    /**
     * Asserts that a variable is NULL.
     *
     * @param  mixed  $actual
     * @param  string $message
     * @access public
     * @static
     */
    public static function assertNull($actual, $message = '')
    {
        $constraint = new PHPUnit_Framework_Constraint_IsIdentical(NULL);

        self::assertThat($actual, $constraint, $message);
    }

    /**
     * Asserts that a class has a specified attribute.
     *
     * @param  string $attributeName
     * @param  string $className
     * @param  string $message
     * @access public
     * @static
     * @since  Method available since Release 3.1.0
     */
    public static function assertClassHasAttribute($attributeName, $className, $message = '')
    {
        if (!is_string($attributeName) || !is_string($className) || !class_exists($className, FALSE)) {
            throw new InvalidArgumentException;
        }

        $constraint = new PHPUnit_Framework_Constraint_ClassHasAttribute($attributeName);

        self::assertThat($className, $constraint, $message);
    }

    /**
     * Asserts that a class does not have a specified attribute.
     *
     * @param  string $attributeName
     * @param  string $className
     * @param  string $message
     * @access public
     * @static
     * @since  Method available since Release 3.1.0
     */
    public static function assertClassNotHasAttribute($attributeName, $className, $message = '')
    {
        if (!is_string($attributeName) || !is_string($className) || !class_exists($className, FALSE)) {
            throw new InvalidArgumentException;
        }

        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_ClassHasAttribute($attributeName)
        );

        self::assertThat($className, $constraint, $message);
    }

    /**
     * Asserts that a class has a specified static attribute.
     *
     * @param  string $attributeName
     * @param  string $className
     * @param  string $message
     * @access public
     * @static
     * @since  Method available since Release 3.1.0
     */
    public static function assertClassHasStaticAttribute($attributeName, $className, $message = '')
    {
        if (!is_string($attributeName) || !is_string($className) || !class_exists($className, FALSE)) {
            throw new InvalidArgumentException;
        }

        $constraint = new PHPUnit_Framework_Constraint_ClassHasStaticAttribute($attributeName);

        self::assertThat($className, $constraint, $message);
    }

    /**
     * Asserts that a class does not have a specified static attribute.
     *
     * @param  string $attributeName
     * @param  string $className
     * @param  string $message
     * @access public
     * @static
     * @since  Method available since Release 3.1.0
     */
    public static function assertClassNotHasStaticAttribute($attributeName, $className, $message = '')
    {
        if (!is_string($attributeName) || !is_string($className) || !class_exists($className, FALSE)) {
            throw new InvalidArgumentException;
        }

        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_ClassHasStaticAttribute($attributeName)
        );

        self::assertThat($className, $constraint, $message);
    }

    /**
     * Asserts that an object has a specified attribute.
     *
     * @param  string $attributeName
     * @param  object $object
     * @param  string $message
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function assertObjectHasAttribute($attributeName, $object, $message = '')
    {
        if (!is_string($attributeName) || !is_object($object)) {
            throw new InvalidArgumentException;
        }

        $constraint = new PHPUnit_Framework_Constraint_ObjectHasAttribute($attributeName);

        self::assertThat($object, $constraint, $message);
    }

    /**
     * Asserts that an object does not have a specified attribute.
     *
     * @param  string $attributeName
     * @param  object $object
     * @param  string $message
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function assertObjectNotHasAttribute($attributeName, $object, $message = '')
    {
        if (!is_string($attributeName) || !is_object($object)) {
            throw new InvalidArgumentException;
        }

        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_ObjectHasAttribute($attributeName)
        );

        self::assertThat($object, $constraint, $message);
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
    public static function assertSame($expected, $actual, $message = '')
    {
        if (is_bool($expected) && is_bool($actual)) {
            self::assertEquals($expected, $actual);
        } else {
            $constraint = new PHPUnit_Framework_Constraint_IsIdentical($expected);

            self::assertThat($actual, $constraint, $message);
        }
    }

    /**
     * Asserts that a variable and an attribute of an object have the same type
     * and value.
     *
     * @param  mixed  $expected
     * @param  string $actualAttributeName
     * @param  object $actualClassOrObject
     * @param  string $message
     * @access public
     * @static
     */
    public static function assertAttributeSame($expected, $actualAttributeName, $actualClassOrObject, $message = '')
    {
        self::assertSame(
          $expected,
          self::readAttribute($actualClassOrObject, $actualAttributeName),
          $message
        );
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
    public static function assertNotSame($expected, $actual, $message = '')
    {
        if (is_bool($expected) && is_bool($actual)) {
            self::assertNotEquals($expected, $actual);
        } else {
            $constraint = new PHPUnit_Framework_Constraint_Not(
              new PHPUnit_Framework_Constraint_IsIdentical($expected)
            );

            self::assertThat($actual, $constraint, $message);
        }
    }

    /**
     * Asserts that a variable and an attribute of an object do not have the
     * same type and value.
     *
     * @param  mixed  $expected
     * @param  string $actualAttributeName
     * @param  object $actualClassOrObject
     * @param  string $message
     * @access public
     * @static
     */
    public static function assertAttributeNotSame($expected, $actualAttributeName, $actualClassOrObject, $message = '')
    {
        self::assertNotSame(
          $expected,
          self::readAttribute($actualClassOrObject, $actualAttributeName),
          $message
        );
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
    public static function assertType($expected, $actual, $message = '')
    {
        if (is_string($expected)) {
            if (class_exists($expected, FALSE) ||
                interface_exists($expected, FALSE)) {
                $constraint = new PHPUnit_Framework_Constraint_IsInstanceOf(
                  $expected
                );
            } else {
                $constraint = new PHPUnit_Framework_Constraint_IsType($expected);
            }
        } else {
            throw new InvalidArgumentException;
        }

        self::assertThat($actual, $constraint, $message);
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
    public static function assertNotType($expected, $actual, $message = '')
    {
        if (is_string($expected)) {
            if (class_exists($expected, FALSE) ||
                interface_exists($expected, FALSE)) {
                $constraint = new PHPUnit_Framework_Constraint_Not(
                  new PHPUnit_Framework_Constraint_IsInstanceOf($expected)
                );
            } else {
                $constraint = new PHPUnit_Framework_Constraint_Not(
                  new PHPUnit_Framework_Constraint_IsType($expected)
                );
            }
        } else {
            throw new InvalidArgumentException;
        }

        self::assertThat($actual, $constraint, $message);
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
    public static function assertRegExp($pattern, $string, $message = '')
    {
        if (!is_string($pattern) || !is_string($string)) {
            throw new InvalidArgumentException;
        }

        $constraint = new PHPUnit_Framework_Constraint_PCREMatch($pattern);

        self::assertThat($string, $constraint, $message);
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
    public static function assertNotRegExp($pattern, $string, $message = '')
    {
        if (!is_string($pattern) || !is_string($string)) {
            throw new InvalidArgumentException;
        }

        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_PCREMatch($pattern)
        );

        self::assertThat($string, $constraint, $message);
    }

    /**
     * Asserts that two XML files are equal.
     *
     * @param  string $expectedFile
     * @param  string $actualFile
     * @param  string $message
     * @access public
     * @static
     * @since  Method available since Release 3.1.0
     */
    public static function assertXmlFileEqualsXmlFile($expectedFile, $actualFile, $message = '')
    {
        self::assertFileExists($expectedFile);
        self::assertFileExists($actualFile);

        $expected = new DOMDocument;
        $expected->load($expectedFile);

        $actual = new DOMDocument;
        $actual->load($actualFile);

        self::assertEquals($expected, $actual, $message);
    }

    /**
     * Asserts that two XML files are not equal.
     *
     * @param  string $expectedFile
     * @param  string $actualFile
     * @param  string $message
     * @access public
     * @static
     * @since  Method available since Release 3.1.0
     */
    public static function assertXmlFileNotEqualsXmlFile($expectedFile, $actualFile, $message = '')
    {
        self::assertFileExists($expectedFile);
        self::assertFileExists($actualFile);

        $expected = new DOMDocument;
        $expected->load($expectedFile);

        $actual = new DOMDocument;
        $actual->load($actualFile);

        self::assertNotEquals($expected, $actual, $message);
    }

    /**
     * Asserts that two XML documents are equal.
     *
     * @param  string $expectedXml
     * @param  string $actualXml
     * @param  string $message
     * @access public
     * @static
     * @since  Method available since Release 3.1.0
     */
    public static function assertXmlStringEqualsXmlString($expectedXml, $actualXml, $message = '')
    {
        $expected = new DOMDocument;
        $expected->loadXML($expectedXml);

        $actual = new DOMDocument;
        $actual->loadXML($actualXml);

        self::assertEquals($expected, $actual, $message);
    }

    /**
     * Asserts that two XML documents are not equal.
     *
     * @param  string $expectedXml
     * @param  string $actualXml
     * @param  string $message
     * @access public
     * @static
     * @since  Method available since Release 3.1.0
     */
    public static function assertXmlStringNotEqualsXmlString($expectedXml, $actualXml, $message = '')
    {
        $expected = new DOMDocument;
        $expected->loadXML($expectedXml);

        $actual = new DOMDocument;
        $actual->loadXML($actualXml);

        self::assertNotEquals($expected, $actual, $message);
    }

    /**
     *
     *
     * @param  mixed                        $value
     * @param  PHPUnit_Framework_Constraint $constraint
     * @param  string                       $message
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function assertThat($value, PHPUnit_Framework_Constraint $constraint, $message = '')
    {
        if (!$constraint->evaluate($value)) {
            $constraint->fail($value, $message);
        }
    }

    /**
     * Logical AND.
     *
     * @return PHPUnit_Framework_Constraint_And
     * @access public
     * @since  Method available since Release 3.0.0
     * @static
     */
    public static function logicalAnd()
    {
        $constraints = func_get_args();

        $constraint = new PHPUnit_Framework_Constraint_And;
        $constraint->setConstraints($constraints);

        return $constraint;
    }

    /**
     * Logical OR.
     *
     * @return PHPUnit_Framework_Constraint_Or
     * @access public
     * @since  Method available since Release 3.0.0
     * @static
     */
    public static function logicalOr()
    {
        $constraints = func_get_args();

        $constraint = new PHPUnit_Framework_Constraint_Or;
        $constraint->setConstraints($constraints);

        return $constraint;
    }

    /**
     * Logical NOT.
     *
     * @param  PHPUnit_Framework_Constraint $constraint
     * @return PHPUnit_Framework_Constraint_Not
     * @access public
     * @since  Method available since Release 3.0.0
     * @static
     */
    public static function logicalNot(PHPUnit_Framework_Constraint $constraint)
    {
        return new PHPUnit_Framework_Constraint_Not($constraint);
    }

    /**
     * Logical XOR.
     *
     * @return PHPUnit_Framework_Constraint_Xor
     * @access public
     * @since  Method available since Release 3.0.0
     * @static
     */
    public static function logicalXor()
    {
        $constraints = func_get_args();

        $constraint = new PHPUnit_Framework_Constraint_Xor;
        $constraint->setConstraints($constraints);

        return $constraint;
    }

    /**
     *
     *
     * @return PHPUnit_Framework_Constraint_IsAnything
     * @access public
     * @since  Method available since Release 3.0.0
     * @static
     */
    public static function anything()
    {
        return new PHPUnit_Framework_Constraint_IsAnything;
    }

    /**
     * 
     *
     * @param  PHPUnit_Framework_Constraint $constraint
     * @param  string                       $attributeName
     * @return PHPUnit_Framework_Constraint_Attribute
     * @access public
     * @since  Method available since Release 3.1.0
     * @static
     */
    public static function attribute(PHPUnit_Framework_Constraint $constraint, $attributeName)
    {
        return new PHPUnit_Framework_Constraint_Attribute(
          $constraint, $attributeName
        );
    }

    /**
     *
     *
     * @param  mixed $value
     * @return PHPUnit_Framework_Constraint_TraversableContains
     * @access public
     * @since  Method available since Release 3.0.0
     * @static
     */
    public static function contains($value)
    {
        return new PHPUnit_Framework_Constraint_TraversableContains($value);
    }

    /**
     *
     *
     * @param  string $type
     * @return PHPUnit_Framework_Constraint_TraversableContainsOnly
     * @access public
     * @since  Method available since Release 3.1.4
     * @static
     */
    public static function containsOnly($type)
    {
        return new PHPUnit_Framework_Constraint_TraversableContainsOnly($type);
    }

    /**
     *
     *
     * @param  mixed $key
     * @return PHPUnit_Framework_Constraint_ArrayHasKey
     * @access public
     * @since  Method available since Release 3.0.0
     * @static
     */
    public static function arrayHasKey($key)
    {
        return new PHPUnit_Framework_Constraint_ArrayHasKey($key);
    }

    /**
     *
     *
     * @param  mixed   $value
     * @param  float   $delta
     * @param  integer $maxDepth
     * @return PHPUnit_Framework_Constraint_IsEqual
     * @access public
     * @since  Method available since Release 3.0.0
     * @static
     */
    public static function equalTo($value, $delta = 0, $maxDepth = 10)
    {
        return new PHPUnit_Framework_Constraint_IsEqual($value, $delta, $maxDepth);
    }

    /**
     *
     *
     * @param  string  $attributeName
     * @param  mixed   $value
     * @param  float   $delta
     * @param  integer $maxDepth
     * @return PHPUnit_Framework_Constraint_Attribute
     * @access public
     * @since  Method available since Release 3.1.0
     * @static
     */
    public static function attributeEqualTo($attributeName, $value, $delta = 0, $maxDepth = 10)
    {
        return new PHPUnit_Framework_Constraint_Attribute(
          new PHPUnit_Framework_Constraint_IsEqual($value, $delta, $maxDepth),
          $attributeName
        );
    }

    /**
     *
     *
     * @return PHPUnit_Framework_Constraint_FileExists
     * @access public
     * @since  Method available since Release 3.0.0
     * @static
     */
    public static function fileExists()
    {
        return new PHPUnit_Framework_Constraint_FileExists;
    }

    /**
     *
     *
     * @param  mixed $value
     * @return PHPUnit_Framework_Constraint_GreaterThan
     * @access public
     * @since  Method available since Release 3.0.0
     * @static
     */
    public static function greaterThan($value)
    {
        return new PHPUnit_Framework_Constraint_GreaterThan($value);
    }

    /**
     *
     *
     * @param  mixed $value
     * @return PHPUnit_Framework_Constraint_Or
     * @access public
     * @since  Method available since Release 3.1.0
     * @static
     */
    public static function greaterThanOrEqual($value)
    {
        return self::logicalOr(
          new PHPUnit_Framework_Constraint_IsEqual($value),
          new PHPUnit_Framework_Constraint_GreaterThan($value)
        );
    }

    /**
     *
     *
     * @param  string $attributeName
     * @return PHPUnit_Framework_Constraint_ClassHasAttribute
     * @access public
     * @since  Method available since Release 3.1.0
     * @static
     */
    public static function classHasAttribute($attributeName)
    {
        return new PHPUnit_Framework_Constraint_ClassHasAttribute($attributeName);
    }

    /**
     *
     *
     * @param  string $attributeName
     * @return PHPUnit_Framework_Constraint_ClassHasStaticAttribute
     * @access public
     * @since  Method available since Release 3.1.0
     * @static
     */
    public static function classHasStaticAttribute($attributeName)
    {
        return new PHPUnit_Framework_Constraint_ClassHasStaticAttribute($attributeName);
    }

    /**
     *
     *
     * @param  string $attributeName
     * @return PHPUnit_Framework_Constraint_ObjectHasAttribute
     * @access public
     * @since  Method available since Release 3.0.0
     * @static
     */
    public static function objectHasAttribute($attributeName)
    {
        return new PHPUnit_Framework_Constraint_ObjectHasAttribute($attributeName);
    }

    /**
     *
     *
     * @param  mixed $value
     * @return PHPUnit_Framework_Constraint_IsIdentical
     * @access public
     * @since  Method available since Release 3.0.0
     * @static
     */
    public static function identicalTo($value)
    {
        return new PHPUnit_Framework_Constraint_IsIdentical($value);
    }

    /**
     *
     *
     * @param  string $className
     * @return PHPUnit_Framework_Constraint_IsInstanceOf
     * @access public
     * @since  Method available since Release 3.0.0
     * @static
     */
    public static function isInstanceOf($className)
    {
        return new PHPUnit_Framework_Constraint_IsInstanceOf($className);
    }

    /**
     *
     *
     * @param  string $type
     * @return PHPUnit_Framework_Constraint_IsType
     * @access public
     * @since  Method available since Release 3.0.0
     * @static
     */
    public static function isType($type)
    {
        return new PHPUnit_Framework_Constraint_IsType($type);
    }

    /**
     *
     *
     * @param  mixed $value
     * @return PHPUnit_Framework_Constraint_LessThan
     * @access public
     * @since  Method available since Release 3.0.0
     * @static
     */
    public static function lessThan($value)
    {
        return new PHPUnit_Framework_Constraint_LessThan($value);
    }

    /**
     *
     *
     * @param  mixed $value
     * @return PHPUnit_Framework_Constraint_Or
     * @access public
     * @since  Method available since Release 3.1.0
     * @static
     */
    public static function lessThanOrEqual($value)
    {
        return self::logicalOr(
          new PHPUnit_Framework_Constraint_IsEqual($value),
          new PHPUnit_Framework_Constraint_LessThan($value)
        );
    }

    /**
     *
     *
     * @param  string $pattern
     * @return PHPUnit_Framework_Constraint_PCREMatch
     * @access public
     * @since  Method available since Release 3.0.0
     * @static
     */
    public static function matchesRegularExpression($pattern)
    {
        return new PHPUnit_Framework_Constraint_PCREMatch($pattern);
    }

    /**
     *
     *
     * @param  string  $string
     * @param  boolean $case
     * @return PHPUnit_Framework_Constraint_StringContains
     * @access public
     * @since  Method available since Release 3.0.0
     * @static
     */
    public static function stringContains($string, $case = TRUE)
    {
        return new PHPUnit_Framework_Constraint_StringContains($string, $case);
    }


    /**
     * Fails a test with the given message.
     *
     * @param  string $message
     * @throws PHPUnit_Framework_AssertionFailedError
     * @access public
     * @static
     */
    public static function fail($message = '')
    {
        throw new PHPUnit_Framework_AssertionFailedError($message);
    }

    /**
     * Returns the value of an attribute of a class or an object.
     * This also works for attributes that are declared protected or private.
     *
     * @param  mixed   $classOrObject
     * @param  string  $attributeName
     * @return mixed
     * @throws InvalidArgumentException
     * @access protected
     * @static
     */
    public static function readAttribute($classOrObject, $attributeName)
    {
        if (!is_string($attributeName)) {
            throw new InvalidArgumentException;
        }

        if (is_string($classOrObject)) {
            if (!class_exists($classOrObject, FALSE)) {
                throw new InvalidArgumentException;
            }

            return self::getStaticAttribute(
              $classOrObject,
              $attributeName
            );
        }
        
        else if (is_object($classOrObject)) {
            return self::getObjectAttribute(
              $classOrObject,
              $attributeName
            );
        }

        else {
            throw new InvalidArgumentException;
        }
    }

    /**
     * Returns the value of a static attribute.
     * This also works for attributes that are declared protected or private.
     *
     * @param  string  $className
     * @param  string  $attributeName
     * @return mixed
     * @throws InvalidArgumentException
     * @access public
     * @static
     * @since  Method available since Release 3.1.0
     */
    public static function getStaticAttribute($className, $attributeName)
    {
        if (!is_string($className) || !class_exists($className, FALSE) || !is_string($attributeName)) {
            throw new InvalidArgumentException;
        }

        $class      = new ReflectionClass($className);
        $attributes = $class->getStaticProperties();

        if (isset($attributes[$attributeName])) {
            return $attributes[$attributeName];
        }

        if (version_compare(PHP_VERSION, '5.2', '<')) {
            $protectedName = "\0*\0" . $attributeName;
        } else {
            $protectedName = '*' . $attributeName;
        }

        if (isset($attributes[$protectedName])) {
            return $attributes[$protectedName];
        }

        $classes = PHPUnit_Util_Class::getHierarchy($className);

        foreach ($classes as $class) {
            $privateName = sprintf(
              "\0%s\0%s",

              $class,
              $attributeName
            );

            if (isset($attributes[$privateName])) {
                return $attributes[$privateName];
            }
        }

        throw new RuntimeException(
          sprintf(
            'Attribute "%s" not found in class.',

            $attributeName
          )
        );
    }

    /**
     * Returns the value of an object's attribute.
     * This also works for attributes that are declared protected or private.
     *
     * @param  object  $object
     * @param  string  $attributeName
     * @return mixed
     * @throws InvalidArgumentException
     * @access public
     * @static
     * @since  Method available since Release 3.1.0
     */
    public static function getObjectAttribute($object, $attributeName)
    {
        if (!is_object($object) || !is_string($attributeName)) {
            throw new InvalidArgumentException;
        }

        self::assertObjectHasAttribute($attributeName, $object);

        if (property_exists($object, $attributeName)) {
            return $object->$attributeName;
        } else {
            $array         = (array) $object;
            $protectedName = "\0*\0" . $attributeName;

            if (array_key_exists($protectedName, $array)) {
                return $array[$protectedName];
            } else {
                $classes = PHPUnit_Util_Class::getHierarchy(get_class($object));

                foreach ($classes as $class) {
                    $privateName = sprintf(
                      "\0%s\0%s",

                      $class,
                      $attributeName
                    );

                    if (array_key_exists($privateName, $array)) {
                        return $array[$privateName];
                    }
                }
            }
        }

        throw new RuntimeException(
          sprintf(
            'Attribute "%s" not found in object.',

            $attributeName
          )
        );
    }

    /**
     * Mark the test as incomplete.
     *
     * @param  string  $message
     * @throws PHPUnit_Framework_IncompleteTestError
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function markTestIncomplete($message = '')
    {
        throw new PHPUnit_Framework_IncompleteTestError($message);
    }

    /**
     * Mark the test as skipped.
     *
     * @param  string  $message
     * @throws PHPUnit_Framework_SkippedTestError
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function markTestSkipped($message = '')
    {
        throw new PHPUnit_Framework_SkippedTestError($message);
    }
}

}
?>
