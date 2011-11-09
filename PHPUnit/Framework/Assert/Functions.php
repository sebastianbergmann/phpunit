<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2011, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @package    PHPUnit
 * @subpackage Framework
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.5.0
 */

/**
 * Returns a matcher that matches when the method it is evaluated for
 * is executed zero or more times.
 *
 * @return PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount
 * @since  Method available since Release 3.0.0
 */
function any()
{
    return PHPUnit_Framework_TestCase::any();
}

/**
 * Returns a PHPUnit_Framework_Constraint_IsAnything matcher object.
 *
 * @return PHPUnit_Framework_Constraint_IsAnything
 * @since  Method available since Release 3.0.0
 */
function anything()
{
    return PHPUnit_Framework_Assert::anything();
}

/**
 * Returns a PHPUnit_Framework_Constraint_ArrayHasKey matcher object.
 *
 * @param  mixed $key
 * @return PHPUnit_Framework_Constraint_ArrayHasKey
 * @since  Method available since Release 3.0.0
 */
function arrayHasKey($key)
{
    return PHPUnit_Framework_Assert::arrayHasKey($key);
}

/**
 * Asserts that an array has a specified key.
 *
 * @param  mixed  $key
 * @param  array  $array
 * @param  string $message
 * @since  Method available since Release 3.0.0
 */
function assertArrayHasKey($key, array $array, $message = '')
{
    return PHPUnit_Framework_Assert::assertArrayHasKey($key, $array, $message);
}

/**
 * Asserts that an array does not have a specified key.
 *
 * @param  mixed  $key
 * @param  array  $array
 * @param  string $message
 * @since  Method available since Release 3.0.0
 */
function assertArrayNotHasKey($key, array $array, $message = '')
{
    return PHPUnit_Framework_Assert::assertArrayNotHasKey($key, $array, $message);
}

/**
 * Asserts that a haystack that is stored in a static attribute of a class
 * or an attribute of an object contains a needle.
 *
 * @param  mixed   $needle
 * @param  string  $haystackAttributeName
 * @param  mixed   $haystackClassOrObject
 * @param  string  $message
 * @param  boolean $ignoreCase
 * @param  boolean $checkForObjectIdentity
 * @since  Method available since Release 3.0.0
 */
