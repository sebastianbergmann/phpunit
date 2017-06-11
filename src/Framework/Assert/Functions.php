<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\Count;
use PHPUnit\Framework\Constraint\LogicalAnd;
use PHPUnit\Framework\Constraint\ArrayHasKey;
use PHPUnit\Framework\Constraint\Attribute;
use PHPUnit\Framework\Constraint\ClassHasAttribute;
use PHPUnit\Framework\Constraint\ClassHasStaticAttribute;
use PHPUnit\Framework\Constraint\DirectoryExists;
use PHPUnit\Framework\Constraint\FileExists;
use PHPUnit\Framework\Constraint\GreaterThan;
use PHPUnit\Framework\Constraint\IsAnything;
use PHPUnit\Framework\Constraint\IsEmpty;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\Constraint\IsFalse;
use PHPUnit\Framework\Constraint\IsIdentical;
use PHPUnit\Framework\Constraint\IsInfinite;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\Constraint\IsJson;
use PHPUnit\Framework\Constraint\IsNan;
use PHPUnit\Framework\Constraint\IsNull;
use PHPUnit\Framework\Constraint\IsReadable;
use PHPUnit\Framework\Constraint\IsTrue;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\Constraint\IsWritable;
use PHPUnit\Framework\Constraint\LessThan;
use PHPUnit\Framework\Constraint\LogicalNot;
use PHPUnit\Framework\Constraint\ObjectHasAttribute;
use PHPUnit\Framework\Constraint\LogicalOr;
use PHPUnit\Framework\Constraint\LogicalXor;
use PHPUnit\Framework\Constraint\TraversableContainsOnly;
use PHPUnit\Framework\Constraint\TraversableContains;
use PHPUnit\Framework\Constraint\StringStartsWith;
use PHPUnit\Framework\Constraint\StringMatchesFormatDescription;
use PHPUnit\Framework\Constraint\StringEndsWith;
use PHPUnit\Framework\Constraint\StringContains;
use PHPUnit\Framework\Constraint\RegularExpression;
use PHPUnit\Framework\TestCase;

/**
 * Returns a matcher that matches when the method is executed
 * zero or more times.
 *
 * @return PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount
 */
function any()
{
    return TestCase::any();
}

/**
 * Returns a PHPUnit\Framework\Constraint\IsAnything matcher object.
 *
 * @return IsAnything
 */
function anything()
{
    return Assert::anything();
}

/**
 * Returns a PHPUnit\Framework\Constraint\ArrayHasKey matcher object.
 *
 * @param mixed $key
 *
 * @return ArrayHasKey
 */
function arrayHasKey($key)
{
    return Assert::arrayHasKey(...\func_get_args());
}

/**
 * Asserts that an array has a specified key.
 *
 * @param mixed             $key
 * @param array|ArrayAccess $array
 * @param string            $message
 */
function assertArrayHasKey($key, $array, $message = '')
{
    return Assert::assertArrayHasKey(...\func_get_args());
}

/**
 * Asserts that an array has a specified subset.
 *
 * @param array|ArrayAccess $subset
 * @param array|ArrayAccess $array
 * @param bool              $strict  Check for object identity
 * @param string            $message
 */
function assertArraySubset($subset, $array, $strict = false, $message = '')
{
    return Assert::assertArraySubset(...\func_get_args());
}

/**
 * Asserts that an array does not have a specified key.
 *
 * @param mixed             $key
 * @param array|ArrayAccess $array
 * @param string            $message
 */
function assertArrayNotHasKey($key, $array, $message = '')
{
    return Assert::assertArrayNotHasKey(...\func_get_args());
}

/**
 * Asserts that a haystack that is stored in a static attribute of a class
 * or an attribute of an object contains a needle.
 *
 * @param mixed  $needle
 * @param string $haystackAttributeName
 * @param mixed  $haystackClassOrObject
 * @param string $message
 * @param bool   $ignoreCase
 * @param bool   $checkForObjectIdentity
 * @param bool   $checkForNonObjectIdentity
 */
function assertAttributeContains($needle, $haystackAttributeName, $haystackClassOrObject, $message = '', $ignoreCase = false, $checkForObjectIdentity = true, $checkForNonObjectIdentity = false)
{
    return Assert::assertAttributeContains(...\func_get_args());
}

/**
 * Asserts that a haystack that is stored in a static attribute of a class
 * or an attribute of an object contains only values of a given type.
 *
 * @param string $type
 * @param string $haystackAttributeName
 * @param mixed  $haystackClassOrObject
 * @param bool   $isNativeType
 * @param string $message
 */
function assertAttributeContainsOnly($type, $haystackAttributeName, $haystackClassOrObject, $isNativeType = null, $message = '')
{
    return Assert::assertAttributeContainsOnly(...\func_get_args());
}

/**
 * Asserts the number of elements of an array, Countable or Traversable
 * that is stored in an attribute.
 *
 * @param int    $expectedCount
 * @param string $haystackAttributeName
 * @param mixed  $haystackClassOrObject
 * @param string $message
 */
function assertAttributeCount($expectedCount, $haystackAttributeName, $haystackClassOrObject, $message = '')
{
    return Assert::assertAttributeCount(...\func_get_args());
}

/**
 * Asserts that a static attribute of a class or an attribute of an object
 * is empty.
 *
 * @param string $haystackAttributeName
 * @param mixed  $haystackClassOrObject
 * @param string $message
 */
function assertAttributeEmpty($haystackAttributeName, $haystackClassOrObject, $message = '')
{
    return Assert::assertAttributeEmpty(...\func_get_args());
}

/**
 * Asserts that a variable is equal to an attribute of an object.
 *
 * @param mixed  $expected
 * @param string $actualAttributeName
 * @param string $actualClassOrObject
 * @param string $message
 * @param float  $delta
 * @param int    $maxDepth
 * @param bool   $canonicalize
 * @param bool   $ignoreCase
 */
