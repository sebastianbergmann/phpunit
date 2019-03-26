<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\ArrayHasKey;
use PHPUnit\Framework\Constraint\Attribute;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\Constraint\ClassHasAttribute;
use PHPUnit\Framework\Constraint\ClassHasStaticAttribute;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\Count;
use PHPUnit\Framework\Constraint\DirectoryExists;
use PHPUnit\Framework\Constraint\FileExists;
use PHPUnit\Framework\Constraint\GreaterThan;
use PHPUnit\Framework\Constraint\IsAnything;
use PHPUnit\Framework\Constraint\IsEmpty;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\Constraint\IsFalse;
use PHPUnit\Framework\Constraint\IsFinite;
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
use PHPUnit\Framework\Constraint\LogicalAnd;
use PHPUnit\Framework\Constraint\LogicalNot;
use PHPUnit\Framework\Constraint\LogicalOr;
use PHPUnit\Framework\Constraint\LogicalXor;
use PHPUnit\Framework\Constraint\ObjectHasAttribute;
use PHPUnit\Framework\Constraint\RegularExpression;
use PHPUnit\Framework\Constraint\StringContains;
use PHPUnit\Framework\Constraint\StringEndsWith;
use PHPUnit\Framework\Constraint\StringMatchesFormatDescription;
use PHPUnit\Framework\Constraint\StringStartsWith;
use PHPUnit\Framework\Constraint\TraversableContains;
use PHPUnit\Framework\Constraint\TraversableContainsOnly;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Matcher\AnyInvokedCount as AnyInvokedCountMatcher;
use PHPUnit\Framework\MockObject\Matcher\InvokedAtIndex as InvokedAtIndexMatcher;
use PHPUnit\Framework\MockObject\Matcher\InvokedAtLeastCount as InvokedAtLeastCountMatcher;
use PHPUnit\Framework\MockObject\Matcher\InvokedAtLeastOnce as InvokedAtLeastOnceMatcher;
use PHPUnit\Framework\MockObject\Matcher\InvokedAtMostCount as InvokedAtMostCountMatcher;
use PHPUnit\Framework\MockObject\Matcher\InvokedCount as InvokedCountMatcher;
use PHPUnit\Framework\MockObject\Stub\ConsecutiveCalls as ConsecutiveCallsStub;
use PHPUnit\Framework\MockObject\Stub\Exception as ExceptionStub;
use PHPUnit\Framework\MockObject\Stub\ReturnArgument as ReturnArgumentStub;
use PHPUnit\Framework\MockObject\Stub\ReturnCallback as ReturnCallbackStub;
use PHPUnit\Framework\MockObject\Stub\ReturnSelf as ReturnSelfStub;
use PHPUnit\Framework\MockObject\Stub\ReturnStub;
use PHPUnit\Framework\MockObject\Stub\ReturnValueMap as ReturnValueMapStub;

/**
 * Asserts that an array has a specified key.
 *
 * @param int|string        $key
 * @param array|ArrayAccess $array
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws Exception
 *
 * @see Assert::assertArrayHasKey
 */
function assertArrayHasKey($key, $array, string $message = ''): void
{
    Assert::assertArrayHasKey(...\func_get_args());
}

/**
 * Asserts that an array has a specified subset.
 *
 * @param array|ArrayAccess $subset
 * @param array|ArrayAccess $array
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws Exception
 *
 * @codeCoverageIgnore
 *
 * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3494
 * @see Assert::assertArraySubset
 */
function assertArraySubset($subset, $array, bool $checkForObjectIdentity = false, string $message = ''): void
{
    Assert::assertArraySubset(...\func_get_args());
}

/**
 * Asserts that an array does not have a specified key.
 *
 * @param int|string        $key
 * @param array|ArrayAccess $array
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws Exception
 *
 * @see Assert::assertArrayNotHasKey
 */
function assertArrayNotHasKey($key, $array, string $message = ''): void
{
    Assert::assertArrayNotHasKey(...\func_get_args());
}

/**
 * Asserts that a haystack contains a needle.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws Exception
 *
 * @see Assert::assertContains
 */
function assertContains($needle, $haystack, string $message = '', bool $ignoreCase = false, bool $checkForObjectIdentity = true, bool $checkForNonObjectIdentity = false): void
{
    Assert::assertContains(...\func_get_args());
}

function assertContainsEquals($needle, iterable $haystack, string $message = ''): void
{
    Assert::assertContainsEquals(...\func_get_args());
}

/**
 * Asserts that a haystack that is stored in a static attribute of a class
 * or an attribute of an object contains a needle.
 *
 * @param object|string $haystackClassOrObject
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws ReflectionException
 * @throws Exception
 *
 * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3338
 * @codeCoverageIgnore
 *
 * @see Assert::assertAttributeContains
 */
function assertAttributeContains($needle, string $haystackAttributeName, $haystackClassOrObject, string $message = '', bool $ignoreCase = false, bool $checkForObjectIdentity = true, bool $checkForNonObjectIdentity = false): void
{
    Assert::assertAttributeContains(...\func_get_args());
}

/**
 * Asserts that a haystack does not contain a needle.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws Exception
 *
 * @see Assert::assertNotContains
 */
function assertNotContains($needle, $haystack, string $message = '', bool $ignoreCase = false, bool $checkForObjectIdentity = true, bool $checkForNonObjectIdentity = false): void
{
    Assert::assertNotContains(...\func_get_args());
}

function assertNotContainsEquals($needle, iterable $haystack, string $message = ''): void
{
    Assert::assertNotContainsEquals(...\func_get_args());
}

/**
 * Asserts that a haystack that is stored in a static attribute of a class
 * or an attribute of an object does not contain a needle.
 *
 * @param object|string $haystackClassOrObject
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws ReflectionException
 * @throws Exception
 *
 * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3338
 * @codeCoverageIgnore
 *
 * @see Assert::assertAttributeNotContains
 */
function assertAttributeNotContains($needle, string $haystackAttributeName, $haystackClassOrObject, string $message = '', bool $ignoreCase = false, bool $checkForObjectIdentity = true, bool $checkForNonObjectIdentity = false): void
{
    Assert::assertAttributeNotContains(...\func_get_args());
}

/**
 * Asserts that a haystack contains only values of a given type.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertContainsOnly
 */
function assertContainsOnly(string $type, iterable $haystack, ?bool $isNativeType = null, string $message = ''): void
{
    Assert::assertContainsOnly(...\func_get_args());
}

