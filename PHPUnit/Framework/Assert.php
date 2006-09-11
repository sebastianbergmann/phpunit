<?php
/**
 * PHPUnit
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
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Extensions/MockObject/Mock.php';
require_once 'PHPUnit/Extensions/MockObject/Matcher/InvokedAtLeastOnce.php';
require_once 'PHPUnit/Extensions/MockObject/Matcher/InvokedAtIndex.php';
require_once 'PHPUnit/Extensions/MockObject/Matcher/InvokedCount.php';
require_once 'PHPUnit/Extensions/MockObject/Stub/ConsecutiveCalls.php';
require_once 'PHPUnit/Extensions/MockObject/Stub/Return.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

if (!class_exists('PHPUnit_Framework_Assert')) {

/**
 * A set of assert methods.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
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
    public static function assertArrayHasKey($key, Array $array, $message = '')
    {
        if (!(is_integer($key) || is_string($key))) {
            throw new InvalidArgumentException;
        }

        $constraint = new PHPUnit_Framework_Constraint_ArrayHasKey($key);

        if (!$constraint->evaluate($array)) {
            self::failConstraint($constraint, $array, $message);
        }
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
    public static function assertArrayNotHasKey($key, Array $array, $message = '')
    {
        if (!(is_integer($key) || is_string($key))) {
            throw new InvalidArgumentException;
        }

        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_ArrayHasKey($key)
        );

        if (!$constraint->evaluate($array)) {
            self::failConstraint($constraint, $array, $message);
        }
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

        if (!$constraint->evaluate($haystack)) {
            self::failConstraint($constraint, $haystack, $message);
        }
    }

    /**
     * Asserts that a haystack that is stored in an attribute of an object
     * contains a needle.
     *
     * @param  mixed   $needle
     * @param  string  $haystackAttributeName
     * @param  object  $haystackObject
     * @param  string  $message
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function assertAttributeContains($needle, $haystackAttributeName, $haystackObject, $message = '')
    {
        if (!is_string($haystackAttributeName) || !is_object($haystackObject)) {
            throw new InvalidArgumentException;
        }

        self::assertContains(
          $needle,
          self::getAttribute($haystackObject, $haystackAttributeName),
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

        if (!$constraint->evaluate($haystack)) {
            self::failConstraint($constraint, $haystack, $message);
        }
    }

    /**
     * Asserts that a haystack that is stored in an attribute of an object
     * does not contain a needle.
     *
     * @param  mixed   $needle
     * @param  string  $haystackAttributeName
     * @param  object  $haystackObject
     * @param  string  $message
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function assertAttributeNotContains($needle, $haystackAttributeName, $haystackObject, $message = '')
    {
        if (!is_string($haystackAttributeName) || !is_object($haystackObject)) {
            throw new InvalidArgumentException;
        }

        self::assertNotContains(
          $needle,
          self::getAttribute($haystackObject, $haystackAttributeName),
          $message
        );
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
    public static function assertEquals($expected, $actual, $message = '', $delta = 0)
    {
        $constraint = new PHPUnit_Framework_Constraint_IsEqual($expected, $delta);

        if (!$constraint->evaluate($actual)) {
            self::failConstraint($constraint, $actual, $message);
        }
    }

    /**
     * Asserts that a variable is equal to an attribute of an object.
     *
     * @param  mixed  $expected
     * @param  string $actualAttributeName
     * @param  string $actualObject
     * @param  string $message
     * @param  mixed  $delta
     * @access public
     * @static
     */
    public static function assertAttributeEquals($expected, $actualAttributeName, $actualObject, $message = '', $delta = 0)
    {
        if (!is_string($actualAttributeName) || !is_object($actualObject)) {
            throw new InvalidArgumentException;
        }

        self::assertEquals(
          $expected,
          self::getAttribute($actualObject, $actualAttributeName),
          $message,
          $delta
        );
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
    public static function assertNotEquals($expected, $actual, $message = '', $delta = 0)
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_IsEqual($expected, $delta)
        );

        if (!$constraint->evaluate($actual)) {
            self::failConstraint($constraint, $actual, $message);
        }
    }

    /**
     * Asserts that a variable is not equal to an attribute of an object.
     *
     * @param  mixed  $expected
     * @param  string $actualAttributeName
     * @param  string $actualObject
     * @param  string $message
     * @param  mixed  $delta
     * @access public
     * @static
     */
    public static function assertAttributeNotEquals($expected, $actualAttributeName, $actualObject, $message = '', $delta = 0)
    {
        if (!is_string($actualAttributeName) || !is_object($actualObject)) {
            throw new InvalidArgumentException;
        }

        self::assertNotEquals(
          $expected,
          self::getAttribute($actualObject, $actualAttributeName),
          $message,
          $delta
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

        if (!$constraint->evaluate($filename)) {
            self::failConstraint($constraint, $filename, $message);
        }
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

        if (!$constraint->evaluate($filename)) {
            self::failConstraint($constraint, $filename, $message);
        }
    }

    /**
     * Asserts that a condition is true.
     *
     * @param  boolean $condition
     * @param  string  $message
     * @throws PHPUnit_Framework_ComparisonFailure
     * @throws InvalidArgumentException
     * @access public
     * @static
     */
    public static function assertTrue($condition, $message = '')
    {
        if (!is_bool($condition)) {
            throw new InvalidArgumentException;
        }

        $constraint = new PHPUnit_Framework_Constraint_IsIdentical(TRUE);

        if (!$constraint->evaluate($condition)) {
            self::failConstraint($constraint, $condition, $message);
        }
    }

    /**
     * Asserts that a condition is false.
     *
     * @param  boolean  $condition
     * @param  string   $message
     * @throws PHPUnit_Framework_ComparisonFailure
     * @throws InvalidArgumentException
     * @access public
     * @static
     */
    public static function assertFalse($condition, $message = '')
    {
        if (!is_bool($condition)) {
            throw new InvalidArgumentException;
        }

        $constraint = new PHPUnit_Framework_Constraint_IsIdentical(FALSE);

        if (!$constraint->evaluate($condition)) {
            self::failConstraint($constraint, $condition, $message);
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

        if (!$constraint->evaluate($actual)) {
            self::failConstraint($constraint, $actual, $message);
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
    public static function assertNull($actual, $message = '')
    {
        $constraint = new PHPUnit_Framework_Constraint_IsIdentical(NULL);

        if (!$constraint->evaluate($actual)) {
            self::failConstraint($constraint, $actual, $message);
        }
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

        if (!$constraint->evaluate($object)) {
            self::failConstraint($constraint, $object, $message);
        }
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

        if (!$constraint->evaluate($object)) {
            self::failConstraint($constraint, $object, $message);
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
    public static function assertSame($expected, $actual, $message = '')
    {
        $constraint = new PHPUnit_Framework_Constraint_IsIdentical($expected);

        if (!$constraint->evaluate($actual)) {
            self::failConstraint($constraint, $actual, $message);
        }
    }

    /**
     * Asserts that a variable and an attribute of an object have the same type
     * and value.
     *
     * @param  mixed  $expected
     * @param  string $actualAttributeName
     * @param  object $actualObject
     * @param  string $message
     * @access public
     * @static
     */
    public static function assertAttributeSame($expected, $actualAttributeName, $actualObject, $message = '')
    {
        if (!is_string($actualAttributeName) || !is_object($actualObject)) {
            throw new InvalidArgumentException;
        }

        self::assertSame(
          $expected,
          self::getAttribute($actualObject, $actualAttributeName),
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
        $constraint = new PHPUnit_Framework_Constraint_Not(
          new PHPUnit_Framework_Constraint_IsIdentical($expected)
        );

        if (!$constraint->evaluate($actual)) {
            self::failConstraint($constraint, $actual, $message);
        }
    }

    /**
     * Asserts that a variable and an attribute of an object have not the same
     * type and value.
     *
     * @param  mixed  $expected
     * @param  string $actualAttributeName
     * @param  object $actualObject
     * @param  string $message
     * @access public
     * @static
     */
    public static function assertAttributeNotSame($expected, $actualAttributeName, $actualObject, $message = '')
    {
        if (!is_string($actualAttributeName) || !is_object($actualObject)) {
            throw new InvalidArgumentException;
        }

        self::assertNotSame(
          $expected,
          self::getAttribute($actualObject, $actualAttributeName),
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
            if (class_exists($expected) || interface_exists($expected)) {
                $constraint = self::logicalAnd(
                  new PHPUnit_Framework_Constraint_IsType('object'),
                  new PHPUnit_Framework_Constraint_IsInstanceOf($expected)
                );
            } else {
                $constraint = new PHPUnit_Framework_Constraint_IsType($expected);
            }
        } else {
            throw new InvalidArgumentException;
        }

        if (!$constraint->evaluate($actual)) {
            self::failConstraint($constraint, $actual, $message);
        }
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
            if (class_exists($expected) || interface_exists($expected)) {
                $constraint = new PHPUnit_Framework_Constraint_Not(
                  self::logicalAnd(
                    new PHPUnit_Framework_Constraint_IsType('object'),
                    new PHPUnit_Framework_Constraint_IsInstanceOf($expected)
                  )
                );
            } else {
                $constraint = new PHPUnit_Framework_Constraint_Not(
                  new PHPUnit_Framework_Constraint_IsType($expected)
                );
            }
        } else {
            throw new InvalidArgumentException;
        }

        if (!$constraint->evaluate($actual)) {
            self::failConstraint($constraint, $actual, $message);
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
    public static function assertRegExp($pattern, $string, $message = '')
    {
        if (!is_string($pattern) || !is_string($string)) {
            throw new InvalidArgumentException;
        }

        $constraint = new PHPUnit_Framework_Constraint_PCREMatch($pattern);

        if (!$constraint->evaluate($string)) {
            $constraint->fail(
              $string,
              sprintf(
                '%sfailed asserting that string <%s> %s',

                $message,
                print_r($string, TRUE),
                $constraint->toString()
              )
            );
        }
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

        if (!$constraint->evaluate($string)) {
            self::failConstraint($constraint, $string, $message);
        }
    }

    /**
     *
     *
     * @param  mixed                         $value
     * @param  PHPUnit_Framework_Constraint $constraint
     * @param  string                        $message
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function assertThat($value, PHPUnit_Framework_Constraint $constraint, $message = '')
    {
        if (!$constraint->evaluate($value)) {
            self::failConstraint($constraint, $value, $message);
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
     * @param  mixed $value
     * @param  mixed $delta
     * @return PHPUnit_Framework_Constraint_IsEqual
     * @access public
     * @since  Method available since Release 3.0.0
     * @static
     */
    public static function equalTo($value, $delta = 0)
    {
        return new PHPUnit_Framework_Constraint_IsEqual($value, $delta);
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
     * Fails a test based on a failed constraint.
     *
     * @param  PHPUnit_Framework_Constraint $constraint
     * @param  mixed                         $value
     * @param  string                        $message
     * @throws PHPUnit_Framework_ExpectationFailedException
     * @access public
     * @static
     * @since  Method available since Release 3.0.0
     */
    public static function failConstraint(PHPUnit_Framework_Constraint $constraint, $value, $message)
    {
        if (is_string($value) && strpos($value, "\n") !== FALSE) {
            $_value = 'text';
        } else {
            $_value = gettype($value) . ':' . print_r($value, TRUE);
        }

        $constraint->fail(
          $value,
          sprintf(
            '%sfailed asserting that <%s> %s',

            $message,
            $_value,
            $constraint->toString()
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
     * @since  Method available since Release 3.0.0
     */
    public static function getAttribute($object, $attributeName)
    {
        if (!is_object($object) || !is_string($attributeName)) {
            throw new InvalidArgumentException;
        }

        self::assertObjectHasAttribute($attributeName, $object);

        $class     = new ReflectionClass($object);
        $attribute = $class->getProperty($attributeName);

        if ($attribute->isPublic()) {
            return $object->$attributeName;
        } else {
            if ($attribute->isProtected()) {
                $attributeName = "\0*\0" . $attributeName;
            } else {
                $attributeName = sprintf(
                    "\0%s\0%s",

                    get_class($object),
                    $attributeName
                );
            }

            $tmp = (array) $object;

            return $tmp[$attributeName];
        }
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
