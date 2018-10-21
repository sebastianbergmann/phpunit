<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Framework;

use ArrayAccess;
use Countable;
use DOMDocument;
use DOMElement;
use PHPUnit\Framework\Constraint\ArrayHasKey;
use PHPUnit\Framework\Constraint\ArraySubset;
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
use PHPUnit\Framework\Constraint\JsonMatches;
use PHPUnit\Framework\Constraint\LessThan;
use PHPUnit\Framework\Constraint\LogicalAnd;
use PHPUnit\Framework\Constraint\LogicalNot;
use PHPUnit\Framework\Constraint\LogicalOr;
use PHPUnit\Framework\Constraint\LogicalXor;
use PHPUnit\Framework\Constraint\ObjectHasAttribute;
use PHPUnit\Framework\Constraint\RegularExpression;
use PHPUnit\Framework\Constraint\SameSize;
use PHPUnit\Framework\Constraint\StringContains;
use PHPUnit\Framework\Constraint\StringEndsWith;
use PHPUnit\Framework\Constraint\StringMatchesFormatDescription;
use PHPUnit\Framework\Constraint\StringStartsWith;
use PHPUnit\Framework\Constraint\TraversableContains;
use PHPUnit\Framework\Constraint\TraversableContainsOnly;
use PHPUnit\Util\InvalidArgumentHelper;
use PHPUnit\Util\Type;
use PHPUnit\Util\Xml;
use ReflectionClass;
use ReflectionException;
use ReflectionObject;
use ReflectionProperty;
use Traversable;

/**
 * A set of assertion methods.
 */
abstract class Assert
{
    /**
     * @var int
     */
    private static $count = 0;

    /**
     * Asserts that an array has a specified key.
     *
     * @param mixed             $key
     * @param array|ArrayAccess $array
     * @param string            $message
     */
    public static function assertArrayHasKey($key, $array, $message = '')
    {
        if (!(\is_int($key) || \is_string($key))) {
            throw InvalidArgumentHelper::factory(
                1,
                'integer or string'
            );
        }

        if (!(\is_array($array) || $array instanceof ArrayAccess)) {
            throw InvalidArgumentHelper::factory(
                2,
                'array or ArrayAccess'
            );
        }

        $constraint = new ArrayHasKey($key);

        static::assertThat($array, $constraint, $message);
    }

    /**
     * Asserts that an array has a specified subset.
     *
     * @param array|ArrayAccess $subset
     * @param array|ArrayAccess $array
     * @param bool              $strict  Check for object identity
     * @param string            $message
     */
    public static function assertArraySubset($subset, $array, $strict = false, $message = '')
    {
        if (!(\is_array($subset) || $subset instanceof ArrayAccess)) {
            throw InvalidArgumentHelper::factory(
                1,
                'array or ArrayAccess'
            );
        }

        if (!(\is_array($array) || $array instanceof ArrayAccess)) {
            throw InvalidArgumentHelper::factory(
                2,
                'array or ArrayAccess'
            );
        }

        $constraint = new ArraySubset($subset, $strict);

        static::assertThat($array, $constraint, $message);
    }