/**
 * Asserts that a haystack contains only instances of a given class name.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertContainsOnlyInstancesOf
 */
function assertContainsOnlyInstancesOf(string $className, iterable $haystack, string $message = ''): void
{
    Assert::assertContainsOnlyInstancesOf(...\func_get_args());
}

/**
 * Asserts that a haystack that is stored in a static attribute of a class
 * or an attribute of an object contains only values of a given type.
 *
 * @param object|string $haystackClassOrObject
 * @param bool          $isNativeType
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws ReflectionException
 * @throws Exception
 *
 * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3338
 * @codeCoverageIgnore
 *
 * @see Assert::assertAttributeContainsOnly
 */
function assertAttributeContainsOnly(string $type, string $haystackAttributeName, $haystackClassOrObject, ?bool $isNativeType = null, string $message = ''): void
{
    Assert::assertAttributeContainsOnly(...\func_get_args());
}

/**
 * Asserts that a haystack does not contain only values of a given type.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertNotContainsOnly
 */
function assertNotContainsOnly(string $type, iterable $haystack, ?bool $isNativeType = null, string $message = ''): void
{
    Assert::assertNotContainsOnly(...\func_get_args());
}

/**
 * Asserts that a haystack that is stored in a static attribute of a class
 * or an attribute of an object does not contain only values of a given
 * type.
 *
 * @param object|string $haystackClassOrObject
 * @param bool          $isNativeType
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws ReflectionException
 * @throws Exception
 *
 * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3338
 * @codeCoverageIgnore
 *
 * @see Assert::assertAttributeNotContainsOnly
 */
function assertAttributeNotContainsOnly(string $type, string $haystackAttributeName, $haystackClassOrObject, ?bool $isNativeType = null, string $message = ''): void
{
    Assert::assertAttributeNotContainsOnly(...\func_get_args());
}

/**
 * Asserts the number of elements of an array, Countable or Traversable.
 *
 * @param Countable|iterable $haystack
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws Exception
 *
 * @see Assert::assertCount
 */
function assertCount(int $expectedCount, $haystack, string $message = ''): void
{
    Assert::assertCount(...\func_get_args());
}

/**
 * Asserts the number of elements of an array, Countable or Traversable
 * that is stored in an attribute.
 *
 * @param object|string $haystackClassOrObject
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws ReflectionException
 * @throws Exception
 *
 * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3338
 * @codeCoverageIgnore
 *
 * @see Assert::assertAttributeCount
 */
function assertAttributeCount(int $expectedCount, string $haystackAttributeName, $haystackClassOrObject, string $message = ''): void
{
    Assert::assertAttributeCount(...\func_get_args());
}

/**
 * Asserts the number of elements of an array, Countable or Traversable.
 *
 * @param Countable|iterable $haystack
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws Exception
 *
 * @see Assert::assertNotCount
 */
function assertNotCount(int $expectedCount, $haystack, string $message = ''): void
{
    Assert::assertNotCount(...\func_get_args());
}

/**
 * Asserts the number of elements of an array, Countable or Traversable
 * that is stored in an attribute.
 *
 * @param object|string $haystackClassOrObject
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws ReflectionException
 * @throws Exception
 *
 * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3338
 * @codeCoverageIgnore
 *
 * @see Assert::assertAttributeNotCount
 */
function assertAttributeNotCount(int $expectedCount, string $haystackAttributeName, $haystackClassOrObject, string $message = ''): void
{
    Assert::assertAttributeNotCount(...\func_get_args());
}

/**
 * Asserts that two variables are equal.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertEquals
 */
function assertEquals($expected, $actual, string $message = '', float $delta = 0.0, int $maxDepth = 10, bool $canonicalize = false, bool $ignoreCase = false): void
{
    Assert::assertEquals(...\func_get_args());
}

/**
 * Asserts that two variables are equal (canonicalizing).
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertEqualsCanonicalizing
 */
function assertEqualsCanonicalizing($expected, $actual, string $message = ''): void
{
    Assert::assertEqualsCanonicalizing(...\func_get_args());
}

/**
 * Asserts that two variables are equal (ignoring case).
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertEqualsIgnoringCase
 */
function assertEqualsIgnoringCase($expected, $actual, string $message = ''): void
{
    Assert::assertEqualsIgnoringCase(...\func_get_args());
}

/**
 * Asserts that two variables are equal (with delta).
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertEqualsWithDelta
 */
function assertEqualsWithDelta($expected, $actual, float $delta, string $message = ''): void
{
    Assert::assertEqualsWithDelta(...\func_get_args());
}

/**
 * Asserts that a variable is equal to an attribute of an object.
 *
 * @param object|string $actualClassOrObject
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws ReflectionException
 * @throws Exception
 *
 * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3338
 * @codeCoverageIgnore
 *
 * @see Assert::assertAttributeEquals
 */
function assertAttributeEquals($expected, string $actualAttributeName, $actualClassOrObject, string $message = '', float $delta = 0.0, int $maxDepth = 10, bool $canonicalize = false, bool $ignoreCase = false): void
{
    Assert::assertAttributeEquals(...\func_get_args());
}

/**
 * Asserts that two variables are not equal.
 *
 * @param float $delta
 * @param int   $maxDepth
 * @param bool  $canonicalize
 * @param bool  $ignoreCase
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertNotEquals
 */
function assertNotEquals($expected, $actual, string $message = '', $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false): void
{
    Assert::assertNotEquals(...\func_get_args());
}

/**
 * Asserts that two variables are not equal (canonicalizing).
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertNotEqualsCanonicalizing
 */
function assertNotEqualsCanonicalizing($expected, $actual, string $message = ''): void
{
    Assert::assertNotEqualsCanonicalizing(...\func_get_args());
}

/**
 * Asserts that two variables are not equal (ignoring case).
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertNotEqualsIgnoringCase
 */
function assertNotEqualsIgnoringCase($expected, $actual, string $message = ''): void
{
    Assert::assertNotEqualsIgnoringCase(...\func_get_args());
}

/**
 * Asserts that two variables are not equal (with delta).
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertNotEqualsWithDelta
 */
function assertNotEqualsWithDelta($expected, $actual, float $delta, string $message = ''): void
{
    Assert::assertNotEqualsWithDelta(...\func_get_args());
}