function assertAttributeEquals($expected, $actualAttributeName, $actualClassOrObject, $message = '', $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
{
    return Assert::assertAttributeEquals(...\func_get_args());
}

/**
 * Asserts that an attribute is greater than another value.
 *
 * @param mixed  $expected
 * @param string $actualAttributeName
 * @param string $actualClassOrObject
 * @param string $message
 */
function assertAttributeGreaterThan($expected, $actualAttributeName, $actualClassOrObject, $message = '')
{
    return Assert::assertAttributeGreaterThan(...\func_get_args());
}

/**
 * Asserts that an attribute is greater than or equal to another value.
 *
 * @param mixed  $expected
 * @param string $actualAttributeName
 * @param string $actualClassOrObject
 * @param string $message
 */
function assertAttributeGreaterThanOrEqual($expected, $actualAttributeName, $actualClassOrObject, $message = '')
{
    return Assert::assertAttributeGreaterThanOrEqual(...\func_get_args());
}

/**
 * Asserts that an attribute is of a given type.
 *
 * @param string $expected
 * @param string $attributeName
 * @param mixed  $classOrObject
 * @param string $message
 */
function assertAttributeInstanceOf($expected, $attributeName, $classOrObject, $message = '')
{
    return Assert::assertAttributeInstanceOf(...\func_get_args());
}

/**
 * Asserts that an attribute is of a given type.
 *
 * @param string $expected
 * @param string $attributeName
 * @param mixed  $classOrObject
 * @param string $message
 */
function assertAttributeInternalType($expected, $attributeName, $classOrObject, $message = '')
{
    return Assert::assertAttributeInternalType(...\func_get_args());
}

/**
 * Asserts that an attribute is smaller than another value.
 *
 * @param mixed  $expected
 * @param string $actualAttributeName
 * @param string $actualClassOrObject
 * @param string $message
 */
function assertAttributeLessThan($expected, $actualAttributeName, $actualClassOrObject, $message = '')
{
    return Assert::assertAttributeLessThan(...\func_get_args());
}

/**
 * Asserts that an attribute is smaller than or equal to another value.
 *
 * @param mixed  $expected
 * @param string $actualAttributeName
 * @param string $actualClassOrObject
 * @param string $message
 */
function assertAttributeLessThanOrEqual($expected, $actualAttributeName, $actualClassOrObject, $message = '')
{
    return Assert::assertAttributeLessThanOrEqual(...\func_get_args());
}

/**
 * Asserts that a haystack that is stored in a static attribute of a class
 * or an attribute of an object does not contain a needle.
 *
 * @param mixed  $needle
 * @param string $haystackAttributeName
 * @param mixed  $haystackClassOrObject
 * @param string $message
 * @param bool   $ignoreCase
 * @param bool   $checkForObjectIdentity
 * @param bool   $checkForNonObjectIdentity
 */
function assertAttributeNotContains($needle, $haystackAttributeName, $haystackClassOrObject, $message = '', $ignoreCase = false, $checkForObjectIdentity = true, $checkForNonObjectIdentity = false)
{
    return Assert::assertAttributeNotContains(...\func_get_args());
}

/**
 * Asserts that a haystack that is stored in a static attribute of a class
 * or an attribute of an object does not contain only values of a given
 * type.
 *
 * @param string $type
 * @param string $haystackAttributeName
 * @param mixed  $haystackClassOrObject
 * @param bool   $isNativeType
 * @param string $message
 */
function assertAttributeNotContainsOnly($type, $haystackAttributeName, $haystackClassOrObject, $isNativeType = null, $message = '')
{
    return Assert::assertAttributeNotContainsOnly(...\func_get_args());
}

/**
 * Asserts the number of elements of an array, Countable or Traversable
 * that is stored in an attribute.
 *
 * @param int    $expectedCount
 * @param string $haystackAttributeName
 * @param mixed  $haystackClassOrObject
 * @param string $message
 */
function assertAttributeNotCount($expectedCount, $haystackAttributeName, $haystackClassOrObject, $message = '')
{
    return Assert::assertAttributeNotCount(...\func_get_args());
}

/**
 * Asserts that a static attribute of a class or an attribute of an object
 * is not empty.
 *
 * @param string $haystackAttributeName
 * @param mixed  $haystackClassOrObject
 * @param string $message
 */
function assertAttributeNotEmpty($haystackAttributeName, $haystackClassOrObject, $message = '')
{
    return Assert::assertAttributeNotEmpty(...\func_get_args());
}

/**
 * Asserts that a variable is not equal to an attribute of an object.
 *
 * @param mixed  $expected
 * @param string $actualAttributeName
 * @param string $actualClassOrObject
 * @param string $message
 * @param float  $delta
 * @param int    $maxDepth
 * @param bool   $canonicalize
 * @param bool   $ignoreCase
 */
function assertAttributeNotEquals($expected, $actualAttributeName, $actualClassOrObject, $message = '', $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
{
    return Assert::assertAttributeNotEquals(...\func_get_args());
}

/**
 * Asserts that an attribute is of a given type.
 *
 * @param string $expected
 * @param string $attributeName
 * @param mixed  $classOrObject
 * @param string $message
 */
function assertAttributeNotInstanceOf($expected, $attributeName, $classOrObject, $message = '')
{
    return Assert::assertAttributeNotInstanceOf(...\func_get_args());
}

/**
 * Asserts that an attribute is of a given type.
 *
 * @param string $expected
 * @param string $attributeName
 * @param mixed  $classOrObject
 * @param string $message
 */
function assertAttributeNotInternalType($expected, $attributeName, $classOrObject, $message = '')
{
    return Assert::assertAttributeNotInternalType(...\func_get_args());
}

/**
 * Asserts that a variable and an attribute of an object do not have the
 * same type and value.
 *
 * @param mixed  $expected
 * @param string $actualAttributeName
 * @param object $actualClassOrObject
 * @param string $message
 */
function assertAttributeNotSame($expected, $actualAttributeName, $actualClassOrObject, $message = '')
{
    return Assert::assertAttributeNotSame(...\func_get_args());
}

/**
 * Asserts that a variable and an attribute of an object have the same type
 * and value.
 *
 * @param mixed  $expected
 * @param string $actualAttributeName
 * @param object $actualClassOrObject
 * @param string $message
 */
function assertAttributeSame($expected, $actualAttributeName, $actualClassOrObject, $message = '')
{
    return Assert::assertAttributeSame(...\func_get_args());
}

/**
 * Asserts that a class has a specified attribute.
 *
 * @param string $attributeName
 * @param string $className
 * @param string $message
 */
function assertClassHasAttribute($attributeName, $className, $message = '')
{
    return Assert::assertClassHasAttribute(...\func_get_args());
}

/**
 * Asserts that a class has a specified static attribute.
 *
 * @param string $attributeName
 * @param string $className
 * @param string $message
 */
function assertClassHasStaticAttribute($attributeName, $className, $message = '')
{
    return Assert::assertClassHasStaticAttribute(...\func_get_args());
}

/**
 * Asserts that a class does not have a specified attribute.
 *
 * @param string $attributeName
 * @param string $className
 * @param string $message
 */
function assertClassNotHasAttribute($attributeName, $className, $message = '')
{
    return Assert::assertClassNotHasAttribute(...\func_get_args());
}

/**
 * Asserts that a class does not have a specified static attribute.
 *
 * @param string $attributeName
 * @param string $className
 * @param string $message
 */
function assertClassNotHasStaticAttribute($attributeName, $className, $message = '')
{
    return Assert::assertClassNotHasStaticAttribute(...\func_get_args());
}

/**
 * Asserts that a haystack contains a needle.
 *
 * @param mixed  $needle
 * @param mixed  $haystack
 * @param string $message
 * @param bool   $ignoreCase
 * @param bool   $checkForObjectIdentity
 * @param bool   $checkForNonObjectIdentity
 */
function assertContains($needle, $haystack, $message = '', $ignoreCase = false, $checkForObjectIdentity = true, $checkForNonObjectIdentity = false)
{
    return Assert::assertContains(...\func_get_args());
}

/**
 * Asserts that a haystack contains only values of a given type.
 *
 * @param string $type
 * @param mixed  $haystack
 * @param bool   $isNativeType
 * @param string $message
 */
function assertContainsOnly($type, $haystack, $isNativeType = null, $message = '')
{
    return Assert::assertContainsOnly(...\func_get_args());
}

/**
 * Asserts that a haystack contains only instances of a given classname
 *
 * @param string            $classname
 * @param array|Traversable $haystack
 * @param string            $message
 */
function assertContainsOnlyInstancesOf($classname, $haystack, $message = '')
{
    return Assert::assertContainsOnlyInstancesOf(...\func_get_args());
}

/**
 * Asserts the number of elements of an array, Countable or Traversable.
 *
 * @param int    $expectedCount
 * @param mixed  $haystack
 * @param string $message
 */
function assertCount($expectedCount, $haystack, $message = '')
{
    return Assert::assertCount(...\func_get_args());
}

/**
 * Asserts that a variable is empty.
 *
 * @param mixed  $actual
 * @param string $message
 *
 * @throws AssertionFailedError
 */
function assertEmpty($actual, $message = '')
{
    return Assert::assertEmpty(...\func_get_args());
}

/**
 * Asserts that a hierarchy of DOMElements matches.
 *
 * @param DOMElement $expectedElement
 * @param DOMElement $actualElement
 * @param bool       $checkAttributes
 * @param string     $message
 */
function assertEqualXMLStructure(DOMElement $expectedElement, DOMElement $actualElement, $checkAttributes = false, $message = '')
{
    return Assert::assertEqualXMLStructure(...\func_get_args());
}

/**
 * Asserts that two variables are equal.
 *
 * @param mixed  $expected
 * @param mixed  $actual
 * @param string $message
 * @param float  $delta
 * @param int    $maxDepth
 * @param bool   $canonicalize
 * @param bool   $ignoreCase
 */
function assertEquals($expected, $actual, $message = '', $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
{
    return Assert::assertEquals(...\func_get_args());
}

/**
 * Asserts that a condition is not true.
 *
 * @param bool   $condition
 * @param string $message
 *
 * @throws AssertionFailedError
 */
function assertNotTrue($condition, $message = '')
{
    return Assert::assertNotTrue(...\func_get_args());
}

/**
 * Asserts that a condition is false.
 *
 * @param bool   $condition
 * @param string $message
 *
 * @throws AssertionFailedError
 */
function assertFalse($condition, $message = '')
{
    return Assert::assertFalse(...\func_get_args());
}

/**
 * Asserts that the contents of one file is equal to the contents of another
 * file.
 *
 * @param string $expected
 * @param string $actual
 * @param string $message
 * @param bool   $canonicalize
 * @param bool   $ignoreCase
 */
function assertFileEquals($expected, $actual, $message = '', $canonicalize = false, $ignoreCase = false)
{
    return Assert::assertFileEquals(...\func_get_args());
}

/**
 * Asserts that a file exists.
 *
 * @param string $filename
 * @param string $message
 */
function assertFileExists($filename, $message = '')
{
    return Assert::assertFileExists(...\func_get_args());
}

/**
 * Asserts that the contents of one file is not equal to the contents of
 * another file.
 *
 * @param string $expected
 * @param string $actual
 * @param string $message
 * @param bool   $canonicalize
 * @param bool   $ignoreCase
 */
function assertFileNotEquals($expected, $actual, $message = '', $canonicalize = false, $ignoreCase = false)
{
    return Assert::assertFileNotEquals(...\func_get_args());
}

/**
 * Asserts that a file does not exist.
 *
 * @param string $filename
 * @param string $message
 */
function assertFileNotExists($filename, $message = '')
{
    return Assert::assertFileNotExists(...\func_get_args());
}

/**
 * Asserts that a value is greater than another value.
 *
 * @param mixed  $expected
 * @param mixed  $actual
 * @param string $message
 */
function assertGreaterThan($expected, $actual, $message = '')
{
    return Assert::assertGreaterThan(...\func_get_args());
}

/**
 * Asserts that a value is greater than or equal to another value.
 *
 * @param mixed  $expected
 * @param mixed  $actual
 * @param string $message
 */
function assertGreaterThanOrEqual($expected, $actual, $message = '')
{
    return Assert::assertGreaterThanOrEqual(...\func_get_args());
}

/**
 * Asserts that a variable is of a given type.
 *
 * @param string $expected
 * @param mixed  $actual
 * @param string $message
 */
function assertInstanceOf($expected, $actual, $message = '')
{
    return Assert::assertInstanceOf(...\func_get_args());
}

/**
 * Asserts that a variable is of a given type.
 *
 * @param string $expected
 * @param mixed  $actual
 * @param string $message
 */
function assertInternalType($expected, $actual, $message = '')
{
    return Assert::assertInternalType(...\func_get_args());
}

/**
 * Asserts that a string is a valid JSON string.
 *
 * @param string $actualJson
 * @param string $message
 */
function assertJson($actualJson, $message = '')
{
    return Assert::assertJson(...\func_get_args());
}

/**
 * Asserts that two JSON files are equal.
 *
 * @param string $expectedFile
 * @param string $actualFile
 * @param string $message
 */
function assertJsonFileEqualsJsonFile($expectedFile, $actualFile, $message = '')
{
    return Assert::assertJsonFileEqualsJsonFile(...\func_get_args());
}

/**
 * Asserts that two JSON files are not equal.
 *
 * @param string $expectedFile
 * @param string $actualFile
 * @param string $message
 */
function assertJsonFileNotEqualsJsonFile($expectedFile, $actualFile, $message = '')
{
    return Assert::assertJsonFileNotEqualsJsonFile(...\func_get_args());
}

/**
 * Asserts that the generated JSON encoded object and the content of the given file are equal.
 *
 * @param string $expectedFile
 * @param string $actualJson
 * @param string $message
 */
function assertJsonStringEqualsJsonFile($expectedFile, $actualJson, $message = '')
{
    return Assert::assertJsonStringEqualsJsonFile(...\func_get_args());
}

/**
 * Asserts that two given JSON encoded objects or arrays are equal.
 *
 * @param string $expectedJson
 * @param string $actualJson
 * @param string $message
 */
function assertJsonStringEqualsJsonString($expectedJson, $actualJson, $message = '')
{
    return Assert::assertJsonStringEqualsJsonString(...\func_get_args());
}

/**
 * Asserts that the generated JSON encoded object and the content of the given file are not equal.
 *
 * @param string $expectedFile
 * @param string $actualJson
 * @param string $message
 */
function assertJsonStringNotEqualsJsonFile($expectedFile, $actualJson, $message = '')
{
    return Assert::assertJsonStringNotEqualsJsonFile(...\func_get_args());
}

/**
 * Asserts that two given JSON encoded objects or arrays are not equal.
 *
 * @param string $expectedJson
 * @param string $actualJson
 * @param string $message
 */
function assertJsonStringNotEqualsJsonString($expectedJson, $actualJson, $message = '')
{
    return Assert::assertJsonStringNotEqualsJsonString(...\func_get_args());
}

/**
 * Asserts that a value is smaller than another value.
 *
 * @param mixed  $expected
 * @param mixed  $actual
 * @param string $message
 */
function assertLessThan($expected, $actual, $message = '')
{
    return Assert::assertLessThan(...\func_get_args());
}

/**
 * Asserts that a value is smaller than or equal to another value.
 *
 * @param mixed  $expected
 * @param mixed  $actual
 * @param string $message
 */
function assertLessThanOrEqual($expected, $actual, $message = '')
{
    return Assert::assertLessThanOrEqual(...\func_get_args());
}

/**
 * Asserts that a variable is finite.
 *
 * @param mixed  $actual
 * @param string $message
 */
function assertFinite($actual, $message = '')
{
    return Assert::assertFinite(...\func_get_args());
}

/**
 * Asserts that a variable is infinite.
 *
 * @param mixed  $actual
 * @param string $message
 */
function assertInfinite($actual, $message = '')
{
    return Assert::assertInfinite(...\func_get_args());
}

/**
 * Asserts that a variable is nan.
 *
 * @param mixed  $actual
 * @param string $message
 */
function assertNan($actual, $message = '')
{
    return Assert::assertNan(...\func_get_args());
}

/**
 * Asserts that a haystack does not contain a needle.
 *
 * @param mixed  $needle
 * @param mixed  $haystack
 * @param string $message
 * @param bool   $ignoreCase
 * @param bool   $checkForObjectIdentity
 * @param bool   $checkForNonObjectIdentity
 */
function assertNotContains($needle, $haystack, $message = '', $ignoreCase = false, $checkForObjectIdentity = true, $checkForNonObjectIdentity = false)
{
    return Assert::assertNotContains(...\func_get_args());
}

/**
 * Asserts that a haystack does not contain only values of a given type.
 *
 * @param string $type
 * @param mixed  $haystack
 * @param bool   $isNativeType
 * @param string $message
 */
function assertNotContainsOnly($type, $haystack, $isNativeType = null, $message = '')
{
    return Assert::assertNotContainsOnly(...\func_get_args());
}

/**
 * Asserts the number of elements of an array, Countable or Traversable.
 *
 * @param int    $expectedCount
 * @param mixed  $haystack
 * @param string $message
 */
function assertNotCount($expectedCount, $haystack, $message = '')
{
    return Assert::assertNotCount(...\func_get_args());
}

/**
 * Asserts that a variable is not empty.
 *
 * @param mixed  $actual
 * @param string $message
 *
 * @throws AssertionFailedError
 */
function assertNotEmpty($actual, $message = '')
{
    return Assert::assertNotEmpty(...\func_get_args());
}

/**
 * Asserts that two variables are not equal.
 *
 * @param mixed  $expected
 * @param mixed  $actual
 * @param string $message
 * @param float  $delta
 * @param int    $maxDepth
 * @param bool   $canonicalize
 * @param bool   $ignoreCase
 */
function assertNotEquals($expected, $actual, $message = '', $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
{
    return Assert::assertNotEquals(...\func_get_args());
}

/**
 * Asserts that a variable is not of a given type.
 *
 * @param string $expected
 * @param mixed  $actual
 * @param string $message
 */
function assertNotInstanceOf($expected, $actual, $message = '')
{
    return Assert::assertNotInstanceOf(...\func_get_args());
}

/**
 * Asserts that a variable is not of a given type.
 *
 * @param string $expected
 * @param mixed  $actual
 * @param string $message
 */
function assertNotInternalType($expected, $actual, $message = '')
{
    return Assert::assertNotInternalType(...\func_get_args());
}

/**
 * Asserts that a condition is not false.
 *
 * @param bool   $condition
 * @param string $message
 *
 * @throws AssertionFailedError
 */
function assertNotFalse($condition, $message = '')
{
    return Assert::assertNotFalse(...\func_get_args());
}

/**
 * Asserts that a variable is not null.
 *
 * @param mixed  $actual
 * @param string $message
 */
function assertNotNull($actual, $message = '')
{
    return Assert::assertNotNull(...\func_get_args());
}

/**
 * Asserts that a string does not match a given regular expression.
 *
 * @param string $pattern
 * @param string $string
 * @param string $message
 */
function assertNotRegExp($pattern, $string, $message = '')
{
    return Assert::assertNotRegExp(...\func_get_args());
}

/**
 * Asserts that two variables do not have the same type and value.
 * Used on objects, it asserts that two variables do not reference
 * the same object.
 *
 * @param mixed  $expected
 * @param mixed  $actual
 * @param string $message
 */
function assertNotSame($expected, $actual, $message = '')
{
    return Assert::assertNotSame(...\func_get_args());
}

/**
 * Assert that the size of two arrays (or `Countable` or `Traversable` objects)
 * is not the same.
 *
 * @param array|Countable|Traversable $expected
 * @param array|Countable|Traversable $actual
 * @param string                      $message
 */
function assertNotSameSize($expected, $actual, $message = '')
{
    return Assert::assertNotSameSize(...\func_get_args());
}

/**
 * Asserts that a variable is null.
 *
 * @param mixed  $actual
 * @param string $message
 */
function assertNull($actual, $message = '')
{
    return Assert::assertNull(...\func_get_args());
}

/**
 * Asserts that an object has a specified attribute.
 *
 * @param string $attributeName
 * @param object $object
 * @param string $message
 */
function assertObjectHasAttribute($attributeName, $object, $message = '')
{
    return Assert::assertObjectHasAttribute(...\func_get_args());
}

/**
 * Asserts that an object does not have a specified attribute.
 *
 * @param string $attributeName
 * @param object $object
 * @param string $message
 */
function assertObjectNotHasAttribute($attributeName, $object, $message = '')
{
    return Assert::assertObjectNotHasAttribute(...\func_get_args());
}

/**
 * Asserts that a string matches a given regular expression.
 *
 * @param string $pattern
 * @param string $string
 * @param string $message
 */
function assertRegExp($pattern, $string, $message = '')
{
    return Assert::assertRegExp(...\func_get_args());
}

/**
 * Asserts that two variables have the same type and value.
 * Used on objects, it asserts that two variables reference
 * the same object.
 *
 * @param mixed  $expected
 * @param mixed  $actual
 * @param string $message
 */
function assertSame($expected, $actual, $message = '')
{
    return Assert::assertSame(...\func_get_args());
}

/**
 * Assert that the size of two arrays (or `Countable` or `Traversable` objects)
 * is the same.
 *
 * @param array|Countable|Traversable $expected
 * @param array|Countable|Traversable $actual
 * @param string                      $message
 */
function assertSameSize($expected, $actual, $message = '')
{
    return Assert::assertSameSize(...\func_get_args());
}

/**
 * Asserts that a string ends not with a given prefix.
 *
 * @param string $suffix
 * @param string $string
 * @param string $message
 */
function assertStringEndsNotWith($suffix, $string, $message = '')
{
    return Assert::assertStringEndsNotWith(...\func_get_args());
}

/**
 * Asserts that a string ends with a given prefix.
 *
 * @param string $suffix
 * @param string $string
 * @param string $message
 */
function assertStringEndsWith($suffix, $string, $message = '')
{
    return Assert::assertStringEndsWith(...\func_get_args());
}

/**
 * Asserts that the contents of a string is equal
 * to the contents of a file.
 *
 * @param string $expectedFile
 * @param string $actualString
 * @param string $message
 * @param bool   $canonicalize
 * @param bool   $ignoreCase
 */
function assertStringEqualsFile($expectedFile, $actualString, $message = '', $canonicalize = false, $ignoreCase = false)
{
    return Assert::assertStringEqualsFile(...\func_get_args());
}

/**
 * Asserts that a string matches a given format string.
 *
 * @param string $format
 * @param string $string
 * @param string $message
 */
function assertStringMatchesFormat($format, $string, $message = '')
{
    return Assert::assertStringMatchesFormat(...\func_get_args());
}

/**
 * Asserts that a string matches a given format file.
 *
 * @param string $formatFile
 * @param string $string
 * @param string $message
 */
function assertStringMatchesFormatFile($formatFile, $string, $message = '')
{
    return Assert::assertStringMatchesFormatFile(...\func_get_args());
}

/**
 * Asserts that the contents of a string is not equal
 * to the contents of a file.
 *
 * @param string $expectedFile
 * @param string $actualString
 * @param string $message
 * @param bool   $canonicalize
 * @param bool   $ignoreCase
 */
function assertStringNotEqualsFile($expectedFile, $actualString, $message = '', $canonicalize = false, $ignoreCase = false)
{
    return Assert::assertStringNotEqualsFile(...\func_get_args());
}

/**
 * Asserts that a string does not match a given format string.
 *
 * @param string $format
 * @param string $string
 * @param string $message
 */
function assertStringNotMatchesFormat($format, $string, $message = '')
{
    return Assert::assertStringNotMatchesFormat(...\func_get_args());
}

/**
 * Asserts that a string does not match a given format string.
 *
 * @param string $formatFile
 * @param string $string
 * @param string $message
 */
function assertStringNotMatchesFormatFile($formatFile, $string, $message = '')
{
    return Assert::assertStringNotMatchesFormatFile(...\func_get_args());
}

/**
 * Asserts that a string starts not with a given prefix.
 *
 * @param string $prefix
 * @param string $string
 * @param string $message
 */
function assertStringStartsNotWith($prefix, $string, $message = '')
{
    return Assert::assertStringStartsNotWith(...\func_get_args());
}

/**
 * Asserts that a string starts with a given prefix.
 *
 * @param string $prefix
 * @param string $string
 * @param string $message
 */
function assertStringStartsWith($prefix, $string, $message = '')
{
    return Assert::assertStringStartsWith(...\func_get_args());
}

/**
 * Evaluates a PHPUnit\Framework\Constraint matcher object.
 *
 * @param mixed      $value
 * @param Constraint $constraint
 * @param string     $message
 */
function assertThat($value, Constraint $constraint, $message = '')
{
    return Assert::assertThat(...\func_get_args());
}

/**
 * Asserts that a condition is true.
 *
 * @param bool   $condition
 * @param string $message
 *
 * @throws AssertionFailedError
 */
function assertTrue($condition, $message = '')
{
    return Assert::assertTrue(...\func_get_args());
}

/**
 * Asserts that two XML files are equal.
 *
 * @param string $expectedFile
 * @param string $actualFile
 * @param string $message
 */
function assertXmlFileEqualsXmlFile($expectedFile, $actualFile, $message = '')
{
    return Assert::assertXmlFileEqualsXmlFile(...\func_get_args());
}

/**
 * Asserts that two XML files are not equal.
 *
 * @param string $expectedFile
 * @param string $actualFile
 * @param string $message
 */
function assertXmlFileNotEqualsXmlFile($expectedFile, $actualFile, $message = '')
{
    return Assert::assertXmlFileNotEqualsXmlFile(...\func_get_args());
}

/**
 * Asserts that two XML documents are equal.
 *
 * @param string             $expectedFile
 * @param string|DOMDocument $actualXml
 * @param string             $message
 */
function assertXmlStringEqualsXmlFile($expectedFile, $actualXml, $message = '')
{
    return Assert::assertXmlStringEqualsXmlFile(...\func_get_args());
}

/**
 * Asserts that two XML documents are equal.
 *
 * @param string|DOMDocument $expectedXml
 * @param string|DOMDocument $actualXml
 * @param string             $message
 */
function assertXmlStringEqualsXmlString($expectedXml, $actualXml, $message = '')
{
    return Assert::assertXmlStringEqualsXmlString(...\func_get_args());
}

/**
 * Asserts that two XML documents are not equal.
 *
 * @param string             $expectedFile
 * @param string|DOMDocument $actualXml
 * @param string             $message
 */
function assertXmlStringNotEqualsXmlFile($expectedFile, $actualXml, $message = '')
{
    return Assert::assertXmlStringNotEqualsXmlFile(...\func_get_args());
}

/**
 * Asserts that two XML documents are not equal.
 *
 * @param string|DOMDocument $expectedXml
 * @param string|DOMDocument $actualXml
 * @param string             $message
 */
function assertXmlStringNotEqualsXmlString($expectedXml, $actualXml, $message = '')
{
    return Assert::assertXmlStringNotEqualsXmlString(...\func_get_args());
}

/**
 * Returns a matcher that matches when the method is executed
 * at the given $index.
 *
 * @param int $index
 *
 * @return PHPUnit_Framework_MockObject_Matcher_InvokedAtIndex
 */
function at($index)
{
    return TestCase::at(...\func_get_args());
}

/**
 * Returns a matcher that matches when the method is executed at least once.
 *
 * @return PHPUnit_Framework_MockObject_Matcher_InvokedAtLeastOnce
 */
function atLeastOnce()
{
    return TestCase::atLeastOnce();
}

/**
 * Returns a PHPUnit\Framework\Constraint\Attribute matcher object.
 *
 * @param Constraint $constraint
 * @param string     $attributeName
 *
 * @return Attribute
 */
function attribute(Constraint $constraint, $attributeName)
{
    return Assert::attribute(...\func_get_args());
}

/**
 * Returns a PHPUnit\Framework\Constraint\IsEqual matcher object
 * that is wrapped in a PHPUnit\Framework\Constraint\Attribute matcher
 * object.
 *
 * @param string $attributeName
 * @param mixed  $value
 * @param float  $delta
 * @param int    $maxDepth
 * @param bool   $canonicalize
 * @param bool   $ignoreCase
 *
 * @return Attribute
 */
function attributeEqualTo($attributeName, $value, $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
{
    return Assert::attributeEqualTo(...\func_get_args());
}

/**
 * Returns a PHPUnit\Framework\Constraint\Callback matcher object.
 *
 * @param callable $callback
 *
 * @return Callback
 */
function callback($callback)
{
    return Assert::callback(...\func_get_args());
}

/**
 * Returns a PHPUnit\Framework\Constraint\ClassHasAttribute matcher object.
 *
 * @param string $attributeName
 *
 * @return ClassHasAttribute
 */
function classHasAttribute($attributeName)
{
    return Assert::classHasAttribute(...\func_get_args());
}

/**
 * Returns a PHPUnit\Framework\Constraint\ClassHasStaticAttribute matcher
 * object.
 *
 * @param string $attributeName
 *
 * @return ClassHasStaticAttribute
 */
function classHasStaticAttribute($attributeName)
{
    return Assert::classHasStaticAttribute(...\func_get_args());
}

/**
 * Returns a PHPUnit\Framework\Constraint\TraversableContains matcher
 * object.
 *
 * @param mixed $value
 * @param bool  $checkForObjectIdentity
 * @param bool  $checkForNonObjectIdentity
 *
 * @return TraversableContains
 */
function contains($value, $checkForObjectIdentity = true, $checkForNonObjectIdentity = false)
{
    return Assert::contains(...\func_get_args());
}

/**
 * Returns a PHPUnit\Framework\Constraint\TraversableContainsOnly matcher
 * object.
 *
 * @param string $type
 *
 * @return TraversableContainsOnly
 */
function containsOnly($type)
{
    return Assert::containsOnly(...\func_get_args());
}

/**
 * Returns a PHPUnit\Framework\Constraint\TraversableContainsOnly matcher
 * object.
 *
 * @param string $classname
 *
 * @return TraversableContainsOnly
 */
function containsOnlyInstancesOf($classname)
{
    return Assert::containsOnlyInstancesOf(...\func_get_args());
}

/**
 * Returns a PHPUnit\Framework\Constraint\Count matcher object.
 *
 * @param int $count
 *
 * @return Count
 */
function countOf($count)
{
    return Assert::countOf(...\func_get_args());
}

/**
 * Returns a PHPUnit\Framework\Constraint\DirectoryExists matcher object.
 *
 * @return DirectoryExists
 */
function directoryExists()
{
    return Assert::directoryExists();
}

/**
 * Returns a PHPUnit\Framework\Constraint\IsEqual matcher object.
 *
 * @param mixed $value
 * @param float $delta
 * @param int   $maxDepth
 * @param bool  $canonicalize
 * @param bool  $ignoreCase
 *
 * @return IsEqual
 */
function equalTo($value, $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
{
    return Assert::equalTo(...\func_get_args());
}

/**
 * Returns a matcher that matches when the method is executed
 * exactly $count times.
 *
 * @param int $count
 *
 * @return PHPUnit_Framework_MockObject_Matcher_InvokedCount
 */
function exactly($count)
{
    return TestCase::exactly(...\func_get_args());
}

/**
 * Returns a PHPUnit\Framework\Constraint\FileExists matcher object.
 *
 * @return FileExists
 */
function fileExists()
{
    return Assert::fileExists();
}

/**
 * Returns a PHPUnit\Framework\Constraint\GreaterThan matcher object.
 *
 * @param mixed $value
 *
 * @return GreaterThan
 */
function greaterThan($value)
{
    return Assert::greaterThan(...\func_get_args());
}

/**
 * Returns a PHPUnit\Framework\Constraint\Or matcher object that wraps
 * a PHPUnit\Framework\Constraint\IsEqual and a
 * PHPUnit\Framework\Constraint\GreaterThan matcher object.
 *
 * @param mixed $value
 *
 * @return LogicalOr
 */
function greaterThanOrEqual($value)
{
    return Assert::greaterThanOrEqual(...\func_get_args());
}

/**
 * Returns a PHPUnit\Framework\Constraint\IsIdentical matcher object.
 *
 * @param mixed $value
 *
 * @return IsIdentical
 */
function identicalTo($value)
{
    return Assert::identicalTo(...\func_get_args());
}

/**
 * Returns a PHPUnit\Framework\Constraint\IsEmpty matcher object.
 *
 * @return IsEmpty
 */
function isEmpty()
{
    return Assert::isEmpty();
}

/**
 * Returns a PHPUnit\Framework\Constraint\IsFalse matcher object.
 *
 * @return IsFalse
 */
function isFalse()
{
    return Assert::isFalse();
}

/**
 * Returns a PHPUnit\Framework\Constraint\IsInfinite matcher object.
 *
 * @return IsInfinite
 */
function isInfinite()
{
    return Assert::isInfinite();
}

/**
 * Returns a PHPUnit\Framework\Constraint\IsInstanceOf matcher object.
 *
 * @param string $className
 *
 * @return IsInstanceOf
 */
function isInstanceOf($className)
{
    return Assert::isInstanceOf(...\func_get_args());
}

/**
 * Returns a PHPUnit\Framework\Constraint\IsJson matcher object.
 *
 * @return IsJson
 */
function isJson()
{
    return Assert::isJson();
}

/**
 * Returns a PHPUnit\Framework\Constraint\IsNan matcher object.
 *
 * @return IsNan
 */
function isNan()
{
    return Assert::isNan();
}

/**
 * Returns a PHPUnit\Framework\Constraint\IsNull matcher object.
 *
 * @return IsNull
 */
function isNull()
{
    return Assert::isNull();
}

/**
 * Returns a PHPUnit\Framework\Constraint\IsReadable matcher object.
 *
 * @return IsReadable
 */
function isReadable()
{
    return Assert::isReadable();
}

/**
 * Returns a PHPUnit\Framework\Constraint\IsTrue matcher object.
 *
 * @return IsTrue
 */
function isTrue()
{
    return Assert::isTrue();
}

/**
 * Returns a PHPUnit\Framework\Constraint\IsType matcher object.
 *
 * @param string $type
 *
 * @return IsType
 */
function isType($type)
{
    return Assert::isType(...\func_get_args());
}

/**
 * Returns a PHPUnit\Framework\Constraint\IsWritable matcher object.
 *
 * @return IsWritable
 */
function isWritable()
{
    return Assert::isWritable();
}

/**
 * Returns a PHPUnit\Framework\Constraint\LessThan matcher object.
 *
 * @param mixed $value
 *
 * @return LessThan
 */
function lessThan($value)
{
    return Assert::lessThan(...\func_get_args());
}

/**
 * Returns a PHPUnit\Framework\Constraint\Or matcher object that wraps
 * a PHPUnit\Framework\Constraint\IsEqual and a
 * PHPUnit\Framework\Constraint\LessThan matcher object.
 *
 * @param mixed $value
 *
 * @return LogicalOr
 */
function lessThanOrEqual($value)
{
    return Assert::lessThanOrEqual(...\func_get_args());
}

/**
 * Returns a PHPUnit\Framework\Constraint\And matcher object.
 *
 * @return LogicalAnd
 */
function logicalAnd()
{
    return Assert::logicalAnd(...\func_get_args());
}

/**
 * Returns a PHPUnit\Framework\Constraint\Not matcher object.
 *
 * @param Constraint $constraint
 *
 * @return LogicalNot
 */
function logicalNot(Constraint $constraint)
{
    return Assert::logicalNot(...\func_get_args());
}

/**
 * Returns a PHPUnit\Framework\Constraint\Or matcher object.
 *
 * @return LogicalOr
 */
function logicalOr()
{
    return Assert::logicalOr(...\func_get_args());
}

/**
 * Returns a PHPUnit\Framework\Constraint\Xor matcher object.
 *
 * @return LogicalXor
 */
function logicalXor()
{
    return Assert::logicalXor(...\func_get_args());
}

/**
 * Returns a PHPUnit\Framework\Constraint\StringMatches matcher object.
 *
 * @param string $string
 *
 * @return StringMatchesFormatDescription
 */
function matches($string)
{
    return Assert::matches(...\func_get_args());
}

/**
 * Returns a PHPUnit\Framework\Constraint\PCREMatch matcher object.
 *
 * @param string $pattern
 *
 * @return RegularExpression
 */
function matchesRegularExpression($pattern)
{
    return Assert::matchesRegularExpression(...\func_get_args());
}

/**
 * Returns a matcher that matches when the method is never executed.
 *
 * @return PHPUnit_Framework_MockObject_Matcher_InvokedCount
 */
function never()
{
    return TestCase::never();
}

/**
 * Returns a PHPUnit\Framework\Constraint\ObjectHasAttribute matcher object.
 *
 * @param string $attributeName
 *
 * @return ObjectHasAttribute
 */
function objectHasAttribute($attributeName)
{
    return Assert::objectHasAttribute(...\func_get_args());
}

/**
 * @param mixed $value, ...
 *
 * @return PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls
 */
function onConsecutiveCalls()
{
    return TestCase::onConsecutiveCalls(...\func_get_args());
}

/**
 * Returns a matcher that matches when the method is executed exactly once.
 *
 * @return PHPUnit_Framework_MockObject_Matcher_InvokedCount
 */
function once()
{
    return TestCase::once();
}

/**
 * @param int $argumentIndex
 *
 * @return PHPUnit_Framework_MockObject_Stub_ReturnArgument
 */
function returnArgument($argumentIndex)
{
    return TestCase::returnArgument(...\func_get_args());
}

/**
 * @param mixed $callback
 *
 * @return PHPUnit_Framework_MockObject_Stub_ReturnCallback
 */
function returnCallback($callback)
{
    return TestCase::returnCallback(...\func_get_args());
}

/**
 * Returns the current object.
 *
 * This method is useful when mocking a fluent interface.
 *
 * @return PHPUnit_Framework_MockObject_Stub_ReturnSelf
 */
function returnSelf()
{
    return TestCase::returnSelf();
}

/**
 * @param mixed $value
 *
 * @return PHPUnit_Framework_MockObject_Stub_Return
 */
function returnValue($value)
{
    return TestCase::returnValue(...\func_get_args());
}

/**
 * @param array $valueMap
 *
 * @return PHPUnit_Framework_MockObject_Stub_ReturnValueMap
 */
function returnValueMap(array $valueMap)
{
    return TestCase::returnValueMap(...\func_get_args());
}

/**
 * Returns a PHPUnit\Framework\Constraint\StringContains matcher object.
 *
 * @param string $string
 * @param bool   $case
 *
 * @return StringContains
 */
function stringContains($string, $case = true)
{
    return Assert::stringContains(...\func_get_args());
}

/**
 * Returns a PHPUnit\Framework\Constraint\StringEndsWith matcher object.
 *
 * @param mixed $suffix
 *
 * @return StringEndsWith
 */
function stringEndsWith($suffix)
{
    return Assert::stringEndsWith(...\func_get_args());
}

/**
 * Returns a PHPUnit\Framework\Constraint\StringStartsWith matcher object.
 *
 * @param mixed $prefix
 *
 * @return StringStartsWith
 */
function stringStartsWith($prefix)
{
    return Assert::stringStartsWith(...\func_get_args());
}

/**
 * @param Exception $exception
 *
 * @return PHPUnit_Framework_MockObject_Stub_Exception
 */
function throwException(Exception $exception)
{
    return TestCase::throwException(...\func_get_args());
}