    /**
     * Asserts that an array does not have a specified key.
     *
     * @param mixed             $key
     * @param array|ArrayAccess $array
     * @param string            $message
     */
    public static function assertArrayNotHasKey($key, $array, $message = '')
    {
        if (!(\is_int($key) || \is_string($key))) {
            throw InvalidArgumentHelper::factory(
                1,
                'integer or string'
            );
        }

        if (!(\is_array($array) || $array instanceof ArrayAccess)) {
            throw InvalidArgumentHelper::factory(
                2,
                'array or ArrayAccess'
            );
        }

        $constraint = new LogicalNot(
            new ArrayHasKey($key)
        );

        static::assertThat($array, $constraint, $message);
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
    public static function assertContains($needle, $haystack, $message = '', $ignoreCase = false, $checkForObjectIdentity = true, $checkForNonObjectIdentity = false)
    {
        if (\is_array($haystack) ||
            (\is_object($haystack) && $haystack instanceof Traversable)) {
            $constraint = new TraversableContains(
                $needle,
                $checkForObjectIdentity,
                $checkForNonObjectIdentity
            );
        } elseif (\is_string($haystack)) {
            if (!\is_string($needle)) {
                throw InvalidArgumentHelper::factory(
                    1,
                    'string'
                );
            }

            $constraint = new StringContains(
                $needle,
                $ignoreCase
            );
        } else {
            throw InvalidArgumentHelper::factory(
                2,
                'array, traversable or string'
            );
        }

        static::assertThat($haystack, $constraint, $message);
    }

    /**
     * Asserts that a haystack that is stored in a static attribute of a class
     * or an attribute of an object contains a needle.
     *
     * @param mixed         $needle
     * @param string        $haystackAttributeName
     * @param string|object $haystackClassOrObject
     * @param string        $message
     * @param bool          $ignoreCase
     * @param bool          $checkForObjectIdentity
     * @param bool          $checkForNonObjectIdentity
     */
    public static function assertAttributeContains($needle, $haystackAttributeName, $haystackClassOrObject, $message = '', $ignoreCase = false, $checkForObjectIdentity = true, $checkForNonObjectIdentity = false)
    {
        static::assertContains(
            $needle,
            static::readAttribute($haystackClassOrObject, $haystackAttributeName),
            $message,
            $ignoreCase,
            $checkForObjectIdentity,
            $checkForNonObjectIdentity
        );
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
    public static function assertNotContains($needle, $haystack, $message = '', $ignoreCase = false, $checkForObjectIdentity = true, $checkForNonObjectIdentity = false)
    {
        if (\is_array($haystack) ||
            (\is_object($haystack) && $haystack instanceof Traversable)) {
            $constraint = new LogicalNot(
                new TraversableContains(
                    $needle,
                    $checkForObjectIdentity,
                    $checkForNonObjectIdentity
                )
            );
        } elseif (\is_string($haystack)) {
            if (!\is_string($needle)) {
                throw InvalidArgumentHelper::factory(
                    1,
                    'string'
                );
            }

            $constraint = new LogicalNot(
                new StringContains(
                    $needle,
                    $ignoreCase
                )
            );
        } else {
            throw InvalidArgumentHelper::factory(
                2,
                'array, traversable or string'
            );
        }

        static::assertThat($haystack, $constraint, $message);
    }

    /**
     * Asserts that a haystack that is stored in a static attribute of a class
     * or an attribute of an object does not contain a needle.
     *
     * @param mixed         $needle
     * @param string        $haystackAttributeName
     * @param string|object $haystackClassOrObject
     * @param string        $message
     * @param bool          $ignoreCase
     * @param bool          $checkForObjectIdentity
     * @param bool          $checkForNonObjectIdentity
     */
    public static function assertAttributeNotContains($needle, $haystackAttributeName, $haystackClassOrObject, $message = '', $ignoreCase = false, $checkForObjectIdentity = true, $checkForNonObjectIdentity = false)
    {
        static::assertNotContains(
            $needle,
            static::readAttribute($haystackClassOrObject, $haystackAttributeName),
            $message,
            $ignoreCase,
            $checkForObjectIdentity,
            $checkForNonObjectIdentity
        );
    }

    /**
     * Asserts that a haystack contains only values of a given type.
     *
     * @param string $type
     * @param mixed  $haystack
     * @param bool   $isNativeType
     * @param string $message
     */
    public static function assertContainsOnly($type, $haystack, $isNativeType = null, $message = '')
    {
        if (!\is_array($haystack) &&
            !(\is_object($haystack) && $haystack instanceof Traversable)) {
            throw InvalidArgumentHelper::factory(
                2,
                'array or traversable'
            );
        }

        if ($isNativeType == null) {
            $isNativeType = Type::isType($type);
        }

        static::assertThat(
            $haystack,
            new TraversableContainsOnly(
                $type,
                $isNativeType
            ),
            $message
        );
    }

    /**
     * Asserts that a haystack contains only instances of a given classname
     *
     * @param string             $classname
     * @param array|\Traversable $haystack
     * @param string             $message
     */
    public static function assertContainsOnlyInstancesOf($classname, $haystack, $message = '')
    {
        if (!\is_array($haystack) &&
            !(\is_object($haystack) && $haystack instanceof Traversable)) {
            throw InvalidArgumentHelper::factory(
                2,
                'array or traversable'
            );
        }

        static::assertThat(
            $haystack,
            new TraversableContainsOnly(
                $classname,
                false
            ),
            $message
        );
    }

    /**
     * Asserts that a haystack that is stored in a static attribute of a class
     * or an attribute of an object contains only values of a given type.
     *
     * @param string        $type
     * @param string        $haystackAttributeName
     * @param string|object $haystackClassOrObject
     * @param bool          $isNativeType
     * @param string        $message
     */
    public static function assertAttributeContainsOnly($type, $haystackAttributeName, $haystackClassOrObject, $isNativeType = null, $message = '')
    {
        static::assertContainsOnly(
            $type,
            static::readAttribute($haystackClassOrObject, $haystackAttributeName),
            $isNativeType,
            $message
        );
    }

    /**
     * Asserts that a haystack does not contain only values of a given type.
     *
     * @param string $type
     * @param mixed  $haystack
     * @param bool   $isNativeType
     * @param string $message
     */
    public static function assertNotContainsOnly($type, $haystack, $isNativeType = null, $message = '')
    {
        if (!\is_array($haystack) &&
            !(\is_object($haystack) && $haystack instanceof Traversable)) {
            throw InvalidArgumentHelper::factory(
                2,
                'array or traversable'
            );
        }

        if ($isNativeType == null) {
            $isNativeType = Type::isType($type);
        }

        static::assertThat(
            $haystack,
            new LogicalNot(
                new TraversableContainsOnly(
                    $type,
                    $isNativeType
                )
            ),
            $message
        );
    }

    /**
     * Asserts that a haystack that is stored in a static attribute of a class
     * or an attribute of an object does not contain only values of a given
     * type.
     *
     * @param string        $type
     * @param string        $haystackAttributeName
     * @param string|object $haystackClassOrObject
     * @param bool          $isNativeType
     * @param string        $message
     */
    public static function assertAttributeNotContainsOnly($type, $haystackAttributeName, $haystackClassOrObject, $isNativeType = null, $message = '')
    {
        static::assertNotContainsOnly(
            $type,
            static::readAttribute($haystackClassOrObject, $haystackAttributeName),
            $isNativeType,
            $message
        );
    }

    /**
     * Asserts the number of elements of an array, Countable or Traversable.
     *
     * @param int    $expectedCount
     * @param mixed  $haystack
     * @param string $message
     */
    public static function assertCount($expectedCount, $haystack, $message = '')
    {
        if (!\is_int($expectedCount)) {
            throw InvalidArgumentHelper::factory(1, 'integer');
        }

        if (!$haystack instanceof Countable &&
            !$haystack instanceof Traversable &&
            !\is_array($haystack)) {
            throw InvalidArgumentHelper::factory(2, 'countable or traversable');
        }

        static::assertThat(
            $haystack,
            new Count($expectedCount),
            $message
        );
    }

    /**
     * Asserts the number of elements of an array, Countable or Traversable
     * that is stored in an attribute.
     *
     * @param int           $expectedCount
     * @param string        $haystackAttributeName
     * @param string|object $haystackClassOrObject
     * @param string        $message
     */
    public static function assertAttributeCount($expectedCount, $haystackAttributeName, $haystackClassOrObject, $message = '')
    {
        static::assertCount(
            $expectedCount,
            static::readAttribute($haystackClassOrObject, $haystackAttributeName),
            $message
        );
    }

    /**
     * Asserts the number of elements of an array, Countable or Traversable.
     *
     * @param int    $expectedCount
     * @param mixed  $haystack
     * @param string $message
     */
    public static function assertNotCount($expectedCount, $haystack, $message = '')
    {
        if (!\is_int($expectedCount)) {
            throw InvalidArgumentHelper::factory(1, 'integer');
        }

        if (!$haystack instanceof Countable &&
            !$haystack instanceof Traversable &&
            !\is_array($haystack)) {
            throw InvalidArgumentHelper::factory(2, 'countable or traversable');
        }

        $constraint = new LogicalNot(
            new Count($expectedCount)
        );

        static::assertThat($haystack, $constraint, $message);
    }

    /**
     * Asserts the number of elements of an array, Countable or Traversable
     * that is stored in an attribute.
     *
     * @param int           $expectedCount
     * @param string        $haystackAttributeName
     * @param string|object $haystackClassOrObject
     * @param string        $message
     */
    public static function assertAttributeNotCount($expectedCount, $haystackAttributeName, $haystackClassOrObject, $message = '')
    {
        static::assertNotCount(
            $expectedCount,
            static::readAttribute($haystackClassOrObject, $haystackAttributeName),
            $message
        );
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
    public static function assertEquals($expected, $actual, $message = '', $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
    {
        $constraint = new IsEqual(
            $expected,
            $delta,
            $maxDepth,
            $canonicalize,
            $ignoreCase
        );

        static::assertThat($actual, $constraint, $message);
    }

    /**
     * Asserts that a variable is equal to an attribute of an object.
     *
     * @param mixed         $expected
     * @param string        $actualAttributeName
     * @param string|object $actualClassOrObject
     * @param string        $message
     * @param float         $delta
     * @param int           $maxDepth
     * @param bool          $canonicalize
     * @param bool          $ignoreCase
     */
    public static function assertAttributeEquals($expected, $actualAttributeName, $actualClassOrObject, $message = '', $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
    {
        static::assertEquals(
            $expected,
            static::readAttribute($actualClassOrObject, $actualAttributeName),
            $message,
            $delta,
            $maxDepth,
            $canonicalize,
            $ignoreCase
        );
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
    public static function assertNotEquals($expected, $actual, $message = '', $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
    {
        $constraint = new LogicalNot(
            new IsEqual(
                $expected,
                $delta,
                $maxDepth,
                $canonicalize,
                $ignoreCase
            )
        );

        static::assertThat($actual, $constraint, $message);
    }

    /**
     * Asserts that a variable is not equal to an attribute of an object.
     *
     * @param mixed         $expected
     * @param string        $actualAttributeName
     * @param string|object $actualClassOrObject
     * @param string        $message
     * @param float         $delta
     * @param int           $maxDepth
     * @param bool          $canonicalize
     * @param bool          $ignoreCase
     */
    public static function assertAttributeNotEquals($expected, $actualAttributeName, $actualClassOrObject, $message = '', $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
    {
        static::assertNotEquals(
            $expected,
            static::readAttribute($actualClassOrObject, $actualAttributeName),
            $message,
            $delta,
            $maxDepth,
            $canonicalize,
            $ignoreCase
        );
    }

    /**
     * Asserts that a variable is empty.
     *
     * @param mixed  $actual
     * @param string $message
     *
     * @throws AssertionFailedError
     */
    public static function assertEmpty($actual, $message = '')
    {
        static::assertThat($actual, static::isEmpty(), $message);
    }

    /**
     * Asserts that a static attribute of a class or an attribute of an object
     * is empty.
     *
     * @param string        $haystackAttributeName
     * @param string|object $haystackClassOrObject
     * @param string        $message
     */
    public static function assertAttributeEmpty($haystackAttributeName, $haystackClassOrObject, $message = '')
    {
        static::assertEmpty(
            static::readAttribute($haystackClassOrObject, $haystackAttributeName),
            $message
        );
    }

    /**
     * Asserts that a variable is not empty.
     *
     * @param mixed  $actual
     * @param string $message
     *
     * @throws AssertionFailedError
     */
    public static function assertNotEmpty($actual, $message = '')
    {
        static::assertThat($actual, static::logicalNot(static::isEmpty()), $message);
    }

    /**
     * Asserts that a static attribute of a class or an attribute of an object
     * is not empty.
     *
     * @param string        $haystackAttributeName
     * @param string|object $haystackClassOrObject
     * @param string        $message
     */
    public static function assertAttributeNotEmpty($haystackAttributeName, $haystackClassOrObject, $message = '')
    {
        static::assertNotEmpty(
            static::readAttribute($haystackClassOrObject, $haystackAttributeName),
            $message
        );
    }

    /**
     * Asserts that a value is greater than another value.
     *
     * @param mixed  $expected
     * @param mixed  $actual
     * @param string $message
     */
    public static function assertGreaterThan($expected, $actual, $message = '')
    {
        static::assertThat($actual, static::greaterThan($expected), $message);
    }

    /**
     * Asserts that an attribute is greater than another value.
     *
     * @param mixed         $expected
     * @param string        $actualAttributeName
     * @param string|object $actualClassOrObject
     * @param string        $message
     */
    public static function assertAttributeGreaterThan($expected, $actualAttributeName, $actualClassOrObject, $message = '')
    {
        static::assertGreaterThan(
            $expected,
            static::readAttribute($actualClassOrObject, $actualAttributeName),
            $message
        );
    }

    /**
     * Asserts that a value is greater than or equal to another value.
     *
     * @param mixed  $expected
     * @param mixed  $actual
     * @param string $message
     */
    public static function assertGreaterThanOrEqual($expected, $actual, $message = '')
    {
        static::assertThat(
            $actual,
            static::greaterThanOrEqual($expected),
            $message
        );
    }

    /**
     * Asserts that an attribute is greater than or equal to another value.
     *
     * @param mixed         $expected
     * @param string        $actualAttributeName
     * @param string|object $actualClassOrObject
     * @param string        $message
     */
    public static function assertAttributeGreaterThanOrEqual($expected, $actualAttributeName, $actualClassOrObject, $message = '')
    {
        static::assertGreaterThanOrEqual(
            $expected,
            static::readAttribute($actualClassOrObject, $actualAttributeName),
            $message
        );
    }

    /**
     * Asserts that a value is smaller than another value.
     *
     * @param mixed  $expected
     * @param mixed  $actual
     * @param string $message
     */
    public static function assertLessThan($expected, $actual, $message = '')
    {
        static::assertThat($actual, static::lessThan($expected), $message);
    }

    /**
     * Asserts that an attribute is smaller than another value.
     *
     * @param mixed         $expected
     * @param string        $actualAttributeName
     * @param string|object $actualClassOrObject
     * @param string        $message
     */
    public static function assertAttributeLessThan($expected, $actualAttributeName, $actualClassOrObject, $message = '')
    {
        static::assertLessThan(
            $expected,
            static::readAttribute($actualClassOrObject, $actualAttributeName),
            $message
        );
    }

    /**
     * Asserts that a value is smaller than or equal to another value.
     *
     * @param mixed  $expected
     * @param mixed  $actual
     * @param string $message
     */
    public static function assertLessThanOrEqual($expected, $actual, $message = '')
    {
        static::assertThat($actual, static::lessThanOrEqual($expected), $message);
    }

    /**
     * Asserts that an attribute is smaller than or equal to another value.
     *
     * @param mixed         $expected
     * @param string        $actualAttributeName
     * @param string|object $actualClassOrObject
     * @param string        $message
     */
    public static function assertAttributeLessThanOrEqual($expected, $actualAttributeName, $actualClassOrObject, $message = '')
    {
        static::assertLessThanOrEqual(
            $expected,
            static::readAttribute($actualClassOrObject, $actualAttributeName),
            $message
        );
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
    public static function assertFileEquals($expected, $actual, $message = '', $canonicalize = false, $ignoreCase = false)
    {
        static::assertFileExists($expected, $message);
        static::assertFileExists($actual, $message);

        static::assertEquals(
            \file_get_contents($expected),
            \file_get_contents($actual),
            $message,
            0,
            10,
            $canonicalize,
            $ignoreCase
        );
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
    public static function assertFileNotEquals($expected, $actual, $message = '', $canonicalize = false, $ignoreCase = false)
    {
        static::assertFileExists($expected, $message);
        static::assertFileExists($actual, $message);

        static::assertNotEquals(
            \file_get_contents($expected),
            \file_get_contents($actual),
            $message,
            0,
            10,
            $canonicalize,
            $ignoreCase
        );
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
    public static function assertStringEqualsFile($expectedFile, $actualString, $message = '', $canonicalize = false, $ignoreCase = false)
    {
        static::assertFileExists($expectedFile, $message);

        static::assertEquals(
            \file_get_contents($expectedFile),
            $actualString,
            $message,
            0,
            10,
            $canonicalize,
            $ignoreCase
        );
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
    public static function assertStringNotEqualsFile($expectedFile, $actualString, $message = '', $canonicalize = false, $ignoreCase = false)
    {
        static::assertFileExists($expectedFile, $message);

        static::assertNotEquals(
            \file_get_contents($expectedFile),
            $actualString,
            $message,
            0,
            10,
            $canonicalize,
            $ignoreCase
        );
    }

    /**
     * Asserts that a file/dir is readable.
     *
     * @param string $filename
     * @param string $message
     */
    public static function assertIsReadable($filename, $message = '')
    {
        if (!\is_string($filename)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        $constraint = new IsReadable;

        static::assertThat($filename, $constraint, $message);
    }

    /**
     * Asserts that a file/dir exists and is not readable.
     *
     * @param string $filename
     * @param string $message
     */
    public static function assertNotIsReadable($filename, $message = '')
    {
        if (!\is_string($filename)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        $constraint = new LogicalNot(
            new IsReadable
        );

        static::assertThat($filename, $constraint, $message);
    }

    /**
     * Asserts that a file/dir exists and is writable.
     *
     * @param string $filename
     * @param string $message
     */
    public static function assertIsWritable($filename, $message = '')
    {
        if (!\is_string($filename)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        $constraint = new IsWritable;

        static::assertThat($filename, $constraint, $message);
    }

    /**
     * Asserts that a file/dir exists and is not writable.
     *
     * @param string $filename
     * @param string $message
     */
    public static function assertNotIsWritable($filename, $message = '')
    {
        if (!\is_string($filename)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        $constraint = new LogicalNot(
            new IsWritable
        );

        static::assertThat($filename, $constraint, $message);
    }

    /**
     * Asserts that a directory exists.
     *
     * @param string $directory
     * @param string $message
     */
    public static function assertDirectoryExists($directory, $message = '')
    {
        if (!\is_string($directory)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        $constraint = new DirectoryExists;

        static::assertThat($directory, $constraint, $message);
    }

    /**
     * Asserts that a directory does not exist.
     *
     * @param string $directory
     * @param string $message
     */
    public static function assertDirectoryNotExists($directory, $message = '')
    {
        if (!\is_string($directory)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        $constraint = new LogicalNot(
            new DirectoryExists
        );

        static::assertThat($directory, $constraint, $message);
    }

    /**
     * Asserts that a directory exists and is readable.
     *
     * @param string $directory
     * @param string $message
     */
    public static function assertDirectoryIsReadable($directory, $message = '')
    {
        self::assertDirectoryExists($directory, $message);
        self::assertIsReadable($directory, $message);
    }

    /**
     * Asserts that a directory exists and is not readable.
     *
     * @param string $directory
     * @param string $message
     */
    public static function assertDirectoryNotIsReadable($directory, $message = '')
    {
        self::assertDirectoryExists($directory, $message);
        self::assertNotIsReadable($directory, $message);
    }

    /**
     * Asserts that a directory exists and is writable.
     *
     * @param string $directory
     * @param string $message
     */
    public static function assertDirectoryIsWritable($directory, $message = '')
    {
        self::assertDirectoryExists($directory, $message);
        self::assertIsWritable($directory, $message);
    }

    /**
     * Asserts that a directory exists and is not writable.
     *
     * @param string $directory
     * @param string $message
     */
    public static function assertDirectoryNotIsWritable($directory, $message = '')
    {
        self::assertDirectoryExists($directory, $message);
        self::assertNotIsWritable($directory, $message);
    }

    /**
     * Asserts that a file exists.
     *
     * @param string $filename
     * @param string $message
     */
    public static function assertFileExists($filename, $message = '')
    {
        if (!\is_string($filename)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        $constraint = new FileExists;

        static::assertThat($filename, $constraint, $message);
    }

    /**
     * Asserts that a file does not exist.
     *
     * @param string $filename
     * @param string $message
     */
    public static function assertFileNotExists($filename, $message = '')
    {
        if (!\is_string($filename)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        $constraint = new LogicalNot(
            new FileExists
        );

        static::assertThat($filename, $constraint, $message);
    }

    /**
     * Asserts that a file exists and is readable.
     *
     * @param string $file
     * @param string $message
     */
    public static function assertFileIsReadable($file, $message = '')
    {
        self::assertFileExists($file, $message);
        self::assertIsReadable($file, $message);
    }

    /**
     * Asserts that a file exists and is not readable.
     *
     * @param string $file
     * @param string $message
     */
    public static function assertFileNotIsReadable($file, $message = '')
    {
        self::assertFileExists($file, $message);
        self::assertNotIsReadable($file, $message);
    }

    /**
     * Asserts that a file exists and is writable.
     *
     * @param string $file
     * @param string $message
     */
    public static function assertFileIsWritable($file, $message = '')
    {
        self::assertFileExists($file, $message);
        self::assertIsWritable($file, $message);
    }

    /**
     * Asserts that a file exists and is not writable.
     *
     * @param string $file
     * @param string $message
     */
    public static function assertFileNotIsWritable($file, $message = '')
    {
        self::assertFileExists($file, $message);
        self::assertNotIsWritable($file, $message);
    }

    /**
     * Asserts that a condition is true.
     *
     * @param bool   $condition
     * @param string $message
     *
     * @throws AssertionFailedError
     */
    public static function assertTrue($condition, $message = '')
    {
        static::assertThat($condition, static::isTrue(), $message);
    }

    /**
     * Asserts that a condition is not true.
     *
     * @param bool   $condition
     * @param string $message
     *
     * @throws AssertionFailedError
     */
    public static function assertNotTrue($condition, $message = '')
    {
        static::assertThat($condition, static::logicalNot(static::isTrue()), $message);
    }

    /**
     * Asserts that a condition is false.
     *
     * @param bool   $condition
     * @param string $message
     *
     * @throws AssertionFailedError
     */
    public static function assertFalse($condition, $message = '')
    {
        static::assertThat($condition, static::isFalse(), $message);
    }

    /**
     * Asserts that a condition is not false.
     *
     * @param bool   $condition
     * @param string $message
     *
     * @throws AssertionFailedError
     */
    public static function assertNotFalse($condition, $message = '')
    {
        static::assertThat($condition, static::logicalNot(static::isFalse()), $message);
    }

    /**
     * Asserts that a variable is null.
     *
     * @param mixed  $actual
     * @param string $message
     */
    public static function assertNull($actual, $message = '')
    {
        static::assertThat($actual, static::isNull(), $message);
    }

    /**
     * Asserts that a variable is not null.
     *
     * @param mixed  $actual
     * @param string $message
     */
    public static function assertNotNull($actual, $message = '')
    {
        static::assertThat($actual, static::logicalNot(static::isNull()), $message);
    }

    /**
     * Asserts that a variable is finite.
     *
     * @param mixed  $actual
     * @param string $message
     */
    public static function assertFinite($actual, $message = '')
    {
        static::assertThat($actual, static::isFinite(), $message);
    }

    /**
     * Asserts that a variable is infinite.
     *
     * @param mixed  $actual
     * @param string $message
     */
    public static function assertInfinite($actual, $message = '')
    {
        static::assertThat($actual, static::isInfinite(), $message);
    }

    /**
     * Asserts that a variable is nan.
     *
     * @param mixed  $actual
     * @param string $message
     */
    public static function assertNan($actual, $message = '')
    {
        static::assertThat($actual, static::isNan(), $message);
    }

    /**
     * Asserts that a class has a specified attribute.
     *
     * @param string $attributeName
     * @param string $className
     * @param string $message
     */
    public static function assertClassHasAttribute($attributeName, $className, $message = '')
    {
        if (!\is_string($attributeName)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        if (!\preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $attributeName)) {
            throw InvalidArgumentHelper::factory(1, 'valid attribute name');
        }

        if (!\is_string($className) || !\class_exists($className)) {
            throw InvalidArgumentHelper::factory(2, 'class name', $className);
        }

        $constraint = new ClassHasAttribute(
            $attributeName
        );

        static::assertThat($className, $constraint, $message);
    }

    /**
     * Asserts that a class does not have a specified attribute.
     *
     * @param string $attributeName
     * @param string $className
     * @param string $message
     */
    public static function assertClassNotHasAttribute($attributeName, $className, $message = '')
    {
        if (!\is_string($attributeName)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        if (!\preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $attributeName)) {
            throw InvalidArgumentHelper::factory(1, 'valid attribute name');
        }

        if (!\is_string($className) || !\class_exists($className)) {
            throw InvalidArgumentHelper::factory(2, 'class name', $className);
        }

        $constraint = new LogicalNot(
            new ClassHasAttribute($attributeName)
        );

        static::assertThat($className, $constraint, $message);
    }

    /**
     * Asserts that a class has a specified static attribute.
     *
     * @param string $attributeName
     * @param string $className
     * @param string $message
     */
    public static function assertClassHasStaticAttribute($attributeName, $className, $message = '')
    {
        if (!\is_string($attributeName)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        if (!\preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $attributeName)) {
            throw InvalidArgumentHelper::factory(1, 'valid attribute name');
        }

        if (!\is_string($className) || !\class_exists($className)) {
            throw InvalidArgumentHelper::factory(2, 'class name', $className);
        }

        $constraint = new ClassHasStaticAttribute(
            $attributeName
        );

        static::assertThat($className, $constraint, $message);
    }

    /**
     * Asserts that a class does not have a specified static attribute.
     *
     * @param string $attributeName
     * @param string $className
     * @param string $message
     */
    public static function assertClassNotHasStaticAttribute($attributeName, $className, $message = '')
    {
        if (!\is_string($attributeName)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        if (!\preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $attributeName)) {
            throw InvalidArgumentHelper::factory(1, 'valid attribute name');
        }

        if (!\is_string($className) || !\class_exists($className)) {
            throw InvalidArgumentHelper::factory(2, 'class name', $className);
        }

        $constraint = new LogicalNot(
            new ClassHasStaticAttribute(
                $attributeName
            )
        );

        static::assertThat($className, $constraint, $message);
    }

    /**
     * Asserts that an object has a specified attribute.
     *
     * @param string $attributeName
     * @param object $object
     * @param string $message
     */
    public static function assertObjectHasAttribute($attributeName, $object, $message = '')
    {
        if (!\is_string($attributeName)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        if (!\preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $attributeName)) {
            throw InvalidArgumentHelper::factory(1, 'valid attribute name');
        }

        if (!\is_object($object)) {
            throw InvalidArgumentHelper::factory(2, 'object');
        }

        $constraint = new ObjectHasAttribute(
            $attributeName
        );

        static::assertThat($object, $constraint, $message);
    }

    /**
     * Asserts that an object does not have a specified attribute.
     *
     * @param string $attributeName
     * @param object $object
     * @param string $message
     */
    public static function assertObjectNotHasAttribute($attributeName, $object, $message = '')
    {
        if (!\is_string($attributeName)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        if (!\preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $attributeName)) {
            throw InvalidArgumentHelper::factory(1, 'valid attribute name');
        }

        if (!\is_object($object)) {
            throw InvalidArgumentHelper::factory(2, 'object');
        }

        $constraint = new LogicalNot(
            new ObjectHasAttribute($attributeName)
        );

        static::assertThat($object, $constraint, $message);
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
    public static function assertSame($expected, $actual, $message = '')
    {
        if (\is_bool($expected) && \is_bool($actual)) {
            static::assertEquals($expected, $actual, $message);
        } else {
            $constraint = new IsIdentical(
                $expected
            );

            static::assertThat($actual, $constraint, $message);
        }
    }

    /**
     * Asserts that a variable and an attribute of an object have the same type
     * and value.
     *
     * @param mixed         $expected
     * @param string        $actualAttributeName
     * @param string|object $actualClassOrObject
     * @param string        $message
     */
    public static function assertAttributeSame($expected, $actualAttributeName, $actualClassOrObject, $message = '')
    {
        static::assertSame(
            $expected,
            static::readAttribute($actualClassOrObject, $actualAttributeName),
            $message
        );
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
    public static function assertNotSame($expected, $actual, $message = '')
    {
        if (\is_bool($expected) && \is_bool($actual)) {
            static::assertNotEquals($expected, $actual, $message);
        } else {
            $constraint = new LogicalNot(
                new IsIdentical($expected)
            );

            static::assertThat($actual, $constraint, $message);
        }
    }

    /**
     * Asserts that a variable and an attribute of an object do not have the
     * same type and value.
     *
     * @param mixed         $expected
     * @param string        $actualAttributeName
     * @param string|object $actualClassOrObject
     * @param string        $message
     */
    public static function assertAttributeNotSame($expected, $actualAttributeName, $actualClassOrObject, $message = '')
    {
        static::assertNotSame(
            $expected,
            static::readAttribute($actualClassOrObject, $actualAttributeName),
            $message
        );
    }

    /**
     * Asserts that a variable is of a given type.
     *
     * @param string $expected
     * @param mixed  $actual
     * @param string $message
     */
    public static function assertInstanceOf($expected, $actual, $message = '')
    {
        if (!(\is_string($expected) && (\class_exists($expected) || \interface_exists($expected)))) {
            throw InvalidArgumentHelper::factory(1, 'class or interface name');
        }

        $constraint = new IsInstanceOf(
            $expected
        );

        static::assertThat($actual, $constraint, $message);
    }

    /**
     * Asserts that an attribute is of a given type.
     *
     * @param string        $expected
     * @param string        $attributeName
     * @param string|object $classOrObject
     * @param string        $message
     */
    public static function assertAttributeInstanceOf($expected, $attributeName, $classOrObject, $message = '')
    {
        static::assertInstanceOf(
            $expected,
            static::readAttribute($classOrObject, $attributeName),
            $message
        );
    }

    /**
     * Asserts that a variable is not of a given type.
     *
     * @param string $expected
     * @param mixed  $actual
     * @param string $message
     */
    public static function assertNotInstanceOf($expected, $actual, $message = '')
    {
        if (!(\is_string($expected) && (\class_exists($expected) || \interface_exists($expected)))) {
            throw InvalidArgumentHelper::factory(1, 'class or interface name');
        }

        $constraint = new LogicalNot(
            new IsInstanceOf($expected)
        );

        static::assertThat($actual, $constraint, $message);
    }

    /**
     * Asserts that an attribute is of a given type.
     *
     * @param string        $expected
     * @param string        $attributeName
     * @param string|object $classOrObject
     * @param string        $message
     */
    public static function assertAttributeNotInstanceOf($expected, $attributeName, $classOrObject, $message = '')
    {
        static::assertNotInstanceOf(
            $expected,
            static::readAttribute($classOrObject, $attributeName),
            $message
        );
    }

    /**
     * Asserts that a variable is of a given type.
     *
     * @param string $expected
     * @param mixed  $actual
     * @param string $message
     */
    public static function assertInternalType($expected, $actual, $message = '')
    {
        if (!\is_string($expected)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        $constraint = new IsType(
            $expected
        );

        static::assertThat($actual, $constraint, $message);
    }

    /**
     * Asserts that an attribute is of a given type.
     *
     * @param string        $expected
     * @param string        $attributeName
     * @param string|object $classOrObject
     * @param string        $message
     */
    public static function assertAttributeInternalType($expected, $attributeName, $classOrObject, $message = '')
    {
        static::assertInternalType(
            $expected,
            static::readAttribute($classOrObject, $attributeName),
            $message
        );
    }

    /**
     * Asserts that a variable is not of a given type.
     *
     * @param string $expected
     * @param mixed  $actual
     * @param string $message
     */
    public static function assertNotInternalType($expected, $actual, $message = '')
    {
        if (!\is_string($expected)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        $constraint = new LogicalNot(
            new IsType($expected)
        );

        static::assertThat($actual, $constraint, $message);
    }

    /**
     * Asserts that an attribute is of a given type.
     *
     * @param string        $expected
     * @param string        $attributeName
     * @param string|object $classOrObject
     * @param string        $message
     */
    public static function assertAttributeNotInternalType($expected, $attributeName, $classOrObject, $message = '')
    {
        static::assertNotInternalType(
            $expected,
            static::readAttribute($classOrObject, $attributeName),
            $message
        );
    }

    /**
     * Asserts that a string matches a given regular expression.
     *
     * @param string $pattern
     * @param string $string
     * @param string $message
     */
    public static function assertRegExp($pattern, $string, $message = '')
    {
        if (!\is_string($pattern)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        if (!\is_string($string)) {
            throw InvalidArgumentHelper::factory(2, 'string');
        }

        $constraint = new RegularExpression($pattern);

        static::assertThat($string, $constraint, $message);
    }

    /**
     * Asserts that a string does not match a given regular expression.
     *
     * @param string $pattern
     * @param string $string
     * @param string $message
     */
    public static function assertNotRegExp($pattern, $string, $message = '')
    {
        if (!\is_string($pattern)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        if (!\is_string($string)) {
            throw InvalidArgumentHelper::factory(2, 'string');
        }

        $constraint = new LogicalNot(
            new RegularExpression($pattern)
        );

        static::assertThat($string, $constraint, $message);
    }

    /**
     * Assert that the size of two arrays (or `Countable` or `Traversable` objects)
     * is the same.
     *
     * @param array|\Countable|\Traversable $expected
     * @param array|\Countable|\Traversable $actual
     * @param string                        $message
     */
    public static function assertSameSize($expected, $actual, $message = '')
    {
        if (!$expected instanceof Countable &&
            !$expected instanceof Traversable &&
            !\is_array($expected)) {
            throw InvalidArgumentHelper::factory(1, 'countable or traversable');
        }

        if (!$actual instanceof Countable &&
            !$actual instanceof Traversable &&
            !\is_array($actual)) {
            throw InvalidArgumentHelper::factory(2, 'countable or traversable');
        }

        static::assertThat(
            $actual,
            new SameSize($expected),
            $message
        );
    }

    /**
     * Assert that the size of two arrays (or `Countable` or `Traversable` objects)
     * is not the same.
     *
     * @param array|\Countable|\Traversable $expected
     * @param array|\Countable|\Traversable $actual
     * @param string                        $message
     */
    public static function assertNotSameSize($expected, $actual, $message = '')
    {
        if (!$expected instanceof Countable &&
            !$expected instanceof Traversable &&
            !\is_array($expected)) {
            throw InvalidArgumentHelper::factory(1, 'countable or traversable');
        }

        if (!$actual instanceof Countable &&
            !$actual instanceof Traversable &&
            !\is_array($actual)) {
            throw InvalidArgumentHelper::factory(2, 'countable or traversable');
        }

        $constraint = new LogicalNot(
            new SameSize($expected)
        );

        static::assertThat($actual, $constraint, $message);
    }

    /**
     * Asserts that a string matches a given format string.
     *
     * @param string $format
     * @param string $string
     * @param string $message
     */
    public static function assertStringMatchesFormat($format, $string, $message = '')
    {
        if (!\is_string($format)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        if (!\is_string($string)) {
            throw InvalidArgumentHelper::factory(2, 'string');
        }

        $constraint = new StringMatchesFormatDescription($format);

        static::assertThat($string, $constraint, $message);
    }

    /**
     * Asserts that a string does not match a given format string.
     *
     * @param string $format
     * @param string $string
     * @param string $message
     */
    public static function assertStringNotMatchesFormat($format, $string, $message = '')
    {
        if (!\is_string($format)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        if (!\is_string($string)) {
            throw InvalidArgumentHelper::factory(2, 'string');
        }

        $constraint = new LogicalNot(
            new StringMatchesFormatDescription($format)
        );

        static::assertThat($string, $constraint, $message);
    }

    /**
     * Asserts that a string matches a given format file.
     *
     * @param string $formatFile
     * @param string $string
     * @param string $message
     */
    public static function assertStringMatchesFormatFile($formatFile, $string, $message = '')
    {
        static::assertFileExists($formatFile, $message);

        if (!\is_string($string)) {
            throw InvalidArgumentHelper::factory(2, 'string');
        }

        $constraint = new StringMatchesFormatDescription(
            \file_get_contents($formatFile)
        );

        static::assertThat($string, $constraint, $message);
    }

    /**
     * Asserts that a string does not match a given format string.
     *
     * @param string $formatFile
     * @param string $string
     * @param string $message
     */
    public static function assertStringNotMatchesFormatFile($formatFile, $string, $message = '')
    {
        static::assertFileExists($formatFile, $message);

        if (!\is_string($string)) {
            throw InvalidArgumentHelper::factory(2, 'string');
        }

        $constraint = new LogicalNot(
            new StringMatchesFormatDescription(
                \file_get_contents($formatFile)
            )
        );

        static::assertThat($string, $constraint, $message);
    }

    /**
     * Asserts that a string starts with a given prefix.
     *
     * @param string $prefix
     * @param string $string
     * @param string $message
     */
    public static function assertStringStartsWith($prefix, $string, $message = '')
    {
        if (!\is_string($prefix)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        if (!\is_string($string)) {
            throw InvalidArgumentHelper::factory(2, 'string');
        }

        $constraint = new StringStartsWith(
            $prefix
        );

        static::assertThat($string, $constraint, $message);
    }

    /**
     * Asserts that a string starts not with a given prefix.
     *
     * @param string $prefix
     * @param string $string
     * @param string $message
     */
    public static function assertStringStartsNotWith($prefix, $string, $message = '')
    {
        if (!\is_string($prefix)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        if (!\is_string($string)) {
            throw InvalidArgumentHelper::factory(2, 'string');
        }

        $constraint = new LogicalNot(
            new StringStartsWith($prefix)
        );

        static::assertThat($string, $constraint, $message);
    }

    /**
     * Asserts that a string ends with a given suffix.
     *
     * @param string $suffix
     * @param string $string
     * @param string $message
     */
    public static function assertStringEndsWith($suffix, $string, $message = '')
    {
        if (!\is_string($suffix)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        if (!\is_string($string)) {
            throw InvalidArgumentHelper::factory(2, 'string');
        }

        $constraint = new StringEndsWith($suffix);

        static::assertThat($string, $constraint, $message);
    }

    /**
     * Asserts that a string ends not with a given suffix.
     *
     * @param string $suffix
     * @param string $string
     * @param string $message
     */
    public static function assertStringEndsNotWith($suffix, $string, $message = '')
    {
        if (!\is_string($suffix)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        if (!\is_string($string)) {
            throw InvalidArgumentHelper::factory(2, 'string');
        }

        $constraint = new LogicalNot(
            new StringEndsWith($suffix)
        );

        static::assertThat($string, $constraint, $message);
    }

    /**
     * Asserts that two XML files are equal.
     *
     * @param string $expectedFile
     * @param string $actualFile
     * @param string $message
     */
    public static function assertXmlFileEqualsXmlFile($expectedFile, $actualFile, $message = '')
    {
        $expected = Xml::loadFile($expectedFile);
        $actual   = Xml::loadFile($actualFile);

        static::assertEquals($expected, $actual, $message);
    }

    /**
     * Asserts that two XML files are not equal.
     *
     * @param string $expectedFile
     * @param string $actualFile
     * @param string $message
     */
    public static function assertXmlFileNotEqualsXmlFile($expectedFile, $actualFile, $message = '')
    {
        $expected = Xml::loadFile($expectedFile);
        $actual   = Xml::loadFile($actualFile);

        static::assertNotEquals($expected, $actual, $message);
    }

    /**
     * Asserts that two XML documents are equal.
     *
     * @param string             $expectedFile
     * @param string|DOMDocument $actualXml
     * @param string             $message
     */
    public static function assertXmlStringEqualsXmlFile($expectedFile, $actualXml, $message = '')
    {
        $expected = Xml::loadFile($expectedFile);
        $actual   = Xml::load($actualXml);

        static::assertEquals($expected, $actual, $message);
    }

    /**
     * Asserts that two XML documents are not equal.
     *
     * @param string             $expectedFile
     * @param string|DOMDocument $actualXml
     * @param string             $message
     */
    public static function assertXmlStringNotEqualsXmlFile($expectedFile, $actualXml, $message = '')
    {
        $expected = Xml::loadFile($expectedFile);
        $actual   = Xml::load($actualXml);

        static::assertNotEquals($expected, $actual, $message);
    }

    /**
     * Asserts that two XML documents are equal.
     *
     * @param string|DOMDocument $expectedXml
     * @param string|DOMDocument $actualXml
     * @param string             $message
     */
    public static function assertXmlStringEqualsXmlString($expectedXml, $actualXml, $message = '')
    {
        $expected = Xml::load($expectedXml);
        $actual   = Xml::load($actualXml);

        static::assertEquals($expected, $actual, $message);
    }

    /**
     * Asserts that two XML documents are not equal.
     *
     * @param string|DOMDocument $expectedXml
     * @param string|DOMDocument $actualXml
     * @param string             $message
     */
    public static function assertXmlStringNotEqualsXmlString($expectedXml, $actualXml, $message = '')
    {
        $expected = Xml::load($expectedXml);
        $actual   = Xml::load($actualXml);

        static::assertNotEquals($expected, $actual, $message);
    }

    /**
     * Asserts that a hierarchy of DOMElements matches.
     *
     * @param DOMElement $expectedElement
     * @param DOMElement $actualElement
     * @param bool       $checkAttributes
     * @param string     $message
     */
    public static function assertEqualXMLStructure(DOMElement $expectedElement, DOMElement $actualElement, $checkAttributes = false, $message = '')
    {
        $tmp             = new DOMDocument;
        $expectedElement = $tmp->importNode($expectedElement, true);

        $tmp           = new DOMDocument;
        $actualElement = $tmp->importNode($actualElement, true);

        unset($tmp);

        static::assertEquals(
            $expectedElement->tagName,
            $actualElement->tagName,
            $message
        );

        if ($checkAttributes) {
            static::assertEquals(
                $expectedElement->attributes->length,
                $actualElement->attributes->length,
                \sprintf(
                    '%s%sNumber of attributes on node "%s" does not match',
                    $message,
                    !empty($message) ? "\n" : '',
                    $expectedElement->tagName
                )
            );

            for ($i = 0; $i < $expectedElement->attributes->length; $i++) {
                $expectedAttribute = $expectedElement->attributes->item($i);
                $actualAttribute   = $actualElement->attributes->getNamedItem(
                    $expectedAttribute->name
                );

                if (!$actualAttribute) {
                    static::fail(
                        \sprintf(
                            '%s%sCould not find attribute "%s" on node "%s"',
                            $message,
                            !empty($message) ? "\n" : '',
                            $expectedAttribute->name,
                            $expectedElement->tagName
                        )
                    );
                }
            }
        }

        Xml::removeCharacterDataNodes($expectedElement);
        Xml::removeCharacterDataNodes($actualElement);

        static::assertEquals(
            $expectedElement->childNodes->length,
            $actualElement->childNodes->length,
            \sprintf(
                '%s%sNumber of child nodes of "%s" differs',
                $message,
                !empty($message) ? "\n" : '',
                $expectedElement->tagName
            )
        );

        for ($i = 0; $i < $expectedElement->childNodes->length; $i++) {
            static::assertEqualXMLStructure(
                $expectedElement->childNodes->item($i),
                $actualElement->childNodes->item($i),
                $checkAttributes,
                $message
            );
        }
    }

    /**
     * Evaluates a PHPUnit\Framework\Constraint matcher object.
     *
     * @param mixed      $value
     * @param Constraint $constraint
     * @param string     $message
     */
    public static function assertThat($value, Constraint $constraint, $message = '')
    {
        self::$count += \count($constraint);

        $constraint->evaluate($value, $message);
    }

    /**
     * Asserts that a string is a valid JSON string.
     *
     * @param string $actualJson
     * @param string $message
     */
    public static function assertJson($actualJson, $message = '')
    {
        if (!\is_string($actualJson)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        static::assertThat($actualJson, static::isJson(), $message);
    }

    /**
     * Asserts that two given JSON encoded objects or arrays are equal.
     *
     * @param string $expectedJson
     * @param string $actualJson
     * @param string $message
     */
    public static function assertJsonStringEqualsJsonString($expectedJson, $actualJson, $message = '')
    {
        static::assertJson($expectedJson, $message);
        static::assertJson($actualJson, $message);

        $constraint = new JsonMatches(
            $expectedJson
        );

        static::assertThat($actualJson, $constraint, $message);
    }

    /**
     * Asserts that two given JSON encoded objects or arrays are not equal.
     *
     * @param string $expectedJson
     * @param string $actualJson
     * @param string $message
     */
    public static function assertJsonStringNotEqualsJsonString($expectedJson, $actualJson, $message = '')
    {
        static::assertJson($expectedJson, $message);
        static::assertJson($actualJson, $message);

        $constraint = new JsonMatches(
            $expectedJson
        );

        static::assertThat($actualJson, new LogicalNot($constraint), $message);
    }

    /**
     * Asserts that the generated JSON encoded object and the content of the given file are equal.
     *
     * @param string $expectedFile
     * @param string $actualJson
     * @param string $message
     */
    public static function assertJsonStringEqualsJsonFile($expectedFile, $actualJson, $message = '')
    {
        static::assertFileExists($expectedFile, $message);
        $expectedJson = \file_get_contents($expectedFile);

        static::assertJson($expectedJson, $message);
        static::assertJson($actualJson, $message);

        $constraint = new JsonMatches(
            $expectedJson
        );

        static::assertThat($actualJson, $constraint, $message);
    }

    /**
     * Asserts that the generated JSON encoded object and the content of the given file are not equal.
     *
     * @param string $expectedFile
     * @param string $actualJson
     * @param string $message
     */
    public static function assertJsonStringNotEqualsJsonFile($expectedFile, $actualJson, $message = '')
    {
        static::assertFileExists($expectedFile, $message);
        $expectedJson = \file_get_contents($expectedFile);

        static::assertJson($expectedJson, $message);
        static::assertJson($actualJson, $message);

        $constraint = new JsonMatches(
            $expectedJson
        );

        static::assertThat($actualJson, new LogicalNot($constraint), $message);
    }

    /**
     * Asserts that two JSON files are equal.
     *
     * @param string $expectedFile
     * @param string $actualFile
     * @param string $message
     */
    public static function assertJsonFileEqualsJsonFile($expectedFile, $actualFile, $message = '')
    {
        static::assertFileExists($expectedFile, $message);
        static::assertFileExists($actualFile, $message);

        $actualJson   = \file_get_contents($actualFile);
        $expectedJson = \file_get_contents($expectedFile);

        static::assertJson($expectedJson, $message);
        static::assertJson($actualJson, $message);

        $constraintExpected = new JsonMatches(
            $expectedJson
        );

        $constraintActual = new JsonMatches($actualJson);

        static::assertThat($expectedJson, $constraintActual, $message);
        static::assertThat($actualJson, $constraintExpected, $message);
    }

    /**
     * Asserts that two JSON files are not equal.
     *
     * @param string $expectedFile
     * @param string $actualFile
     * @param string $message
     */
    public static function assertJsonFileNotEqualsJsonFile($expectedFile, $actualFile, $message = '')
    {
        static::assertFileExists($expectedFile, $message);
        static::assertFileExists($actualFile, $message);

        $actualJson   = \file_get_contents($actualFile);
        $expectedJson = \file_get_contents($expectedFile);

        static::assertJson($expectedJson, $message);
        static::assertJson($actualJson, $message);

        $constraintExpected = new JsonMatches(
            $expectedJson
        );

        $constraintActual = new JsonMatches($actualJson);

        static::assertThat($expectedJson, new LogicalNot($constraintActual), $message);
        static::assertThat($actualJson, new LogicalNot($constraintExpected), $message);
    }

    /**
     * @return LogicalAnd
     */
    public static function logicalAnd()
    {
        $constraints = \func_get_args();

        $constraint = new LogicalAnd;
        $constraint->setConstraints($constraints);

        return $constraint;
    }

    /**
     * @return LogicalOr
     */
    public static function logicalOr()
    {
        $constraints = \func_get_args();

        $constraint = new LogicalOr;
        $constraint->setConstraints($constraints);

        return $constraint;
    }

    /**
     * @param Constraint $constraint
     *
     * @return LogicalNot
     */
    public static function logicalNot(Constraint $constraint)
    {
        return new LogicalNot($constraint);
    }

    /**
     * @return LogicalXor
     */
    public static function logicalXor()
    {
        $constraints = \func_get_args();

        $constraint = new LogicalXor;
        $constraint->setConstraints($constraints);

        return $constraint;
    }

    /**
     * @return IsAnything
     */
    public static function anything()
    {
        return new IsAnything;
    }

    /**
     * @return IsTrue
     */
    public static function isTrue()
    {
        return new IsTrue;
    }

    /**
     * @param callable $callback
     *
     * @return Callback
     */
    public static function callback($callback)
    {
        return new Callback($callback);
    }

    /**
     * @return IsFalse
     */
    public static function isFalse()
    {
        return new IsFalse;
    }

    /**
     * @return IsJson
     */
    public static function isJson()
    {
        return new IsJson;
    }

    /**
     * @return IsNull
     */
    public static function isNull()
    {
        return new IsNull;
    }

    /**
     * @return IsFinite
     */
    public static function isFinite()
    {
        return new IsFinite;
    }

    /**
     * @return IsInfinite
     */
    public static function isInfinite()
    {
        return new IsInfinite;
    }

    /**
     * @return IsNan
     */
    public static function isNan()
    {
        return new IsNan;
    }

    /**
     * @param Constraint $constraint
     * @param string     $attributeName
     *
     * @return Attribute
     */
    public static function attribute(Constraint $constraint, $attributeName)
    {
        return new Attribute(
            $constraint,
            $attributeName
        );
    }

    /**
     * @param mixed $value
     * @param bool  $checkForObjectIdentity
     * @param bool  $checkForNonObjectIdentity
     *
     * @return TraversableContains
     */
    public static function contains($value, $checkForObjectIdentity = true, $checkForNonObjectIdentity = false)
    {
        return new TraversableContains($value, $checkForObjectIdentity, $checkForNonObjectIdentity);
    }

    /**
     * @param string $type
     *
     * @return TraversableContainsOnly
     */
    public static function containsOnly($type)
    {
        return new TraversableContainsOnly($type);
    }

    /**
     * @param string $classname
     *
     * @return TraversableContainsOnly
     */
    public static function containsOnlyInstancesOf($classname)
    {
        return new TraversableContainsOnly($classname, false);
    }

    /**
     * @param mixed $key
     *
     * @return ArrayHasKey
     */
    public static function arrayHasKey($key)
    {
        return new ArrayHasKey($key);
    }

    /**
     * @param mixed $value
     * @param float $delta
     * @param int   $maxDepth
     * @param bool  $canonicalize
     * @param bool  $ignoreCase
     *
     * @return IsEqual
     */
    public static function equalTo($value, $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
    {
        return new IsEqual(
            $value,
            $delta,
            $maxDepth,
            $canonicalize,
            $ignoreCase
        );
    }

    /**
     * @param string $attributeName
     * @param mixed  $value
     * @param float  $delta
     * @param int    $maxDepth
     * @param bool   $canonicalize
     * @param bool   $ignoreCase
     *
     * @return Attribute
     */
    public static function attributeEqualTo($attributeName, $value, $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
    {
        return static::attribute(
            static::equalTo(
                $value,
                $delta,
                $maxDepth,
                $canonicalize,
                $ignoreCase
            ),
            $attributeName
        );
    }

    /**
     * @return IsEmpty
     */
    public static function isEmpty()
    {
        return new IsEmpty;
    }

    /**
     * @return IsWritable
     */
    public static function isWritable()
    {
        return new IsWritable;
    }

    /**
     * @return IsReadable
     */
    public static function isReadable()
    {
        return new IsReadable;
    }

    /**
     * @return DirectoryExists
     */
    public static function directoryExists()
    {
        return new DirectoryExists;
    }

    /**
     * @return FileExists
     */
    public static function fileExists()
    {
        return new FileExists;
    }

    /**
     * @param mixed $value
     *
     * @return GreaterThan
     */
    public static function greaterThan($value)
    {
        return new GreaterThan($value);
    }

    /**
     * @param mixed $value
     *
     * @return LogicalOr
     */
    public static function greaterThanOrEqual($value)
    {
        return static::logicalOr(
            new IsEqual($value),
            new GreaterThan($value)
        );
    }

    /**
     * @param string $attributeName
     *
     * @return ClassHasAttribute
     */
    public static function classHasAttribute($attributeName)
    {
        return new ClassHasAttribute(
            $attributeName
        );
    }

    /**
     * @param string $attributeName
     *
     * @return ClassHasStaticAttribute
     */
    public static function classHasStaticAttribute($attributeName)
    {
        return new ClassHasStaticAttribute(
            $attributeName
        );
    }

    /**
     * @param string $attributeName
     *
     * @return ObjectHasAttribute
     */
    public static function objectHasAttribute($attributeName)
    {
        return new ObjectHasAttribute(
            $attributeName
        );
    }

    /**
     * @param mixed $value
     *
     * @return IsIdentical
     */
    public static function identicalTo($value)
    {
        return new IsIdentical($value);
    }

    /**
     * @param string $className
     *
     * @return IsInstanceOf
     */
    public static function isInstanceOf($className)
    {
        return new IsInstanceOf($className);
    }

    /**
     * @param string $type
     *
     * @return IsType
     */
    public static function isType($type)
    {
        return new IsType($type);
    }

    /**
     * @param mixed $value
     *
     * @return LessThan
     */
    public static function lessThan($value)
    {
        return new LessThan($value);
    }

    /**
     * @param mixed $value
     *
     * @return LogicalOr
     */
    public static function lessThanOrEqual($value)
    {
        return static::logicalOr(
            new IsEqual($value),
            new LessThan($value)
        );
    }

    /**
     * @param string $pattern
     *
     * @return RegularExpression
     */
    public static function matchesRegularExpression($pattern)
    {
        return new RegularExpression($pattern);
    }

    /**
     * @param string $string
     *
     * @return StringMatchesFormatDescription
     */
    public static function matches($string)
    {
        return new StringMatchesFormatDescription($string);
    }

    /**
     * @param mixed $prefix
     *
     * @return StringStartsWith
     */
    public static function stringStartsWith($prefix)
    {
        return new StringStartsWith($prefix);
    }

    /**
     * @param string $string
     * @param bool   $case
     *
     * @return StringContains
     */
    public static function stringContains($string, $case = true)
    {
        return new StringContains($string, $case);
    }

    /**
     * @param mixed $suffix
     *
     * @return StringEndsWith
     */
    public static function stringEndsWith($suffix)
    {
        return new StringEndsWith($suffix);
    }

    /**
     * @param int $count
     *
     * @return Count
     */
    public static function countOf($count)
    {
        return new Count($count);
    }

    /**
     * Fails a test with the given message.
     *
     * @param string $message
     *
     * @throws AssertionFailedError
     */
    public static function fail($message = '')
    {
        self::$count++;

        throw new AssertionFailedError($message);
    }

    /**
     * Returns the value of an attribute of a class or an object.
     * This also works for attributes that are declared protected or private.
     *
     * @param string|object $classOrObject
     * @param string        $attributeName
     *
     * @return mixed
     *
     * @throws Exception
     */
    public static function readAttribute($classOrObject, $attributeName)
    {
        if (!\is_string($attributeName)) {
            throw InvalidArgumentHelper::factory(2, 'string');
        }

        if (!\preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $attributeName)) {
            throw InvalidArgumentHelper::factory(2, 'valid attribute name');
        }

        if (\is_string($classOrObject)) {
            if (!\class_exists($classOrObject)) {
                throw InvalidArgumentHelper::factory(
                    1,
                    'class name'
                );
            }

            return static::getStaticAttribute(
                $classOrObject,
                $attributeName
            );
        }

        if (\is_object($classOrObject)) {
            return static::getObjectAttribute(
                $classOrObject,
                $attributeName
            );
        }

        throw InvalidArgumentHelper::factory(
            1,
            'class name or object'
        );
    }

    /**
     * Returns the value of a static attribute.
     * This also works for attributes that are declared protected or private.
     *
     * @param string $className
     * @param string $attributeName
     *
     * @return mixed
     *
     * @throws Exception
     */
    public static function getStaticAttribute($className, $attributeName)
    {
        if (!\is_string($className)) {
            throw InvalidArgumentHelper::factory(1, 'string');
        }

        if (!\class_exists($className)) {
            throw InvalidArgumentHelper::factory(1, 'class name');
        }

        if (!\is_string($attributeName)) {
            throw InvalidArgumentHelper::factory(2, 'string');
        }

        if (!\preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $attributeName)) {
            throw InvalidArgumentHelper::factory(2, 'valid attribute name');
        }

        $class = new ReflectionClass($className);

        while ($class) {
            $attributes = $class->getStaticProperties();

            if (\array_key_exists($attributeName, $attributes)) {
                return $attributes[$attributeName];
            }

            $class = $class->getParentClass();
        }

        throw new Exception(
            \sprintf(
                'Attribute "%s" not found in class.',
                $attributeName
            )
        );
    }

    /**
     * Returns the value of an object's attribute.
     * This also works for attributes that are declared protected or private.
     *
     * @param object $object
     * @param string $attributeName
     *
     * @return mixed
     *
     * @throws Exception
     */
    public static function getObjectAttribute($object, $attributeName)
    {
        if (!\is_object($object)) {
            throw InvalidArgumentHelper::factory(1, 'object');
        }

        if (!\is_string($attributeName)) {
            throw InvalidArgumentHelper::factory(2, 'string');
        }

        if (!\preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $attributeName)) {
            throw InvalidArgumentHelper::factory(2, 'valid attribute name');
        }

        try {
            $attribute = new ReflectionProperty($object, $attributeName);
        } catch (ReflectionException $e) {
            $reflector = new ReflectionObject($object);

            while ($reflector = $reflector->getParentClass()) {
                try {
                    $attribute = $reflector->getProperty($attributeName);

                    break;
                } catch (ReflectionException $e) {
                }
            }
        }

        if (isset($attribute)) {
            if (!$attribute || $attribute->isPublic()) {
                return $object->$attributeName;
            }

            $attribute->setAccessible(true);
            $value = $attribute->getValue($object);
            $attribute->setAccessible(false);

            return $value;
        }

        throw new Exception(
            \sprintf(
                'Attribute "%s" not found in object.',
                $attributeName
            )
        );
    }

    /**
     * Mark the test as incomplete.
     *
     * @param string $message
     *
     * @throws IncompleteTestError
     */
    public static function markTestIncomplete($message = '')
    {
        throw new IncompleteTestError($message);
    }

    /**
     * Mark the test as skipped.
     *
     * @param string $message
     *
     * @throws SkippedTestError
     */
    public static function markTestSkipped($message = '')
    {
        throw new SkippedTestError($message);
    }

    /**
     * Return the current assertion count.
     *
     * @return int
     */
    public static function getCount()
    {
        return self::$count;
    }

    /**
     * Reset the assertion counter.
     */
    public static function resetCount()
    {
        self::$count = 0;
    }
}