/**
 * Asserts that a variable is not equal to an attribute of an object.
 *
 * @param object|string $actualClassOrObject
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws ReflectionException
 * @throws Exception
 *
 * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3338
 * @codeCoverageIgnore
 *
 * @see Assert::assertAttributeNotEquals
 */
function assertAttributeNotEquals($expected, string $actualAttributeName, $actualClassOrObject, string $message = '', float $delta = 0.0, int $maxDepth = 10, bool $canonicalize = false, bool $ignoreCase = false): void
{
    Assert::assertAttributeNotEquals(...\func_get_args());
}

/**
 * Asserts that a variable is empty.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertEmpty
 */
function assertEmpty($actual, string $message = ''): void
{
    Assert::assertEmpty(...\func_get_args());
}

/**
 * Asserts that a static attribute of a class or an attribute of an object
 * is empty.
 *
 * @param object|string $haystackClassOrObject
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws ReflectionException
 * @throws Exception
 *
 * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3338
 * @codeCoverageIgnore
 *
 * @see Assert::assertAttributeEmpty
 */
function assertAttributeEmpty(string $haystackAttributeName, $haystackClassOrObject, string $message = ''): void
{
    Assert::assertAttributeEmpty(...\func_get_args());
}

/**
 * Asserts that a variable is not empty.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertNotEmpty
 */
function assertNotEmpty($actual, string $message = ''): void
{
    Assert::assertNotEmpty(...\func_get_args());
}

/**
 * Asserts that a static attribute of a class or an attribute of an object
 * is not empty.
 *
 * @param object|string $haystackClassOrObject
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws ReflectionException
 * @throws Exception
 *
 * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3338
 * @codeCoverageIgnore
 *
 * @see Assert::assertAttributeNotEmpty
 */
function assertAttributeNotEmpty(string $haystackAttributeName, $haystackClassOrObject, string $message = ''): void
{
    Assert::assertAttributeNotEmpty(...\func_get_args());
}

/**
 * Asserts that a value is greater than another value.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertGreaterThan
 */
function assertGreaterThan($expected, $actual, string $message = ''): void
{
    Assert::assertGreaterThan(...\func_get_args());
}

/**
 * Asserts that an attribute is greater than another value.
 *
 * @param object|string $actualClassOrObject
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws ReflectionException
 * @throws Exception
 *
 * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3338
 * @codeCoverageIgnore
 *
 * @see Assert::assertAttributeGreaterThan
 */
function assertAttributeGreaterThan($expected, string $actualAttributeName, $actualClassOrObject, string $message = ''): void
{
    Assert::assertAttributeGreaterThan(...\func_get_args());
}

/**
 * Asserts that a value is greater than or equal to another value.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertGreaterThanOrEqual
 */
function assertGreaterThanOrEqual($expected, $actual, string $message = ''): void
{
    Assert::assertGreaterThanOrEqual(...\func_get_args());
}

/**
 * Asserts that an attribute is greater than or equal to another value.
 *
 * @param object|string $actualClassOrObject
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws ReflectionException
 * @throws Exception
 *
 * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3338
 * @codeCoverageIgnore
 *
 * @see Assert::assertAttributeGreaterThanOrEqual
 */
function assertAttributeGreaterThanOrEqual($expected, string $actualAttributeName, $actualClassOrObject, string $message = ''): void
{
    Assert::assertAttributeGreaterThanOrEqual(...\func_get_args());
}

/**
 * Asserts that a value is smaller than another value.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertLessThan
 */
function assertLessThan($expected, $actual, string $message = ''): void
{
    Assert::assertLessThan(...\func_get_args());
}

/**
 * Asserts that an attribute is smaller than another value.
 *
 * @param object|string $actualClassOrObject
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws ReflectionException
 * @throws Exception
 *
 * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3338
 * @codeCoverageIgnore
 *
 * @see Assert::assertAttributeLessThan
 */
function assertAttributeLessThan($expected, string $actualAttributeName, $actualClassOrObject, string $message = ''): void
{
    Assert::assertAttributeLessThan(...\func_get_args());
}

/**
 * Asserts that a value is smaller than or equal to another value.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertLessThanOrEqual
 */
function assertLessThanOrEqual($expected, $actual, string $message = ''): void
{
    Assert::assertLessThanOrEqual(...\func_get_args());
}

/**
 * Asserts that an attribute is smaller than or equal to another value.
 *
 * @param object|string $actualClassOrObject
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws ReflectionException
 * @throws Exception
 *
 * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3338
 * @codeCoverageIgnore
 *
 * @see Assert::assertAttributeLessThanOrEqual
 */
function assertAttributeLessThanOrEqual($expected, string $actualAttributeName, $actualClassOrObject, string $message = ''): void
{
    Assert::assertAttributeLessThanOrEqual(...\func_get_args());
}

/**
 * Asserts that the contents of one file is equal to the contents of another
 * file.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertFileEquals
 */
function assertFileEquals(string $expected, string $actual, string $message = '', bool $canonicalize = false, bool $ignoreCase = false): void
{
    Assert::assertFileEquals(...\func_get_args());
}

/**
 * Asserts that the contents of one file is not equal to the contents of
 * another file.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertFileNotEquals
 */
function assertFileNotEquals(string $expected, string $actual, string $message = '', bool $canonicalize = false, bool $ignoreCase = false): void
{
    Assert::assertFileNotEquals(...\func_get_args());
}

/**
 * Asserts that the contents of a string is equal
 * to the contents of a file.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertStringEqualsFile
 */
function assertStringEqualsFile(string $expectedFile, string $actualString, string $message = '', bool $canonicalize = false, bool $ignoreCase = false): void
{
    Assert::assertStringEqualsFile(...\func_get_args());
}

/**
 * Asserts that the contents of a string is not equal
 * to the contents of a file.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertStringNotEqualsFile
 */
function assertStringNotEqualsFile(string $expectedFile, string $actualString, string $message = '', bool $canonicalize = false, bool $ignoreCase = false): void
{
    Assert::assertStringNotEqualsFile(...\func_get_args());
}

/**
 * Asserts that a file/dir is readable.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertIsReadable
 */
function assertIsReadable(string $filename, string $message = ''): void
{
    Assert::assertIsReadable(...\func_get_args());
}

/**
 * Asserts that a file/dir exists and is not readable.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertNotIsReadable
 */