function assertAttributeContains($needle, $haystackAttributeName, $haystackClassOrObject, $message = '', $ignoreCase = FALSE, $checkForObjectIdentity = TRUE)
{
    return PHPUnit_Framework_Assert::assertAttributeContains($needle, $haystackAttributeName, $haystackClassOrObject, $message, $ignoreCase, $checkForObjectIdentity);
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
 * @since  Method available since Release 3.1.4
 */
function assertAttributeContainsOnly($type, $haystackAttributeName, $haystackClassOrObject, $isNativeType = NULL, $message = '')
{
    return PHPUnit_Framework_Assert::assertAttributeContainsOnly($type, $haystackAttributeName, $haystackClassOrObject, $isNativeType, $message);
}

/**
 * Asserts the number of elements of an array, Countable or Iterator
 * that is stored in an attribute.
 *
 * @param integer $expectedCount
 * @param string  $haystackAttributeName
 * @param mixed   $haystackClassOrObject
 * @param string  $message
 * @since Method available since Release 3.6.0
 */
function assertAttributeCount($expectedCount, $haystackAttributeName, $haystackClassOrObject, $message = '')
{
    return PHPUnit_Framework_Assert::assertAttributeCount($expectedCount, $haystackAttributeName, $haystackClassOrObject, $message);
}

/**
 * Asserts that a static attribute of a class or an attribute of an object
 * is empty.
 *
 * @param string $haystackAttributeName
 * @param mixed  $haystackClassOrObject
 * @param string $message
 * @since Method available since Release 3.5.0
 */
function assertAttributeEmpty($haystackAttributeName, $haystackClassOrObject, $message = '')
{
    return PHPUnit_Framework_Assert::assertAttributeEmpty($haystackAttributeName, $haystackClassOrObject, $message);
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
 * @param  boolean $canonicalize
 * @param  boolean $ignoreCase
 */
function assertAttributeEquals($expected, $actualAttributeName, $actualClassOrObject, $message = '', $delta = 0, $maxDepth = 10, $canonicalize = FALSE, $ignoreCase = FALSE)
{
    return PHPUnit_Framework_Assert::assertAttributeEquals($expected, $actualAttributeName, $actualClassOrObject, $message, $delta, $maxDepth, $canonicalize, $ignoreCase);
}

/**
 * Asserts that an attribute is greater than another value.
 *
 * @param  mixed   $expected
 * @param  string  $actualAttributeName
 * @param  string  $actualClassOrObject
 * @param  string  $message
 * @since  Method available since Release 3.1.0
 */
function assertAttributeGreaterThan($expected, $actualAttributeName, $actualClassOrObject, $message = '')
{
    return PHPUnit_Framework_Assert::assertAttributeGreaterThan($expected, $actualAttributeName, $actualClassOrObject, $message);
}

/**
 * Asserts that an attribute is greater than or equal to another value.
 *
 * @param  mixed   $expected
 * @param  string  $actualAttributeName
 * @param  string  $actualClassOrObject
 * @param  string  $message
 * @since  Method available since Release 3.1.0
 */
function assertAttributeGreaterThanOrEqual($expected, $actualAttributeName, $actualClassOrObject, $message = '')
{
    return PHPUnit_Framework_Assert::assertAttributeGreaterThanOrEqual($expected, $actualAttributeName, $actualClassOrObject, $message);
}

/**
 * Asserts that an attribute is of a given type.
 *
 * @param string $expected
 * @param string $attributeName
 * @param mixed  $classOrObject
 * @param string $message
 * @since Method available since Release 3.5.0
 */
function assertAttributeInstanceOf($expected, $attributeName, $classOrObject, $message = '')
{
    return PHPUnit_Framework_Assert::assertAttributeInstanceOf($expected, $attributeName, $classOrObject, $message);
}

/**
 * Asserts that an attribute is of a given type.
 *
 * @param string $expected
 * @param string $attributeName
 * @param mixed  $classOrObject
 * @param string $message
 * @since Method available since Release 3.5.0
 */
function assertAttributeInternalType($expected, $attributeName, $classOrObject, $message = '')
{
    return PHPUnit_Framework_Assert::assertAttributeInternalType($expected, $attributeName, $classOrObject, $message);
}

/**
 * Asserts that an attribute is smaller than another value.
 *
 * @param  mixed   $expected
 * @param  string  $actualAttributeName
 * @param  string  $actualClassOrObject
 * @param  string  $message
 * @since  Method available since Release 3.1.0
 */
function assertAttributeLessThan($expected, $actualAttributeName, $actualClassOrObject, $message = '')
{
    return PHPUnit_Framework_Assert::assertAttributeLessThan($expected, $actualAttributeName, $actualClassOrObject, $message);
}

/**
 * Asserts that an attribute is smaller than or equal to another value.
 *
 * @param  mixed   $expected
 * @param  string  $actualAttributeName
 * @param  string  $actualClassOrObject
 * @param  string  $message
 * @since  Method available since Release 3.1.0
 */
function assertAttributeLessThanOrEqual($expected, $actualAttributeName, $actualClassOrObject, $message = '')
{
    return PHPUnit_Framework_Assert::assertAttributeLessThanOrEqual($expected, $actualAttributeName, $actualClassOrObject, $message);
}

/**
 * Asserts that a haystack that is stored in a static attribute of a class
 * or an attribute of an object does not contain a needle.
 *
 * @param  mixed   $needle
 * @param  string  $haystackAttributeName
 * @param  mixed   $haystackClassOrObject
 * @param  string  $message
 * @param  boolean $ignoreCase
 * @param  boolean $checkForObjectIdentity
 * @since  Method available since Release 3.0.0
 */
function assertAttributeNotContains($needle, $haystackAttributeName, $haystackClassOrObject, $message = '', $ignoreCase = FALSE, $checkForObjectIdentity = TRUE)
{
    return PHPUnit_Framework_Assert::assertAttributeNotContains($needle, $haystackAttributeName, $haystackClassOrObject, $message, $ignoreCase, $checkForObjectIdentity);
}

/**
 * Asserts that a haystack that is stored in a static attribute of a class
 * or an attribute of an object does not contain only values of a given
 * type.
 *
 * @param  string  $type
 * @param  string  $haystackAttributeName
 * @param  mixed   $haystackClassOrObject
 * @param  boolean $isNativeType
 * @param  string  $message
 * @since  Method available since Release 3.1.4
 */
function assertAttributeNotContainsOnly($type, $haystackAttributeName, $haystackClassOrObject, $isNativeType = NULL, $message = '')
{
    return PHPUnit_Framework_Assert::assertAttributeNotContainsOnly($type, $haystackAttributeName, $haystackClassOrObject, $isNativeType, $message);
}

/**
 * Asserts the number of elements of an array, Countable or Iterator
 * that is stored in an attribute.
 *
 * @param integer $expectedCount
 * @param string  $haystackAttributeName
 * @param mixed   $haystackClassOrObject
 * @param string  $message
 * @since Method available since Release 3.6.0
 */
function assertAttributeNotCount($expectedCount, $haystackAttributeName, $haystackClassOrObject, $message = '')
{
    return PHPUnit_Framework_Assert::assertAttributeNotCount($expectedCount, $haystackAttributeName, $haystackClassOrObject, $message);
}

/**
 * Asserts that a static attribute of a class or an attribute of an object
 * is not empty.
 *
 * @param string $haystackAttributeName
 * @param mixed  $haystackClassOrObject
 * @param string $message
 * @since Method available since Release 3.5.0
 */
function assertAttributeNotEmpty($haystackAttributeName, $haystackClassOrObject, $message = '')
{
    return PHPUnit_Framework_Assert::assertAttributeNotEmpty($haystackAttributeName, $haystackClassOrObject, $message);
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
 * @param  boolean $canonicalize
 * @param  boolean $ignoreCase
 */
function assertAttributeNotEquals($expected, $actualAttributeName, $actualClassOrObject, $message = '', $delta = 0, $maxDepth = 10, $canonicalize = FALSE, $ignoreCase = FALSE)
{
    return PHPUnit_Framework_Assert::assertAttributeNotEquals($expected, $actualAttributeName, $actualClassOrObject, $message, $delta, $maxDepth, $canonicalize, $ignoreCase);
}

/**
 * Asserts that an attribute is of a given type.
 *
 * @param string $expected
 * @param string $attributeName
 * @param mixed  $classOrObject
 * @param string $message
 * @since Method available since Release 3.5.0
 */
function assertAttributeNotInstanceOf($expected, $attributeName, $classOrObject, $message = '')
{
    return PHPUnit_Framework_Assert::assertAttributeNotInstanceOf($expected, $attributeName, $classOrObject, $message);
}

/**
 * Asserts that an attribute is of a given type.
 *
 * @param string $expected
 * @param string $attributeName
 * @param mixed  $classOrObject
 * @param string $message
 * @since Method available since Release 3.5.0
 */
function assertAttributeNotInternalType($expected, $attributeName, $classOrObject, $message = '')
{
    return PHPUnit_Framework_Assert::assertAttributeNotInternalType($expected, $attributeName, $classOrObject, $message);
}

/**
 * Asserts that a variable and an attribute of an object do not have the
 * same type and value.
 *
 * @param  mixed  $expected
 * @param  string $actualAttributeName
 * @param  object $actualClassOrObject
 * @param  string $message
 */
function assertAttributeNotSame($expected, $actualAttributeName, $actualClassOrObject, $message = '')
{
    return PHPUnit_Framework_Assert::assertAttributeNotSame($expected, $actualAttributeName, $actualClassOrObject, $message);
}

/**
 * Asserts that a variable and an attribute of an object have the same type
 * and value.
 *
 * @param  mixed  $expected
 * @param  string $actualAttributeName
 * @param  object $actualClassOrObject
 * @param  string $message
 */
function assertAttributeSame($expected, $actualAttributeName, $actualClassOrObject, $message = '')
{
    return PHPUnit_Framework_Assert::assertAttributeSame($expected, $actualAttributeName, $actualClassOrObject, $message);
}

/**
 * Asserts that a class has a specified attribute.
 *
 * @param  string $attributeName
 * @param  string $className
 * @param  string $message
 * @since  Method available since Release 3.1.0
 */
function assertClassHasAttribute($attributeName, $className, $message = '')
{
    return PHPUnit_Framework_Assert::assertClassHasAttribute($attributeName, $className, $message);
}

/**
 * Asserts that a class has a specified static attribute.
 *
 * @param  string $attributeName
 * @param  string $className
 * @param  string $message
 * @since  Method available since Release 3.1.0
 */
function assertClassHasStaticAttribute($attributeName, $className, $message = '')
{
    return PHPUnit_Framework_Assert::assertClassHasStaticAttribute($attributeName, $className, $message);
}

/**
 * Asserts that a class does not have a specified attribute.
 *
 * @param  string $attributeName
 * @param  string $className
 * @param  string $message
 * @since  Method available since Release 3.1.0
 */
function assertClassNotHasAttribute($attributeName, $className, $message = '')
{
    return PHPUnit_Framework_Assert::assertClassNotHasAttribute($attributeName, $className, $message);
}

/**
 * Asserts that a class does not have a specified static attribute.
 *
 * @param  string $attributeName
 * @param  string $className
 * @param  string $message
 * @since  Method available since Release 3.1.0
 */
function assertClassNotHasStaticAttribute($attributeName, $className, $message = '')
{
    return PHPUnit_Framework_Assert::assertClassNotHasStaticAttribute($attributeName, $className, $message);
}

/**
 * Asserts that a haystack contains a needle.
 *
 * @param  mixed   $needle
 * @param  mixed   $haystack
 * @param  string  $message
 * @param  boolean $ignoreCase
 * @param  boolean $checkForObjectIdentity
 * @since  Method available since Release 2.1.0
 */
function assertContains($needle, $haystack, $message = '', $ignoreCase = FALSE, $checkForObjectIdentity = TRUE)
{
    return PHPUnit_Framework_Assert::assertContains($needle, $haystack, $message, $ignoreCase, $checkForObjectIdentity);
}

/**
 * Asserts that a haystack contains only values of a given type.
 *
 * @param  string  $type
 * @param  mixed   $haystack
 * @param  boolean $isNativeType
 * @param  string  $message
 * @since  Method available since Release 3.1.4
 */
function assertContainsOnly($type, $haystack, $isNativeType = NULL, $message = '')
{
    return PHPUnit_Framework_Assert::assertContainsOnly($type, $haystack, $isNativeType, $message);
}

/**
 * Asserts the number of elements of an array, Countable or Iterator.
 *
 * @param integer $expectedCount
 * @param mixed   $haystack
 * @param string  $message
 */
function assertCount($expectedCount, $haystack, $message = '')
{
    return PHPUnit_Framework_Assert::assertCount($expectedCount, $haystack, $message);
}

/**
 * Asserts that a variable is empty.
 *
 * @param  mixed   $actual
 * @param  string  $message
 * @throws PHPUnit_Framework_AssertionFailedError
 */
function assertEmpty($actual, $message = '')
{
    return PHPUnit_Framework_Assert::assertEmpty($actual, $message);
}

/**
 * Asserts that a hierarchy of DOMElements matches.
 *
 * @param DOMElement $expectedElement
 * @param DOMElement $actualElement
 * @param boolean $checkAttributes
 * @param string  $message
 * @author Mattis Stordalen Flister <mattis@xait.no>
 * @since  Method available since Release 3.3.0
 */
function assertEqualXMLStructure(DOMElement $expectedElement, DOMElement $actualElement, $checkAttributes = FALSE, $message = '')
{
    return PHPUnit_Framework_Assert::assertEqualXMLStructure($expectedElement, $actualElement, $checkAttributes, $message);
}

/**
 * Asserts that two variables are equal.
 *
 * @param  mixed   $expected
 * @param  mixed   $actual
 * @param  string  $message
 * @param  float   $delta
 * @param  integer $maxDepth
 * @param  boolean $canonicalize
 * @param  boolean $ignoreCase
 */
function assertEquals($expected, $actual, $message = '', $delta = 0, $maxDepth = 10, $canonicalize = FALSE, $ignoreCase = FALSE)
{
    return PHPUnit_Framework_Assert::assertEquals($expected, $actual, $message, $delta, $maxDepth, $canonicalize, $ignoreCase);
}

/**
 * Asserts that a condition is false.
 *
 * @param  boolean  $condition
 * @param  string   $message
 * @throws PHPUnit_Framework_AssertionFailedError
 */
function assertFalse($condition, $message = '')
{
    return PHPUnit_Framework_Assert::assertFalse($condition, $message);
}

/**
 * Asserts that the contents of one file is equal to the contents of another
 * file.
 *
 * @param  string  $expected
 * @param  string  $actual
 * @param  string  $message
 * @param  boolean $canonicalize
 * @param  boolean $ignoreCase
 * @since  Method available since Release 3.2.14
 */
function assertFileEquals($expected, $actual, $message = '', $canonicalize = FALSE, $ignoreCase = FALSE)
{
    return PHPUnit_Framework_Assert::assertFileEquals($expected, $actual, $message, $canonicalize, $ignoreCase);
}

/**
 * Asserts that a file exists.
 *
 * @param  string $filename
 * @param  string $message
 * @since  Method available since Release 3.0.0
 */
function assertFileExists($filename, $message = '')
{
    return PHPUnit_Framework_Assert::assertFileExists($filename, $message);
}

/**
 * Asserts that the contents of one file is not equal to the contents of
 * another file.
 *
 * @param  string  $expected
 * @param  string  $actual
 * @param  string  $message
 * @param  boolean $canonicalize
 * @param  boolean $ignoreCase
 * @since  Method available since Release 3.2.14
 */
function assertFileNotEquals($expected, $actual, $message = '', $canonicalize = FALSE, $ignoreCase = FALSE)
{
    return PHPUnit_Framework_Assert::assertFileNotEquals($expected, $actual, $message, $canonicalize, $ignoreCase);
}

/**
 * Asserts that a file does not exist.
 *
 * @param  string $filename
 * @param  string $message
 * @since  Method available since Release 3.0.0
 */
function assertFileNotExists($filename, $message = '')
{
    return PHPUnit_Framework_Assert::assertFileNotExists($filename, $message);
}

/**
 * Asserts that a value is greater than another value.
 *
 * @param  mixed   $expected
 * @param  mixed   $actual
 * @param  string  $message
 * @since  Method available since Release 3.1.0
 */
function assertGreaterThan($expected, $actual, $message = '')
{
    return PHPUnit_Framework_Assert::assertGreaterThan($expected, $actual, $message);
}

/**
 * Asserts that a value is greater than or equal to another value.
 *
 * @param  mixed   $expected
 * @param  mixed   $actual
 * @param  string  $message
 * @since  Method available since Release 3.1.0
 */
function assertGreaterThanOrEqual($expected, $actual, $message = '')
{
    return PHPUnit_Framework_Assert::assertGreaterThanOrEqual($expected, $actual, $message);
}

/**
 * Asserts that a variable is of a given type.
 *
 * @param string $expected
 * @param mixed  $actual
 * @param string $message
 * @since Method available since Release 3.5.0
 */
function assertInstanceOf($expected, $actual, $message = '')
{
    return PHPUnit_Framework_Assert::assertInstanceOf($expected, $actual, $message);
}

/**
 * Asserts that a variable is of a given type.
 *
 * @param string $expected
 * @param mixed  $actual
 * @param string $message
 * @since Method available since Release 3.5.0
 */
function assertInternalType($expected, $actual, $message = '')
{
    return PHPUnit_Framework_Assert::assertInternalType($expected, $actual, $message);
}

/**
 * Asserts that a value is smaller than another value.
 *
 * @param  mixed   $expected
 * @param  mixed   $actual
 * @param  string  $message
 * @since  Method available since Release 3.1.0
 */
function assertLessThan($expected, $actual, $message = '')
{
    return PHPUnit_Framework_Assert::assertLessThan($expected, $actual, $message);
}

/**
 * Asserts that a value is smaller than or equal to another value.
 *
 * @param  mixed   $expected
 * @param  mixed   $actual
 * @param  string  $message
 * @since  Method available since Release 3.1.0
 */
function assertLessThanOrEqual($expected, $actual, $message = '')
{
    return PHPUnit_Framework_Assert::assertLessThanOrEqual($expected, $actual, $message);
}

/**
 * Asserts that a haystack does not contain a needle.
 *
 * @param  mixed   $needle
 * @param  mixed   $haystack
 * @param  string  $message
 * @param  boolean $ignoreCase
 * @param  boolean $checkForObjectIdentity
 * @since  Method available since Release 2.1.0
 */
function assertNotContains($needle, $haystack, $message = '', $ignoreCase = FALSE, $checkForObjectIdentity = TRUE)
{
    return PHPUnit_Framework_Assert::assertNotContains($needle, $haystack, $message, $ignoreCase, $checkForObjectIdentity);
}

/**
 * Asserts that a haystack does not contain only values of a given type.
 *
 * @param  string  $type
 * @param  mixed   $haystack
 * @param  boolean $isNativeType
 * @param  string  $message
 * @since  Method available since Release 3.1.4
 */
function assertNotContainsOnly($type, $haystack, $isNativeType = NULL, $message = '')
{
    return PHPUnit_Framework_Assert::assertNotContainsOnly($type, $haystack, $isNativeType, $message);
}

/**
 * Asserts the number of elements of an array, Countable or Iterator.
 *
 * @param integer $expectedCount
 * @param mixed   $haystack
 * @param string  $message
 */
function assertNotCount($expectedCount, $haystack, $message = '')
{
    return PHPUnit_Framework_Assert::assertNotCount($expectedCount, $haystack, $message);
}

/**
 * Asserts that a variable is not empty.
 *
 * @param  mixed   $actual
 * @param  string  $message
 * @throws PHPUnit_Framework_AssertionFailedError
 */
function assertNotEmpty($actual, $message = '')
{
    return PHPUnit_Framework_Assert::assertNotEmpty($actual, $message);
}

/**
 * Asserts that two variables are not equal.
 *
 * @param  mixed   $expected
 * @param  mixed   $actual
 * @param  string  $message
 * @param  float   $delta
 * @param  integer $maxDepth
 * @param  boolean $canonicalize
 * @param  boolean $ignoreCase
 * @since  Method available since Release 2.3.0
 */
function assertNotEquals($expected, $actual, $message = '', $delta = 0, $maxDepth = 10, $canonicalize = FALSE, $ignoreCase = FALSE)
{
    return PHPUnit_Framework_Assert::assertNotEquals($expected, $actual, $message, $delta, $maxDepth, $canonicalize, $ignoreCase);
}

/**
 * Asserts that a variable is not of a given type.
 *
 * @param string $expected
 * @param mixed  $actual
 * @param string $message
 * @since Method available since Release 3.5.0
 */
function assertNotInstanceOf($expected, $actual, $message = '')
{
    return PHPUnit_Framework_Assert::assertNotInstanceOf($expected, $actual, $message);
}

/**
 * Asserts that a variable is not of a given type.
 *
 * @param string $expected
 * @param mixed  $actual
 * @param string $message
 * @since Method available since Release 3.5.0
 */
function assertNotInternalType($expected, $actual, $message = '')
{
    return PHPUnit_Framework_Assert::assertNotInternalType($expected, $actual, $message);
}

/**
 * Asserts that a variable is not NULL.
 *
 * @param  mixed  $actual
 * @param  string $message
 */
function assertNotNull($actual, $message = '')
{
    return PHPUnit_Framework_Assert::assertNotNull($actual, $message);
}

/**
 * Asserts that a string does not match a given regular expression.
 *
 * @param  string $pattern
 * @param  string $string
 * @param  string $message
 * @since  Method available since Release 2.1.0
 */
function assertNotRegExp($pattern, $string, $message = '')
{
    return PHPUnit_Framework_Assert::assertNotRegExp($pattern, $string, $message);
}

/**
 * Asserts that two variables do not have the same type and value.
 * Used on objects, it asserts that two variables do not reference
 * the same object.
 *
 * @param  mixed  $expected
 * @param  mixed  $actual
 * @param  string $message
 */
function assertNotSame($expected, $actual, $message = '')
{
    return PHPUnit_Framework_Assert::assertNotSame($expected, $actual, $message);
}

/**
 * Assert that the size of two arrays (or `Countable` or `Iterator` objects)
 * is not the same.
 *
 * @param integer $expected
 * @param mixed   $actual
 * @param string  $message
 */
function assertNotSameSize($expectedCount, $haystack, $message = '')
{
    return PHPUnit_Framework_Assert::assertNotSameSize($expectedCount, $haystack, $message);
}

/**
 * This assertion is the exact opposite of assertTag().
 *
 * Rather than asserting that $matcher results in a match, it asserts that
 * $matcher does not match.
 *
 * @param  array   $matcher
 * @param  string  $actual
 * @param  string  $message
 * @param  boolean $isHtml
 * @since  Method available since Release 3.3.0
 * @author Mike Naberezny <mike@maintainable.com>
 * @author Derek DeVries <derek@maintainable.com>
 */
function assertNotTag($matcher, $actual, $message = '', $isHtml = TRUE)
{
    return PHPUnit_Framework_Assert::assertNotTag($matcher, $actual, $message, $isHtml);
}

/**
 * Asserts that a variable is NULL.
 *
 * @param  mixed  $actual
 * @param  string $message
 */
function assertNull($actual, $message = '')
{
    return PHPUnit_Framework_Assert::assertNull($actual, $message);
}

/**
 * Asserts that an object has a specified attribute.
 *
 * @param  string $attributeName
 * @param  object $object
 * @param  string $message
 * @since  Method available since Release 3.0.0
 */
function assertObjectHasAttribute($attributeName, $object, $message = '')
{
    return PHPUnit_Framework_Assert::assertObjectHasAttribute($attributeName, $object, $message);
}

/**
 * Asserts that an object does not have a specified attribute.
 *
 * @param  string $attributeName
 * @param  object $object
 * @param  string $message
 * @since  Method available since Release 3.0.0
 */
function assertObjectNotHasAttribute($attributeName, $object, $message = '')
{
    return PHPUnit_Framework_Assert::assertObjectNotHasAttribute($attributeName, $object, $message);
}

/**
 * Asserts that a string matches a given regular expression.
 *
 * @param  string $pattern
 * @param  string $string
 * @param  string $message
 */
function assertRegExp($pattern, $string, $message = '')
{
    return PHPUnit_Framework_Assert::assertRegExp($pattern, $string, $message);
}

/**
 * Asserts that two variables have the same type and value.
 * Used on objects, it asserts that two variables reference
 * the same object.
 *
 * @param  mixed  $expected
 * @param  mixed  $actual
 * @param  string $message
 */
function assertSame($expected, $actual, $message = '')
{
    return PHPUnit_Framework_Assert::assertSame($expected, $actual, $message);
}

/**
 * Assert that the size of two arrays (or `Countable` or `Iterator` objects)
 * is the same.
 *
 * @param integer $expected
 * @param mixed   $actual
 * @param string  $message
 */
function assertSameSize($expected, $actual, $message = '')
{
    return PHPUnit_Framework_Assert::assertSameSize($expected, $actual, $message);
}

/**
 * Assert the presence, absence, or count of elements in a document matching
 * the CSS $selector, regardless of the contents of those elements.
 *
 * The first argument, $selector, is the CSS selector used to match
 * the elements in the $actual document.
 *
 * The second argument, $count, can be either boolean or numeric.
 * When boolean, it asserts for presence of elements matching the selector
 * (TRUE) or absence of elements (FALSE).
 * When numeric, it asserts the count of elements.
 *
 * assertSelectCount("#binder", true, $xml);  // any?
 * assertSelectCount(".binder", 3, $xml); // exactly 3?
 *
 * @param  array   $selector
 * @param  integer $count
 * @param  mixed   $actual
 * @param  string  $message
 * @param  boolean $isHtml
 * @since  Method available since Release 3.3.0
 * @author Mike Naberezny <mike@maintainable.com>
 * @author Derek DeVries <derek@maintainable.com>
 */
function assertSelectCount($selector, $count, $actual, $message = '', $isHtml = TRUE)
{
    return PHPUnit_Framework_Assert::assertSelectCount($selector, $count, $actual, $message, $isHtml);
}

/**
 * assertSelectEquals("#binder .name", "Chuck", true,  $xml);  // any?
 * assertSelectEquals("#binder .name", "Chuck", false, $xml);  // none?
 *
 * @param  array   $selector
 * @param  string  $content
 * @param  integer $count
 * @param  mixed   $actual
 * @param  string  $message
 * @param  boolean $isHtml
 * @since  Method available since Release 3.3.0
 * @author Mike Naberezny <mike@maintainable.com>
 * @author Derek DeVries <derek@maintainable.com>
 */
function assertSelectEquals($selector, $content, $count, $actual, $message = '', $isHtml = TRUE)
{
    return PHPUnit_Framework_Assert::assertSelectEquals($selector, $content, $count, $actual, $message, $isHtml);
}

/**
 * assertSelectRegExp("#binder .name", "/Mike|Derek/", true, $xml); // any?
 * assertSelectRegExp("#binder .name", "/Mike|Derek/", 3, $xml);// 3?
 *
 * @param  array   $selector
 * @param  string  $pattern
 * @param  integer $count
 * @param  mixed   $actual
 * @param  string  $message
 * @param  boolean $isHtml
 * @since  Method available since Release 3.3.0
 * @author Mike Naberezny <mike@maintainable.com>
 * @author Derek DeVries <derek@maintainable.com>
 */
function assertSelectRegExp($selector, $pattern, $count, $actual, $message = '', $isHtml = TRUE)
{
    return PHPUnit_Framework_Assert::assertSelectRegExp($selector, $pattern, $count, $actual, $message, $isHtml);
}

/**
 * Asserts that a string ends not with a given prefix.
 *
 * @param  string $suffix
 * @param  string $string
 * @param  string $message
 * @since  Method available since Release 3.4.0
 */
function assertStringEndsNotWith($suffix, $string, $message = '')
{
    return PHPUnit_Framework_Assert::assertStringEndsNotWith($suffix, $string, $message);
}

/**
 * Asserts that a string ends with a given prefix.
 *
 * @param  string $suffix
 * @param  string $string
 * @param  string $message
 * @since  Method available since Release 3.4.0
 */
function assertStringEndsWith($suffix, $string, $message = '')
{
    return PHPUnit_Framework_Assert::assertStringEndsWith($suffix, $string, $message);
}

/**
 * Asserts that the contents of a string is equal
 * to the contents of a file.
 *
 * @param  string  $expectedFile
 * @param  string  $actualString
 * @param  string  $message
 * @param  boolean $canonicalize
 * @param  boolean $ignoreCase
 * @since  Method available since Release 3.3.0
 */
function assertStringEqualsFile($expectedFile, $actualString, $message = '', $canonicalize = FALSE, $ignoreCase = FALSE)
{
    return PHPUnit_Framework_Assert::assertStringEqualsFile($expectedFile, $actualString, $message, $canonicalize, $ignoreCase);
}

/**
 * Asserts that a string matches a given format string.
 *
 * @param  string $format
 * @param  string $string
 * @param  string $message
 * @since  Method available since Release 3.5.0
 */
function assertStringMatchesFormat($format, $string, $message = '')
{
    return PHPUnit_Framework_Assert::assertStringMatchesFormat($format, $string, $message);
}

/**
 * Asserts that a string matches a given format file.
 *
 * @param  string $formatFile
 * @param  string $string
 * @param  string $message
 * @since  Method available since Release 3.5.0
 */
function assertStringMatchesFormatFile($formatFile, $string, $message = '')
{
    return PHPUnit_Framework_Assert::assertStringMatchesFormatFile($formatFile, $string, $message);
}

/**
 * Asserts that the contents of a string is not equal
 * to the contents of a file.
 *
 * @param  string  $expectedFile
 * @param  string  $actualString
 * @param  string  $message
 * @param  boolean $canonicalize
 * @param  boolean $ignoreCase
 * @since  Method available since Release 3.3.0
 */
function assertStringNotEqualsFile($expectedFile, $actualString, $message = '', $canonicalize = FALSE, $ignoreCase = FALSE)
{
    return PHPUnit_Framework_Assert::assertStringNotEqualsFile($expectedFile, $actualString, $message, $canonicalize, $ignoreCase);
}

/**
 * Asserts that a string does not match a given format string.
 *
 * @param  string $format
 * @param  string $string
 * @param  string $message
 * @since  Method available since Release 3.5.0
 */
function assertStringNotMatchesFormat($format, $string, $message = '')
{
    return PHPUnit_Framework_Assert::assertStringNotMatchesFormat($format, $string, $message);
}

/**
 * Asserts that a string does not match a given format string.
 *
 * @param  string $formatFile
 * @param  string $string
 * @param  string $message
 * @since  Method available since Release 3.5.0
 */
function assertStringNotMatchesFormatFile($formatFile, $string, $message = '')
{
    return PHPUnit_Framework_Assert::assertStringNotMatchesFormatFile($formatFile, $string, $message);
}

/**
 * Asserts that a string starts not with a given prefix.
 *
 * @param  string $prefix
 * @param  string $string
 * @param  string $message
 * @since  Method available since Release 3.4.0
 */
function assertStringStartsNotWith($prefix, $string, $message = '')
{
    return PHPUnit_Framework_Assert::assertStringStartsNotWith($prefix, $string, $message);
}

/**
 * Asserts that a string starts with a given prefix.
 *
 * @param  string $prefix
 * @param  string $string
 * @param  string $message
 * @since  Method available since Release 3.4.0
 */
function assertStringStartsWith($prefix, $string, $message = '')
{
    return PHPUnit_Framework_Assert::assertStringStartsWith($prefix, $string, $message);
}

/**
 * Evaluate an HTML or XML string and assert its structure and/or contents.
 *
 * The first argument ($matcher) is an associative array that specifies the
 * match criteria for the assertion:
 *
 *  - `id`   : the node with the given id attribute must match the
 * corresponsing value.
 *  - `tag`  : the node type must match the corresponding value.
 *  - `attributes`   : a hash. The node's attributres must match the
 * corresponsing values in the hash.
 *  - `content`  : The text content must match the given value.
 *  - `parent`   : a hash. The node's parent must match the
 * corresponsing hash.
 *  - `child`: a hash. At least one of the node's immediate children
 * must meet the criteria described by the hash.
 *  - `ancestor` : a hash. At least one of the node's ancestors must
 * meet the criteria described by the hash.
 *  - `descendant`   : a hash. At least one of the node's descendants must
 * meet the criteria described by the hash.
 *  - `children` : a hash, for counting children of a node.
 * Accepts the keys:
 *- `count`: a number which must equal the number of children
 *   that match
 *- `less_than`: the number of matching children must be greater
 *   than this number
 *- `greater_than` : the number of matching children must be less than
 *   this number
 *- `only` : another hash consisting of the keys to use to match
 *   on the children, and only matching children will be
 *   counted
 *
 * <code>
 * // Matcher that asserts that there is an element with an id="my_id".
 * $matcher = array('id' => 'my_id');
 *
 * // Matcher that asserts that there is a "span" tag.
 * $matcher = array('tag' => 'span');
 *
 * // Matcher that asserts that there is a "span" tag with the content
 * // "Hello World".
 * $matcher = array('tag' => 'span', 'content' => 'Hello World');
 *
 * // Matcher that asserts that there is a "span" tag with content matching
 * // the regular expression pattern.
 * $matcher = array('tag' => 'span', 'content' => 'regexp:/Try P(HP|ython)/');
 *
 * // Matcher that asserts that there is a "span" with an "list" class
 * // attribute.
 * $matcher = array(
 *   'tag'=> 'span',
 *   'attributes' => array('class' => 'list')
 * );
 *
 * // Matcher that asserts that there is a "span" inside of a "div".
 * $matcher = array(
 *   'tag'=> 'span',
 *   'parent' => array('tag' => 'div')
 * );
 *
 * // Matcher that asserts that there is a "span" somewhere inside a
 * // "table".
 * $matcher = array(
 *   'tag'  => 'span',
 *   'ancestor' => array('tag' => 'table')
 * );
 *
 * // Matcher that asserts that there is a "span" with at least one "em"
 * // child.
 * $matcher = array(
 *   'tag'   => 'span',
 *   'child' => array('tag' => 'em')
 * );
 *
 * // Matcher that asserts that there is a "span" containing a (possibly
 * // nested) "strong" tag.
 * $matcher = array(
 *   'tag'=> 'span',
 *   'descendant' => array('tag' => 'strong')
 * );
 *
 * // Matcher that asserts that there is a "span" containing 5-10 "em" tags
 * // as immediate children.
 * $matcher = array(
 *   'tag'  => 'span',
 *   'children' => array(
 * 'less_than'=> 11,
 * 'greater_than' => 4,
 * 'only' => array('tag' => 'em')
 *   )
 * );
 *
 * // Matcher that asserts that there is a "div", with an "ul" ancestor and
 * // a "li" parent (with class="enum"), and containing a "span" descendant
 * // that contains an element with id="my_test" and the text "Hello World".
 * $matcher = array(
 *   'tag'=> 'div',
 *   'ancestor'   => array('tag' => 'ul'),
 *   'parent' => array(
 * 'tag'=> 'li',
 * 'attributes' => array('class' => 'enum')
 *   ),
 *   'descendant' => array(
 * 'tag'   => 'span',
 * 'child' => array(
 *   'id'  => 'my_test',
 *   'content' => 'Hello World'
 * )
 *   )
 * );
 *
 * // Use assertTag() to apply a $matcher to a piece of $html.
 * $this->assertTag($matcher, $html);
 *
 * // Use assertTag() to apply a $matcher to a piece of $xml.
 * $this->assertTag($matcher, $xml, '', FALSE);
 * </code>
 *
 * The second argument ($actual) is a string containing either HTML or
 * XML text to be tested.
 *
 * The third argument ($message) is an optional message that will be
 * used if the assertion fails.
 *
 * The fourth argument ($html) is an optional flag specifying whether
 * to load the $actual string into a DOMDocument using the HTML or
 * XML load strategy.  It is TRUE by default, which assumes the HTML
 * load strategy.  In many cases, this will be acceptable for XML as well.
 *
 * @param  array   $matcher
 * @param  string  $actual
 * @param  string  $message
 * @param  boolean $isHtml
 * @since  Method available since Release 3.3.0
 * @author Mike Naberezny <mike@maintainable.com>
 * @author Derek DeVries <derek@maintainable.com>
 */
function assertTag($matcher, $actual, $message = '', $isHtml = TRUE)
{
    return PHPUnit_Framework_Assert::assertTag($matcher, $actual, $message, $isHtml);
}

/**
 * Evaluates a PHPUnit_Framework_Constraint matcher object.
 *
 * @param  mixed$value
 * @param  PHPUnit_Framework_Constraint $constraint
 * @param  string   $message
 * @since  Method available since Release 3.0.0
 */
function assertThat($value, PHPUnit_Framework_Constraint $constraint, $message = '')
{
    return PHPUnit_Framework_Assert::assertThat($value, $constraint, $message);
}

/**
 * Asserts that a condition is true.
 *
 * @param  boolean $condition
 * @param  string  $message
 * @throws PHPUnit_Framework_AssertionFailedError
 */
function assertTrue($condition, $message = '')
{
    return PHPUnit_Framework_Assert::assertTrue($condition, $message);
}

/**
 * Asserts that two XML files are equal.
 *
 * @param  string $expectedFile
 * @param  string $actualFile
 * @param  string $message
 * @since  Method available since Release 3.1.0
 */
function assertXmlFileEqualsXmlFile($expectedFile, $actualFile, $message = '')
{
    return PHPUnit_Framework_Assert::assertXmlFileEqualsXmlFile($expectedFile, $actualFile, $message);
}

/**
 * Asserts that two XML files are not equal.
 *
 * @param  string $expectedFile
 * @param  string $actualFile
 * @param  string $message
 * @since  Method available since Release 3.1.0
 */
function assertXmlFileNotEqualsXmlFile($expectedFile, $actualFile, $message = '')
{
    return PHPUnit_Framework_Assert::assertXmlFileNotEqualsXmlFile($expectedFile, $actualFile, $message);
}

/**
 * Asserts that two XML documents are equal.
 *
 * @param  string $expectedFile
 * @param  string $actualXml
 * @param  string $message
 * @since  Method available since Release 3.3.0
 */
function assertXmlStringEqualsXmlFile($expectedFile, $actualXml, $message = '')
{
    return PHPUnit_Framework_Assert::assertXmlStringEqualsXmlFile($expectedFile, $actualXml, $message);
}

/**
 * Asserts that two XML documents are equal.
 *
 * @param  string $expectedXml
 * @param  string $actualXml
 * @param  string $message
 * @since  Method available since Release 3.1.0
 */
function assertXmlStringEqualsXmlString($expectedXml, $actualXml, $message = '')
{
    return PHPUnit_Framework_Assert::assertXmlStringEqualsXmlString($expectedXml, $actualXml, $message);
}

/**
 * Asserts that two XML documents are not equal.
 *
 * @param  string $expectedFile
 * @param  string $actualXml
 * @param  string $message
 * @since  Method available since Release 3.3.0
 */
function assertXmlStringNotEqualsXmlFile($expectedFile, $actualXml, $message = '')
{
    return PHPUnit_Framework_Assert::assertXmlStringNotEqualsXmlFile($expectedFile, $actualXml, $message);
}

/**
 * Asserts that two XML documents are not equal.
 *
 * @param  string $expectedXml
 * @param  string $actualXml
 * @param  string $message
 * @since  Method available since Release 3.1.0
 */
function assertXmlStringNotEqualsXmlString($expectedXml, $actualXml, $message = '')
{
    return PHPUnit_Framework_Assert::assertXmlStringNotEqualsXmlString($expectedXml, $actualXml, $message);
}

/**
 * Returns a matcher that matches when the method it is evaluated for
 * is invoked at the given $index.
 *
 * @param  integer $index
 * @return PHPUnit_Framework_MockObject_Matcher_InvokedAtIndex
 * @since  Method available since Release 3.0.0
 */
function at($index)
{
    return PHPUnit_Framework_TestCase::at($index);
}

/**
 * Returns a matcher that matches when the method it is evaluated for
 * is executed at least once.
 *
 * @return PHPUnit_Framework_MockObject_Matcher_InvokedAtLeastOnce
 * @since  Method available since Release 3.0.0
 */
function atLeastOnce()
{
    return PHPUnit_Framework_TestCase::atLeastOnce();
}

/**
 * Returns a PHPUnit_Framework_Constraint_Attribute matcher object.
 *
 * @param  PHPUnit_Framework_Constraint $constraint
 * @param  string   $attributeName
 * @return PHPUnit_Framework_Constraint_Attribute
 * @since  Method available since Release 3.1.0
 */
function attribute(PHPUnit_Framework_Constraint $constraint, $attributeName)
{
    return PHPUnit_Framework_Assert::attribute($constraint, $attributeName);
}

/**
 * Returns a PHPUnit_Framework_Constraint_IsEqual matcher object
 * that is wrapped in a PHPUnit_Framework_Constraint_Attribute matcher
 * object.
 *
 * @param  string  $attributeName
 * @param  mixed   $value
 * @param  float   $delta
 * @param  integer $maxDepth
 * @param  boolean $canonicalize
 * @param  boolean $ignoreCase
 * @return PHPUnit_Framework_Constraint_Attribute
 * @since  Method available since Release 3.1.0
 */
function attributeEqualTo($attributeName, $value, $delta = 0, $maxDepth = 10, $canonicalize = FALSE, $ignoreCase = FALSE)
{
    return PHPUnit_Framework_Assert::attributeEqualTo($attributeName, $value, $delta, $maxDepth, $canonicalize, $ignoreCase);
}

/**
 * Returns a PHPUnit_Framework_Constraint_ClassHasAttribute matcher object.
 *
 * @param  string $attributeName
 * @return PHPUnit_Framework_Constraint_ClassHasAttribute
 * @since  Method available since Release 3.1.0
 */
function classHasAttribute($attributeName)
{
    return PHPUnit_Framework_Assert::classHasAttribute($attributeName);
}

/**
 * Returns a PHPUnit_Framework_Constraint_ClassHasStaticAttribute matcher
 * object.
 *
 * @param  string $attributeName
 * @return PHPUnit_Framework_Constraint_ClassHasStaticAttribute
 * @since  Method available since Release 3.1.0
 */
function classHasStaticAttribute($attributeName)
{
    return PHPUnit_Framework_Assert::classHasStaticAttribute($attributeName);
}

/**
 * Returns a PHPUnit_Framework_Constraint_TraversableContains matcher
 * object.
 *
 * @param  mixed   $value
 * @param  boolean $checkForObjectIdentity
 * @return PHPUnit_Framework_Constraint_TraversableContains
 * @since  Method available since Release 3.0.0
 */
function contains($value, $checkForObjectIdentity = TRUE)
{
    return PHPUnit_Framework_Assert::contains($value, $checkForObjectIdentity);
}

/**
 * Returns a PHPUnit_Framework_Constraint_TraversableContainsOnly matcher
 * object.
 *
 * @param  string $type
 * @return PHPUnit_Framework_Constraint_TraversableContainsOnly
 * @since  Method available since Release 3.1.4
 */
function containsOnly($type)
{
    return PHPUnit_Framework_Assert::containsOnly($type);
}

/**
 * Returns a PHPUnit_Framework_Constraint_IsEqual matcher object.
 *
 * @param  mixed   $value
 * @param  float   $delta
 * @param  integer $maxDepth
 * @param  boolean $canonicalize
 * @param  boolean $ignoreCase
 * @return PHPUnit_Framework_Constraint_IsEqual
 * @since  Method available since Release 3.0.0
 */
function equalTo($value, $delta = 0, $maxDepth = 10, $canonicalize = FALSE, $ignoreCase = FALSE)
{
    return PHPUnit_Framework_Assert::equalTo($value, $delta, $maxDepth, $canonicalize, $ignoreCase);
}

/**
 * Returns a matcher that matches when the method it is evaluated for
 * is executed exactly $count times.
 *
 * @param  integer $count
 * @return PHPUnit_Framework_MockObject_Matcher_InvokedCount
 * @since  Method available since Release 3.0.0
 */
function exactly($count)
{
    return PHPUnit_Framework_TestCase::exactly($count);
}

/**
 * Returns a PHPUnit_Framework_Constraint_FileExists matcher object.
 *
 * @return PHPUnit_Framework_Constraint_FileExists
 * @since  Method available since Release 3.0.0
 */
function fileExists()
{
    return PHPUnit_Framework_Assert::fileExists();
}

/**
 * Returns a PHPUnit_Framework_Constraint_GreaterThan matcher object.
 *
 * @param  mixed $value
 * @return PHPUnit_Framework_Constraint_GreaterThan
 * @since  Method available since Release 3.0.0
 */
function greaterThan($value)
{
    return PHPUnit_Framework_Assert::greaterThan($value);
}

/**
 * Returns a PHPUnit_Framework_Constraint_Or matcher object that wraps
 * a PHPUnit_Framework_Constraint_IsEqual and a
 * PHPUnit_Framework_Constraint_GreaterThan matcher object.
 *
 * @param  mixed $value
 * @return PHPUnit_Framework_Constraint_Or
 * @since  Method available since Release 3.1.0
 */
function greaterThanOrEqual($value)
{
    return PHPUnit_Framework_Assert::greaterThanOrEqual($value);
}

/**
 * Returns a PHPUnit_Framework_Constraint_IsIdentical matcher object.
 *
 * @param  mixed $value
 * @return PHPUnit_Framework_Constraint_IsIdentical
 * @since  Method available since Release 3.0.0
 */
function identicalTo($value)
{
    return PHPUnit_Framework_Assert::identicalTo($value);
}

/**
 * Returns a PHPUnit_Framework_Constraint_IsEmpty matcher object.
 *
 * @return PHPUnit_Framework_Constraint_IsEmpty
 * @since  Method available since Release 3.5.0
 */
function isEmpty()
{
    return PHPUnit_Framework_Assert::isEmpty();
}

/**
 * Returns a PHPUnit_Framework_Constraint_IsFalse matcher object.
 *
 * @return PHPUnit_Framework_Constraint_IsFalse
 * @since  Method available since Release 3.3.0
 */
function isFalse()
{
    return PHPUnit_Framework_Assert::isFalse();
}

/**
 * Returns a PHPUnit_Framework_Constraint_IsInstanceOf matcher object.
 *
 * @param  string $className
 * @return PHPUnit_Framework_Constraint_IsInstanceOf
 * @since  Method available since Release 3.0.0
 */
function isInstanceOf($className)
{
    return PHPUnit_Framework_Assert::isInstanceOf($className);
}

/**
 * Returns a PHPUnit_Framework_Constraint_IsNull matcher object.
 *
 * @return PHPUnit_Framework_Constraint_IsNull
 * @since  Method available since Release 3.3.0
 */
function isNull()
{
    return PHPUnit_Framework_Assert::isNull();
}

/**
 * Returns a PHPUnit_Framework_Constraint_IsTrue matcher object.
 *
 * @return PHPUnit_Framework_Constraint_IsTrue
 * @since  Method available since Release 3.3.0
 */
function isTrue()
{
    return PHPUnit_Framework_Assert::isTrue();
}

/**
 * Returns a PHPUnit_Framework_Constraint_IsType matcher object.
 *
 * @param  string $type
 * @return PHPUnit_Framework_Constraint_IsType
 * @since  Method available since Release 3.0.0
 */
function isType($type)
{
    return PHPUnit_Framework_Assert::isType($type);
}

/**
 * Returns a PHPUnit_Framework_Constraint_LessThan matcher object.
 *
 * @param  mixed $value
 * @return PHPUnit_Framework_Constraint_LessThan
 * @since  Method available since Release 3.0.0
 */
function lessThan($value)
{
    return PHPUnit_Framework_Assert::lessThan($value);
}

/**
 * Returns a PHPUnit_Framework_Constraint_Or matcher object that wraps
 * a PHPUnit_Framework_Constraint_IsEqual and a
 * PHPUnit_Framework_Constraint_LessThan matcher object.
 *
 * @param  mixed $value
 * @return PHPUnit_Framework_Constraint_Or
 * @since  Method available since Release 3.1.0
 */
function lessThanOrEqual($value)
{
    return PHPUnit_Framework_Assert::lessThanOrEqual($value);
}

/**
 * Returns a PHPUnit_Framework_Constraint_And matcher object.
 *
 * @return PHPUnit_Framework_Constraint_And
 * @since  Method available since Release 3.0.0
 */
function logicalAnd()
{
    return PHPUnit_Framework_Assert::logicalAnd();
}

/**
 * Returns a PHPUnit_Framework_Constraint_Not matcher object.
 *
 * @param  PHPUnit_Framework_Constraint $constraint
 * @return PHPUnit_Framework_Constraint_Not
 * @since  Method available since Release 3.0.0
 */
function logicalNot(PHPUnit_Framework_Constraint $constraint)
{
    return PHPUnit_Framework_Assert::logicalNot($constraint);
}

/**
 * Returns a PHPUnit_Framework_Constraint_Or matcher object.
 *
 * @return PHPUnit_Framework_Constraint_Or
 * @since  Method available since Release 3.0.0
 */
function logicalOr()
{
    return PHPUnit_Framework_Assert::logicalOr();
}

/**
 * Returns a PHPUnit_Framework_Constraint_Xor matcher object.
 *
 * @return PHPUnit_Framework_Constraint_Xor
 * @since  Method available since Release 3.0.0
 */
function logicalXor()
{
    return PHPUnit_Framework_Assert::logicalXor();
}

/**
 * Returns a PHPUnit_Framework_Constraint_StringMatches matcher object.
 *
 * @param  string $string
 * @return PHPUnit_Framework_Constraint_StringMatches
 * @since  Method available since Release 3.5.0
 */
function matches($string)
{
    return PHPUnit_Framework_Assert::matches($string);
}

/**
 * Returns a PHPUnit_Framework_Constraint_PCREMatch matcher object.
 *
 * @param  string $pattern
 * @return PHPUnit_Framework_Constraint_PCREMatch
 * @since  Method available since Release 3.0.0
 */
function matchesRegularExpression($pattern)
{
    return PHPUnit_Framework_Assert::matchesRegularExpression($pattern);
}

/**
 * Returns a matcher that matches when the method it is evaluated for
 * is never executed.
 *
 * @return PHPUnit_Framework_MockObject_Matcher_InvokedCount
 * @since  Method available since Release 3.0.0
 */
function never()
{
    return PHPUnit_Framework_TestCase::never();
}

/**
 * Returns a PHPUnit_Framework_Constraint_ObjectHasAttribute matcher object.
 *
 * @param  string $attributeName
 * @return PHPUnit_Framework_Constraint_ObjectHasAttribute
 * @since  Method available since Release 3.0.0
 */
function objectHasAttribute($attributeName)
{
    return PHPUnit_Framework_Assert::objectHasAttribute($attributeName);
}

/**
 *
 *
 * @param  mixed $value, ...
 * @return PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls
 * @since  Method available since Release 3.0.0
 */
function onConsecutiveCalls()
{
    return PHPUnit_Framework_TestCase::onConsecutiveCalls();
}

/**
 * Returns a matcher that matches when the method it is evaluated for
 * is executed exactly once.
 *
 * @return PHPUnit_Framework_MockObject_Matcher_InvokedCount
 * @since  Method available since Release 3.0.0
 */
function once()
{
    return PHPUnit_Framework_TestCase::once();
}

/**
 *
 *
 * @param  integer $argumentIndex
 * @return PHPUnit_Framework_MockObject_Stub_ReturnArgument
 * @since  Method available since Release 3.3.0
 */
function returnArgument($argumentIndex)
{
    return PHPUnit_Framework_TestCase::returnArgument($argumentIndex);
}

/**
 *
 *
 * @param  mixed $callback
 * @return PHPUnit_Framework_MockObject_Stub_ReturnCallback
 * @since  Method available since Release 3.3.0
 */
function returnCallback($callback)
{
    return PHPUnit_Framework_TestCase::returnCallback($callback);
}

/**
 * Returns the current object.
 *
 * This method is useful when mocking a fluent interface.
 *
 * @return PHPUnit_Framework_MockObject_Stub_ReturnSelf
 * @since  Method available since Release 3.6.0
 */
function returnSelf()
{
    return PHPUnit_Framework_TestCase::returnSelf();
}

/**
 *
 *
 * @param  mixed $value
 * @return PHPUnit_Framework_MockObject_Stub_Return
 * @since  Method available since Release 3.0.0
 */
function returnValue($value)
{
    return PHPUnit_Framework_TestCase::returnValue($value);
}

/**
 *
 *
 * @param  array $valueMap
 * @return PHPUnit_Framework_MockObject_Stub_ReturnValueMap
 * @since  Method available since Release 3.6.0
 */
function returnValueMap(array $valueMap)
{
    return PHPUnit_Framework_TestCase::returnValueMap($valueMap);
}

/**
 * Returns a PHPUnit_Framework_Constraint_StringContains matcher object.
 *
 * @param  string  $string
 * @param  boolean $case
 * @return PHPUnit_Framework_Constraint_StringContains
 * @since  Method available since Release 3.0.0
 */
function stringContains($string, $case = TRUE)
{
    return PHPUnit_Framework_Assert::stringContains($string, $case);
}

/**
 * Returns a PHPUnit_Framework_Constraint_StringEndsWith matcher object.
 *
 * @param  mixed $suffix
 * @return PHPUnit_Framework_Constraint_StringEndsWith
 * @since  Method available since Release 3.4.0
 */
function stringEndsWith($suffix)
{
    return PHPUnit_Framework_Assert::stringEndsWith($suffix);
}

/**
 * Returns a PHPUnit_Framework_Constraint_StringStartsWith matcher object.
 *
 * @param  mixed $prefix
 * @return PHPUnit_Framework_Constraint_StringStartsWith
 * @since  Method available since Release 3.4.0
 */
function stringStartsWith($prefix)
{
    return PHPUnit_Framework_Assert::stringStartsWith($prefix);
}

/**
 *
 *
 * @param  Exception $exception
 * @return PHPUnit_Framework_MockObject_Stub_Exception
 * @since  Method available since Release 3.1.0
 */
function throwException(Exception $exception)
{
    return PHPUnit_Framework_TestCase::throwException($exception);
}