function assertNotIsReadable(string $filename, string $message = ''): void
{
    Assert::assertNotIsReadable(...\func_get_args());
}

/**
 * Asserts that a file/dir exists and is writable.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertIsWritable
 */
function assertIsWritable(string $filename, string $message = ''): void
{
    Assert::assertIsWritable(...\func_get_args());
}

/**
 * Asserts that a file/dir exists and is not writable.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertNotIsWritable
 */
function assertNotIsWritable(string $filename, string $message = ''): void
{
    Assert::assertNotIsWritable(...\func_get_args());
}

/**
 * Asserts that a directory exists.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertDirectoryExists
 */
function assertDirectoryExists(string $directory, string $message = ''): void
{
    Assert::assertDirectoryExists(...\func_get_args());
}

/**
 * Asserts that a directory does not exist.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertDirectoryNotExists
 */
function assertDirectoryNotExists(string $directory, string $message = ''): void
{
    Assert::assertDirectoryNotExists(...\func_get_args());
}

/**
 * Asserts that a directory exists and is readable.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertDirectoryIsReadable
 */
function assertDirectoryIsReadable(string $directory, string $message = ''): void
{
    Assert::assertDirectoryIsReadable(...\func_get_args());
}

/**
 * Asserts that a directory exists and is not readable.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertDirectoryNotIsReadable
 */
function assertDirectoryNotIsReadable(string $directory, string $message = ''): void
{
    Assert::assertDirectoryNotIsReadable(...\func_get_args());
}

/**
 * Asserts that a directory exists and is writable.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertDirectoryIsWritable
 */
function assertDirectoryIsWritable(string $directory, string $message = ''): void
{
    Assert::assertDirectoryIsWritable(...\func_get_args());
}

/**
 * Asserts that a directory exists and is not writable.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertDirectoryNotIsWritable
 */
function assertDirectoryNotIsWritable(string $directory, string $message = ''): void
{
    Assert::assertDirectoryNotIsWritable(...\func_get_args());
}

/**
 * Asserts that a file exists.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertFileExists
 */
function assertFileExists(string $filename, string $message = ''): void
{
    Assert::assertFileExists(...\func_get_args());
}

/**
 * Asserts that a file does not exist.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertFileNotExists
 */
function assertFileNotExists(string $filename, string $message = ''): void
{
    Assert::assertFileNotExists(...\func_get_args());
}

/**
 * Asserts that a file exists and is readable.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertFileIsReadable
 */
function assertFileIsReadable(string $file, string $message = ''): void
{
    Assert::assertFileIsReadable(...\func_get_args());
}

/**
 * Asserts that a file exists and is not readable.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertFileNotIsReadable
 */
function assertFileNotIsReadable(string $file, string $message = ''): void
{
    Assert::assertFileNotIsReadable(...\func_get_args());
}

/**
 * Asserts that a file exists and is writable.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertFileIsWritable
 */
function assertFileIsWritable(string $file, string $message = ''): void
{
    Assert::assertFileIsWritable(...\func_get_args());
}

/**
 * Asserts that a file exists and is not writable.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertFileNotIsWritable
 */
function assertFileNotIsWritable(string $file, string $message = ''): void
{
    Assert::assertFileNotIsWritable(...\func_get_args());
}

/**
 * Asserts that a condition is true.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertTrue
 */
function assertTrue($condition, string $message = ''): void
{
    Assert::assertTrue(...\func_get_args());
}

/**
 * Asserts that a condition is not true.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertNotTrue
 */
function assertNotTrue($condition, string $message = ''): void
{
    Assert::assertNotTrue(...\func_get_args());
}

/**
 * Asserts that a condition is false.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertFalse
 */
function assertFalse($condition, string $message = ''): void
{
    Assert::assertFalse(...\func_get_args());
}

/**
 * Asserts that a condition is not false.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertNotFalse
 */
function assertNotFalse($condition, string $message = ''): void
{
    Assert::assertNotFalse(...\func_get_args());
}

/**
 * Asserts that a variable is null.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertNull
 */
function assertNull($actual, string $message = ''): void
{
    Assert::assertNull(...\func_get_args());
}

/**
 * Asserts that a variable is not null.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertNotNull
 */
function assertNotNull($actual, string $message = ''): void
{
    Assert::assertNotNull(...\func_get_args());
}

/**
 * Asserts that a variable is finite.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertFinite
 */
function assertFinite($actual, string $message = ''): void
{
    Assert::assertFinite(...\func_get_args());
}

/**
 * Asserts that a variable is infinite.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertInfinite
 */
function assertInfinite($actual, string $message = ''): void
{
    Assert::assertInfinite(...\func_get_args());
}

/**
 * Asserts that a variable is nan.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertNan
 */
function assertNan($actual, string $message = ''): void
{
    Assert::assertNan(...\func_get_args());
}

/**
 * Asserts that a class has a specified attribute.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws Exception
 *
 * @see Assert::assertClassHasAttribute
 */
function assertClassHasAttribute(string $attributeName, string $className, string $message = ''): void
{
    Assert::assertClassHasAttribute(...\func_get_args());
}

/**
 * Asserts that a class does not have a specified attribute.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws Exception
 *
 * @see Assert::assertClassNotHasAttribute
 */
function assertClassNotHasAttribute(string $attributeName, string $className, string $message = ''): void
{
    Assert::assertClassNotHasAttribute(...\func_get_args());
}

/**
 * Asserts that a class has a specified static attribute.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws Exception
 *
 * @see Assert::assertClassHasStaticAttribute
 */
function assertClassHasStaticAttribute(string $attributeName, string $className, string $message = ''): void
{
    Assert::assertClassHasStaticAttribute(...\func_get_args());
}

/**
 * Asserts that a class does not have a specified static attribute.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws Exception
 *
 * @see Assert::assertClassNotHasStaticAttribute
 */
function assertClassNotHasStaticAttribute(string $attributeName, string $className, string $message = ''): void
{
    Assert::assertClassNotHasStaticAttribute(...\func_get_args());
}

/**
 * Asserts that an object has a specified attribute.
 *
 * @param object $object
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws Exception
 *
 * @see Assert::assertObjectHasAttribute
 */
function assertObjectHasAttribute(string $attributeName, $object, string $message = ''): void
{
    Assert::assertObjectHasAttribute(...\func_get_args());
}

/**
 * Asserts that an object does not have a specified attribute.
 *
 * @param object $object
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws Exception
 *
 * @see Assert::assertObjectNotHasAttribute
 */
function assertObjectNotHasAttribute(string $attributeName, $object, string $message = ''): void
{
    Assert::assertObjectNotHasAttribute(...\func_get_args());
}

/**
 * Asserts that two variables have the same type and value.
 * Used on objects, it asserts that two variables reference
 * the same object.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertSame
 */
function assertSame($expected, $actual, string $message = ''): void
{
    Assert::assertSame(...\func_get_args());
}

/**
 * Asserts that a variable and an attribute of an object have the same type
 * and value.
 *
 * @param object|string $actualClassOrObject
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws ReflectionException
 * @throws Exception
 *
 * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3338
 * @codeCoverageIgnore
 *
 * @see Assert::assertAttributeSame
 */
function assertAttributeSame($expected, string $actualAttributeName, $actualClassOrObject, string $message = ''): void
{
    Assert::assertAttributeSame(...\func_get_args());
}

/**
 * Asserts that two variables do not have the same type and value.
 * Used on objects, it asserts that two variables do not reference
 * the same object.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertNotSame
 */
function assertNotSame($expected, $actual, string $message = ''): void
{
    Assert::assertNotSame(...\func_get_args());
}

/**
 * Asserts that a variable and an attribute of an object do not have the
 * same type and value.
 *
 * @param object|string $actualClassOrObject
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws ReflectionException
 * @throws Exception
 *
 * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3338
 * @codeCoverageIgnore
 *
 * @see Assert::assertAttributeNotSame
 */
function assertAttributeNotSame($expected, string $actualAttributeName, $actualClassOrObject, string $message = ''): void
{
    Assert::assertAttributeNotSame(...\func_get_args());
}

/**
 * Asserts that a variable is of a given type.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws Exception
 *
 * @see Assert::assertInstanceOf
 */
function assertInstanceOf(string $expected, $actual, string $message = ''): void
{
    Assert::assertInstanceOf(...\func_get_args());
}

/**
 * Asserts that an attribute is of a given type.
 *
 * @param object|string $classOrObject
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws ReflectionException
 * @throws Exception
 *
 * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3338
 * @codeCoverageIgnore
 *
 * @see Assert::assertAttributeInstanceOf
 */
function assertAttributeInstanceOf(string $expected, string $attributeName, $classOrObject, string $message = ''): void
{
    Assert::assertAttributeInstanceOf(...\func_get_args());
}

/**
 * Asserts that a variable is not of a given type.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws Exception
 *
 * @see Assert::assertNotInstanceOf
 */
function assertNotInstanceOf(string $expected, $actual, string $message = ''): void
{
    Assert::assertNotInstanceOf(...\func_get_args());
}

/**
 * Asserts that an attribute is of a given type.
 *
 * @param object|string $classOrObject
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws ReflectionException
 * @throws Exception
 *
 * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3338
 * @codeCoverageIgnore
 *
 * @see Assert::assertAttributeNotInstanceOf
 */
function assertAttributeNotInstanceOf(string $expected, string $attributeName, $classOrObject, string $message = ''): void
{
    Assert::assertAttributeNotInstanceOf(...\func_get_args());
}

/**
 * Asserts that a variable is of a given type.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3369
 * @codeCoverageIgnore
 *
 * @see Assert::assertInternalType
 */
function assertInternalType(string $expected, $actual, string $message = ''): void
{
    Assert::assertInternalType(...\func_get_args());
}

/**
 * Asserts that an attribute is of a given type.
 *
 * @param object|string $classOrObject
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws ReflectionException
 * @throws Exception
 *
 * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3338
 * @codeCoverageIgnore
 *
 * @see Assert::assertAttributeInternalType
 */
function assertAttributeInternalType(string $expected, string $attributeName, $classOrObject, string $message = ''): void
{
    Assert::assertAttributeInternalType(...\func_get_args());
}

/**
 * Asserts that a variable is of type array.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertIsArray
 */
function assertIsArray($actual, string $message = ''): void
{
    Assert::assertIsArray(...\func_get_args());
}

/**
 * Asserts that a variable is of type bool.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertIsBool
 */
function assertIsBool($actual, string $message = ''): void
{
    Assert::assertIsBool(...\func_get_args());
}

/**
 * Asserts that a variable is of type float.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertIsFloat
 */
function assertIsFloat($actual, string $message = ''): void
{
    Assert::assertIsFloat(...\func_get_args());
}

/**
 * Asserts that a variable is of type int.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertIsInt
 */
function assertIsInt($actual, string $message = ''): void
{
    Assert::assertIsInt(...\func_get_args());
}

/**
 * Asserts that a variable is of type numeric.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertIsNumeric
 */
function assertIsNumeric($actual, string $message = ''): void
{
    Assert::assertIsNumeric(...\func_get_args());
}

/**
 * Asserts that a variable is of type object.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertIsObject
 */
function assertIsObject($actual, string $message = ''): void
{
    Assert::assertIsObject(...\func_get_args());
}

/**
 * Asserts that a variable is of type resource.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertIsResource
 */
function assertIsResource($actual, string $message = ''): void
{
    Assert::assertIsResource(...\func_get_args());
}

/**
 * Asserts that a variable is of type string.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertIsString
 */
function assertIsString($actual, string $message = ''): void
{
    Assert::assertIsString(...\func_get_args());
}

/**
 * Asserts that a variable is of type scalar.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertIsScalar
 */
function assertIsScalar($actual, string $message = ''): void
{
    Assert::assertIsScalar(...\func_get_args());
}

/**
 * Asserts that a variable is of type callable.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertIsCallable
 */
function assertIsCallable($actual, string $message = ''): void
{
    Assert::assertIsCallable(...\func_get_args());
}

/**
 * Asserts that a variable is of type iterable.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertIsIterable
 */
function assertIsIterable($actual, string $message = ''): void
{
    Assert::assertIsIterable(...\func_get_args());
}

/**
 * Asserts that a variable is not of a given type.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3369
 * @codeCoverageIgnore
 *
 * @see Assert::assertNotInternalType
 */
function assertNotInternalType(string $expected, $actual, string $message = ''): void
{
    Assert::assertNotInternalType(...\func_get_args());
}

/**
 * Asserts that a variable is not of type array.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertIsNotArray
 */
function assertIsNotArray($actual, string $message = ''): void
{
    Assert::assertIsNotArray(...\func_get_args());
}

/**
 * Asserts that a variable is not of type bool.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertIsNotBool
 */
function assertIsNotBool($actual, string $message = ''): void
{
    Assert::assertIsNotBool(...\func_get_args());
}

/**
 * Asserts that a variable is not of type float.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertIsNotFloat
 */
function assertIsNotFloat($actual, string $message = ''): void
{
    Assert::assertIsNotFloat(...\func_get_args());
}

/**
 * Asserts that a variable is not of type int.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertIsNotInt
 */
function assertIsNotInt($actual, string $message = ''): void
{
    Assert::assertIsNotInt(...\func_get_args());
}

/**
 * Asserts that a variable is not of type numeric.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertIsNotNumeric
 */
function assertIsNotNumeric($actual, string $message = ''): void
{
    Assert::assertIsNotNumeric(...\func_get_args());
}

/**
 * Asserts that a variable is not of type object.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertIsNotObject
 */
function assertIsNotObject($actual, string $message = ''): void
{
    Assert::assertIsNotObject(...\func_get_args());
}

/**
 * Asserts that a variable is not of type resource.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertIsNotResource
 */
function assertIsNotResource($actual, string $message = ''): void
{
    Assert::assertIsNotResource(...\func_get_args());
}

/**
 * Asserts that a variable is not of type string.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertIsNotString
 */
function assertIsNotString($actual, string $message = ''): void
{
    Assert::assertIsNotString(...\func_get_args());
}

/**
 * Asserts that a variable is not of type scalar.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertIsNotScalar
 */
function assertIsNotScalar($actual, string $message = ''): void
{
    Assert::assertIsNotScalar(...\func_get_args());
}

/**
 * Asserts that a variable is not of type callable.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertIsNotCallable
 */
function assertIsNotCallable($actual, string $message = ''): void
{
    Assert::assertIsNotCallable(...\func_get_args());
}

/**
 * Asserts that a variable is not of type iterable.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertIsNotIterable
 */
function assertIsNotIterable($actual, string $message = ''): void
{
    Assert::assertIsNotIterable(...\func_get_args());
}

/**
 * Asserts that an attribute is of a given type.
 *
 * @param object|string $classOrObject
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws ReflectionException
 * @throws Exception
 *
 * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3338
 * @codeCoverageIgnore
 *
 * @see Assert::assertAttributeNotInternalType
 */
function assertAttributeNotInternalType(string $expected, string $attributeName, $classOrObject, string $message = ''): void
{
    Assert::assertAttributeNotInternalType(...\func_get_args());
}

/**
 * Asserts that a string matches a given regular expression.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertRegExp
 */
function assertRegExp(string $pattern, string $string, string $message = ''): void
{
    Assert::assertRegExp(...\func_get_args());
}

/**
 * Asserts that a string does not match a given regular expression.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertNotRegExp
 */
function assertNotRegExp(string $pattern, string $string, string $message = ''): void
{
    Assert::assertNotRegExp(...\func_get_args());
}

/**
 * Assert that the size of two arrays (or `Countable` or `Traversable` objects)
 * is the same.
 *
 * @param Countable|iterable $expected
 * @param Countable|iterable $actual
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws Exception
 *
 * @see Assert::assertSameSize
 */
function assertSameSize($expected, $actual, string $message = ''): void
{
    Assert::assertSameSize(...\func_get_args());
}

/**
 * Assert that the size of two arrays (or `Countable` or `Traversable` objects)
 * is not the same.
 *
 * @param Countable|iterable $expected
 * @param Countable|iterable $actual
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws Exception
 *
 * @see Assert::assertNotSameSize
 */
function assertNotSameSize($expected, $actual, string $message = ''): void
{
    Assert::assertNotSameSize(...\func_get_args());
}

/**
 * Asserts that a string matches a given format string.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertStringMatchesFormat
 */
function assertStringMatchesFormat(string $format, string $string, string $message = ''): void
{
    Assert::assertStringMatchesFormat(...\func_get_args());
}

/**
 * Asserts that a string does not match a given format string.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertStringNotMatchesFormat
 */
function assertStringNotMatchesFormat(string $format, string $string, string $message = ''): void
{
    Assert::assertStringNotMatchesFormat(...\func_get_args());
}

/**
 * Asserts that a string matches a given format file.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertStringMatchesFormatFile
 */
function assertStringMatchesFormatFile(string $formatFile, string $string, string $message = ''): void
{
    Assert::assertStringMatchesFormatFile(...\func_get_args());
}

/**
 * Asserts that a string does not match a given format string.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertStringNotMatchesFormatFile
 */
function assertStringNotMatchesFormatFile(string $formatFile, string $string, string $message = ''): void
{
    Assert::assertStringNotMatchesFormatFile(...\func_get_args());
}

/**
 * Asserts that a string starts with a given prefix.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertStringStartsWith
 */
function assertStringStartsWith(string $prefix, string $string, string $message = ''): void
{
    Assert::assertStringStartsWith(...\func_get_args());
}

/**
 * Asserts that a string starts not with a given prefix.
 *
 * @param string $prefix
 * @param string $string
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertStringStartsNotWith
 */
function assertStringStartsNotWith($prefix, $string, string $message = ''): void
{
    Assert::assertStringStartsNotWith(...\func_get_args());
}

/**
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertStringContainsString
 */
function assertStringContainsString(string $needle, string $haystack, string $message = ''): void
{
    Assert::assertStringContainsString(...\func_get_args());
}

/**
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertStringContainsStringIgnoringCase
 */
function assertStringContainsStringIgnoringCase(string $needle, string $haystack, string $message = ''): void
{
    Assert::assertStringContainsStringIgnoringCase(...\func_get_args());
}

/**
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertStringNotContainsString
 */
function assertStringNotContainsString(string $needle, string $haystack, string $message = ''): void
{
    Assert::assertStringNotContainsString(...\func_get_args());
}

/**
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertStringNotContainsStringIgnoringCase
 */
function assertStringNotContainsStringIgnoringCase(string $needle, string $haystack, string $message = ''): void
{
    Assert::assertStringNotContainsStringIgnoringCase(...\func_get_args());
}

/**
 * Asserts that a string ends with a given suffix.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertStringEndsWith
 */
function assertStringEndsWith(string $suffix, string $string, string $message = ''): void
{
    Assert::assertStringEndsWith(...\func_get_args());
}

/**
 * Asserts that a string ends not with a given suffix.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertStringEndsNotWith
 */
function assertStringEndsNotWith(string $suffix, string $string, string $message = ''): void
{
    Assert::assertStringEndsNotWith(...\func_get_args());
}

/**
 * Asserts that two XML files are equal.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws Exception
 *
 * @see Assert::assertXmlFileEqualsXmlFile
 */
function assertXmlFileEqualsXmlFile(string $expectedFile, string $actualFile, string $message = ''): void
{
    Assert::assertXmlFileEqualsXmlFile(...\func_get_args());
}

/**
 * Asserts that two XML files are not equal.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws Exception
 *
 * @see Assert::assertXmlFileNotEqualsXmlFile
 */
function assertXmlFileNotEqualsXmlFile(string $expectedFile, string $actualFile, string $message = ''): void
{
    Assert::assertXmlFileNotEqualsXmlFile(...\func_get_args());
}

/**
 * Asserts that two XML documents are equal.
 *
 * @param DOMDocument|string $actualXml
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws Exception
 *
 * @see Assert::assertXmlStringEqualsXmlFile
 */
function assertXmlStringEqualsXmlFile(string $expectedFile, $actualXml, string $message = ''): void
{
    Assert::assertXmlStringEqualsXmlFile(...\func_get_args());
}

/**
 * Asserts that two XML documents are not equal.
 *
 * @param DOMDocument|string $actualXml
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws Exception
 *
 * @see Assert::assertXmlStringNotEqualsXmlFile
 */
function assertXmlStringNotEqualsXmlFile(string $expectedFile, $actualXml, string $message = ''): void
{
    Assert::assertXmlStringNotEqualsXmlFile(...\func_get_args());
}

/**
 * Asserts that two XML documents are equal.
 *
 * @param DOMDocument|string $expectedXml
 * @param DOMDocument|string $actualXml
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws Exception
 *
 * @see Assert::assertXmlStringEqualsXmlString
 */
function assertXmlStringEqualsXmlString($expectedXml, $actualXml, string $message = ''): void
{
    Assert::assertXmlStringEqualsXmlString(...\func_get_args());
}

/**
 * Asserts that two XML documents are not equal.
 *
 * @param DOMDocument|string $expectedXml
 * @param DOMDocument|string $actualXml
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 * @throws Exception
 *
 * @see Assert::assertXmlStringNotEqualsXmlString
 */
function assertXmlStringNotEqualsXmlString($expectedXml, $actualXml, string $message = ''): void
{
    Assert::assertXmlStringNotEqualsXmlString(...\func_get_args());
}

/**
 * Asserts that a hierarchy of DOMElements matches.
 *
 * @throws AssertionFailedError
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertEqualXMLStructure
 */
function assertEqualXMLStructure(DOMElement $expectedElement, DOMElement $actualElement, bool $checkAttributes = false, string $message = ''): void
{
    Assert::assertEqualXMLStructure(...\func_get_args());
}

/**
 * Evaluates a PHPUnit\Framework\Constraint matcher object.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertThat
 */
function assertThat($value, Constraint $constraint, string $message = ''): void
{
    Assert::assertThat(...\func_get_args());
}

/**
 * Asserts that a string is a valid JSON string.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertJson
 */
function assertJson(string $actualJson, string $message = ''): void
{
    Assert::assertJson(...\func_get_args());
}

/**
 * Asserts that two given JSON encoded objects or arrays are equal.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertJsonStringEqualsJsonString
 */
function assertJsonStringEqualsJsonString(string $expectedJson, string $actualJson, string $message = ''): void
{
    Assert::assertJsonStringEqualsJsonString(...\func_get_args());
}

/**
 * Asserts that two given JSON encoded objects or arrays are not equal.
 *
 * @param string $expectedJson
 * @param string $actualJson
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertJsonStringNotEqualsJsonString
 */
function assertJsonStringNotEqualsJsonString($expectedJson, $actualJson, string $message = ''): void
{
    Assert::assertJsonStringNotEqualsJsonString(...\func_get_args());
}

/**
 * Asserts that the generated JSON encoded object and the content of the given file are equal.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertJsonStringEqualsJsonFile
 */
function assertJsonStringEqualsJsonFile(string $expectedFile, string $actualJson, string $message = ''): void
{
    Assert::assertJsonStringEqualsJsonFile(...\func_get_args());
}

/**
 * Asserts that the generated JSON encoded object and the content of the given file are not equal.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertJsonStringNotEqualsJsonFile
 */
function assertJsonStringNotEqualsJsonFile(string $expectedFile, string $actualJson, string $message = ''): void
{
    Assert::assertJsonStringNotEqualsJsonFile(...\func_get_args());
}

/**
 * Asserts that two JSON files are equal.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertJsonFileEqualsJsonFile
 */
function assertJsonFileEqualsJsonFile(string $expectedFile, string $actualFile, string $message = ''): void
{
    Assert::assertJsonFileEqualsJsonFile(...\func_get_args());
}

/**
 * Asserts that two JSON files are not equal.
 *
 * @throws ExpectationFailedException
 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
 *
 * @see Assert::assertJsonFileNotEqualsJsonFile
 */
function assertJsonFileNotEqualsJsonFile(string $expectedFile, string $actualFile, string $message = ''): void
{
    Assert::assertJsonFileNotEqualsJsonFile(...\func_get_args());
}

function logicalAnd(): LogicalAnd
{
    return Assert::logicalAnd(...\func_get_args());
}

function logicalOr(): LogicalOr
{
    return Assert::logicalOr(...\func_get_args());
}

function logicalNot(Constraint $constraint): LogicalNot
{
    return Assert::logicalNot(...\func_get_args());
}

function logicalXor(): LogicalXor
{
    return Assert::logicalXor(...\func_get_args());
}

function anything(): IsAnything
{
    return Assert::anything(...\func_get_args());
}

function isTrue(): IsTrue
{
    return Assert::isTrue(...\func_get_args());
}

function callback(callable $callback): Callback
{
    return Assert::callback(...\func_get_args());
}

function isFalse(): IsFalse
{
    return Assert::isFalse(...\func_get_args());
}

function isJson(): IsJson
{
    return Assert::isJson(...\func_get_args());
}

function isNull(): IsNull
{
    return Assert::isNull(...\func_get_args());
}

function isFinite(): IsFinite
{
    return Assert::isFinite(...\func_get_args());
}

function isInfinite(): IsInfinite
{
    return Assert::isInfinite(...\func_get_args());
}

function isNan(): IsNan
{
    return Assert::isNan(...\func_get_args());
}

function attribute(Constraint $constraint, string $attributeName): Attribute
{
    return Assert::attribute(...\func_get_args());
}

function contains($value, bool $checkForObjectIdentity = true, bool $checkForNonObjectIdentity = false): TraversableContains
{
    return Assert::contains(...\func_get_args());
}

function containsOnly(string $type): TraversableContainsOnly
{
    return Assert::containsOnly(...\func_get_args());
}

function containsOnlyInstancesOf(string $className): TraversableContainsOnly
{
    return Assert::containsOnlyInstancesOf(...\func_get_args());
}

function arrayHasKey($key): ArrayHasKey
{
    return Assert::arrayHasKey(...\func_get_args());
}

function equalTo($value, float $delta = 0.0, int $maxDepth = 10, bool $canonicalize = false, bool $ignoreCase = false): IsEqual
{
    return Assert::equalTo(...\func_get_args());
}

function attributeEqualTo(string $attributeName, $value, float $delta = 0.0, int $maxDepth = 10, bool $canonicalize = false, bool $ignoreCase = false): Attribute
{
    return Assert::attributeEqualTo(...\func_get_args());
}

function isEmpty(): IsEmpty
{
    return Assert::isEmpty(...\func_get_args());
}

function isWritable(): IsWritable
{
    return Assert::isWritable(...\func_get_args());
}

function isReadable(): IsReadable
{
    return Assert::isReadable(...\func_get_args());
}

function directoryExists(): DirectoryExists
{
    return Assert::directoryExists(...\func_get_args());
}

function fileExists(): FileExists
{
    return Assert::fileExists(...\func_get_args());
}

function greaterThan($value): GreaterThan
{
    return Assert::greaterThan(...\func_get_args());
}

function greaterThanOrEqual($value): LogicalOr
{
    return Assert::greaterThanOrEqual(...\func_get_args());
}

function classHasAttribute(string $attributeName): ClassHasAttribute
{
    return Assert::classHasAttribute(...\func_get_args());
}

function classHasStaticAttribute(string $attributeName): ClassHasStaticAttribute
{
    return Assert::classHasStaticAttribute(...\func_get_args());
}

function objectHasAttribute($attributeName): ObjectHasAttribute
{
    return Assert::objectHasAttribute(...\func_get_args());
}

function identicalTo($value): IsIdentical
{
    return Assert::identicalTo(...\func_get_args());
}

function isInstanceOf(string $className): IsInstanceOf
{
    return Assert::isInstanceOf(...\func_get_args());
}

function isType(string $type): IsType
{
    return Assert::isType(...\func_get_args());
}

function lessThan($value): LessThan
{
    return Assert::lessThan(...\func_get_args());
}

function lessThanOrEqual($value): LogicalOr
{
    return Assert::lessThanOrEqual(...\func_get_args());
}

function matchesRegularExpression(string $pattern): RegularExpression
{
    return Assert::matchesRegularExpression(...\func_get_args());
}

function matches(string $string): StringMatchesFormatDescription
{
    return Assert::matches(...\func_get_args());
}

function stringStartsWith($prefix): StringStartsWith
{
    return Assert::stringStartsWith(...\func_get_args());
}

function stringContains(string $string, bool $case = true): StringContains
{
    return Assert::stringContains(...\func_get_args());
}

function stringEndsWith(string $suffix): StringEndsWith
{
    return Assert::stringEndsWith(...\func_get_args());
}

function countOf(int $count): Count
{
    return Assert::countOf(...\func_get_args());
}

/**
 * Returns a matcher that matches when the method is executed
 * zero or more times.
 */
function any(): AnyInvokedCountMatcher
{
    return new AnyInvokedCountMatcher;
}

/**
 * Returns a matcher that matches when the method is never executed.
 */
function never(): InvokedCountMatcher
{
    return new InvokedCountMatcher(0);
}

/**
 * Returns a matcher that matches when the method is executed
 * at least N times.
 */
function atLeast(int $requiredInvocations): InvokedAtLeastCountMatcher
{
    return new InvokedAtLeastCountMatcher(
        $requiredInvocations
    );
}

/**
 * Returns a matcher that matches when the method is executed at least once.
 */
function atLeastOnce(): InvokedAtLeastOnceMatcher
{
    return new InvokedAtLeastOnceMatcher;
}

/**
 * Returns a matcher that matches when the method is executed exactly once.
 */
function once(): InvokedCountMatcher
{
    return new InvokedCountMatcher(1);
}

/**
 * Returns a matcher that matches when the method is executed
 * exactly $count times.
 */
function exactly(int $count): InvokedCountMatcher
{
    return new InvokedCountMatcher($count);
}

/**
 * Returns a matcher that matches when the method is executed
 * at most N times.
 */
function atMost(int $allowedInvocations): InvokedAtMostCountMatcher
{
    return new InvokedAtMostCountMatcher($allowedInvocations);
}

/**
 * Returns a matcher that matches when the method is executed
 * at the given index.
 */
function at(int $index): InvokedAtIndexMatcher
{
    return new InvokedAtIndexMatcher($index);
}

function returnValue($value): ReturnStub
{
    return new ReturnStub($value);
}

function returnValueMap(array $valueMap): ReturnValueMapStub
{
    return new ReturnValueMapStub($valueMap);
}

function returnArgument(int $argumentIndex): ReturnArgumentStub
{
    return new ReturnArgumentStub($argumentIndex);
}

function returnCallback($callback): ReturnCallbackStub
{
    return new ReturnCallbackStub($callback);
}

/**
 * Returns the current object.
 *
 * This method is useful when mocking a fluent interface.
 */
function returnSelf(): ReturnSelfStub
{
    return new ReturnSelfStub;
}

function throwException(Throwable $exception): ExceptionStub
{
    return new ExceptionStub($exception);
}

function onConsecutiveCalls(): ConsecutiveCallsStub
{
    $args = \func_get_args();

    return new ConsecutiveCallsStub($args);
}
