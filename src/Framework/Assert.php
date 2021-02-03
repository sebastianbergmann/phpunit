<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use const DEBUG_BACKTRACE_IGNORE_ARGS;
use const PHP_EOL;
use function array_shift;
use function array_unshift;
use function assert;
use function class_exists;
use function count;
use function debug_backtrace;
use function explode;
use function file_get_contents;
use function func_get_args;
use function implode;
use function interface_exists;
use function is_array;
use function is_bool;
use function is_int;
use function is_iterable;
use function is_object;
use function is_string;
use function preg_match;
use function preg_split;
use function sprintf;
use function strpos;
use ArrayAccess;
use Countable;
use DOMAttr;
use DOMDocument;
use DOMElement;
use PHPUnit\Framework\Constraint\ArrayHasKey;
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
use PHPUnit\Framework\Constraint\IsEqualCanonicalizing;
use PHPUnit\Framework\Constraint\IsEqualIgnoringCase;
use PHPUnit\Framework\Constraint\IsEqualWithDelta;
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
use PHPUnit\Framework\Constraint\ObjectEquals;
use PHPUnit\Framework\Constraint\ObjectHasAttribute;
use PHPUnit\Framework\Constraint\RegularExpression;
use PHPUnit\Framework\Constraint\SameSize;
use PHPUnit\Framework\Constraint\StringContains;
use PHPUnit\Framework\Constraint\StringEndsWith;
use PHPUnit\Framework\Constraint\StringMatchesFormatDescription;
use PHPUnit\Framework\Constraint\StringStartsWith;
use PHPUnit\Framework\Constraint\TraversableContainsEqual;
use PHPUnit\Framework\Constraint\TraversableContainsIdentical;
use PHPUnit\Framework\Constraint\TraversableContainsOnly;
use PHPUnit\Util\Type;
use PHPUnit\Util\Xml;
use PHPUnit\Util\Xml\Loader as XmlLoader;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
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
     * @param int|string        $key
     * @param array|ArrayAccess $array
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws ExpectationFailedException
     */
    public static function assertArrayHasKey($key, $array, string $message = ''): void
    {
        if (!(is_int($key) || is_string($key))) {
            throw InvalidArgumentException::create(
                1,
                'integer or string'
            );
        }

        if (!(is_array($array) || $array instanceof ArrayAccess)) {
            throw InvalidArgumentException::create(
                2,
                'array or ArrayAccess'
            );
        }

        $constraint = new ArrayHasKey($key);

        static::assertThat($array, $constraint, $message);
    }

    /**
     * Asserts that an array does not have a specified key.
     *
     * @param int|string        $key
     * @param array|ArrayAccess $array
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws ExpectationFailedException
     */
    public static function assertArrayNotHasKey($key, $array, string $message = ''): void
    {
        if (!(is_int($key) || is_string($key))) {
            throw InvalidArgumentException::create(
                1,
                'integer or string'
            );
        }

        if (!(is_array($array) || $array instanceof ArrayAccess)) {
            throw InvalidArgumentException::create(
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
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws ExpectationFailedException
     */
    public static function assertContains($needle, iterable $haystack, string $message = ''): void
    {
        $constraint = new TraversableContainsIdentical($needle);

        static::assertThat($haystack, $constraint, $message);
    }

    public static function assertContainsEquals($needle, iterable $haystack, string $message = ''): void
    {
        $constraint = new TraversableContainsEqual($needle);

        static::assertThat($haystack, $constraint, $message);
    }

    /**
     * Asserts that a haystack does not contain a needle.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws ExpectationFailedException
     */
    public static function assertNotContains($needle, iterable $haystack, string $message = ''): void
    {
        $constraint = new LogicalNot(
            new TraversableContainsIdentical($needle)
        );

        static::assertThat($haystack, $constraint, $message);
    }

    public static function assertNotContainsEquals($needle, iterable $haystack, string $message = ''): void
    {
        $constraint = new LogicalNot(new TraversableContainsEqual($needle));

        static::assertThat($haystack, $constraint, $message);
    }

    /**
     * Asserts that a haystack contains only values of a given type.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertContainsOnly(string $type, iterable $haystack, ?bool $isNativeType = null, string $message = ''): void
    {
        if ($isNativeType === null) {
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
     * Asserts that a haystack contains only instances of a given class name.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertContainsOnlyInstancesOf(string $className, iterable $haystack, string $message = ''): void
    {
        static::assertThat(
            $haystack,
            new TraversableContainsOnly(
                $className,
                false
            ),
            $message
        );
    }

    /**
     * Asserts that a haystack does not contain only values of a given type.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertNotContainsOnly(string $type, iterable $haystack, ?bool $isNativeType = null, string $message = ''): void
    {
        if ($isNativeType === null) {
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
     * Asserts the number of elements of an array, Countable or Traversable.
     *
     * @param Countable|iterable $haystack
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws ExpectationFailedException
     */
    public static function assertCount(int $expectedCount, $haystack, string $message = ''): void
    {
        if (!$haystack instanceof Countable && !is_iterable($haystack)) {
            throw InvalidArgumentException::create(2, 'countable or iterable');
        }

        static::assertThat(
            $haystack,
            new Count($expectedCount),
            $message
        );
    }

    /**
     * Asserts the number of elements of an array, Countable or Traversable.
     *
     * @param Countable|iterable $haystack
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws ExpectationFailedException
     */
    public static function assertNotCount(int $expectedCount, $haystack, string $message = ''): void
    {
        if (!$haystack instanceof Countable && !is_iterable($haystack)) {
            throw InvalidArgumentException::create(2, 'countable or iterable');
        }

        $constraint = new LogicalNot(
            new Count($expectedCount)
        );

        static::assertThat($haystack, $constraint, $message);
    }

    /**
     * Asserts that two variables are equal.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertEquals($expected, $actual, string $message = ''): void
    {
        $constraint = new IsEqual($expected);

        static::assertThat($actual, $constraint, $message);
    }

    /**
     * Asserts that two variables are equal (canonicalizing).
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertEqualsCanonicalizing($expected, $actual, string $message = ''): void
    {
        $constraint = new IsEqualCanonicalizing($expected);

        static::assertThat($actual, $constraint, $message);
    }

    /**
     * Asserts that two variables are equal (ignoring case).
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertEqualsIgnoringCase($expected, $actual, string $message = ''): void
    {
        $constraint = new IsEqualIgnoringCase($expected);

        static::assertThat($actual, $constraint, $message);
    }

    /**
     * Asserts that two variables are equal (with delta).
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertEqualsWithDelta($expected, $actual, float $delta, string $message = ''): void
    {
        $constraint = new IsEqualWithDelta(
            $expected,
            $delta
        );

        static::assertThat($actual, $constraint, $message);
    }

    /**
     * Asserts that two variables are not equal.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertNotEquals($expected, $actual, string $message = ''): void
    {
        $constraint = new LogicalNot(
            new IsEqual($expected)
        );

        static::assertThat($actual, $constraint, $message);
    }

    /**
     * Asserts that two variables are not equal (canonicalizing).
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertNotEqualsCanonicalizing($expected, $actual, string $message = ''): void
    {
        $constraint = new LogicalNot(
            new IsEqualCanonicalizing($expected)
        );

        static::assertThat($actual, $constraint, $message);
    }

    /**
     * Asserts that two variables are not equal (ignoring case).
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertNotEqualsIgnoringCase($expected, $actual, string $message = ''): void
    {
        $constraint = new LogicalNot(
            new IsEqualIgnoringCase($expected)
        );

        static::assertThat($actual, $constraint, $message);
    }

    /**
     * Asserts that two variables are not equal (with delta).
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertNotEqualsWithDelta($expected, $actual, float $delta, string $message = ''): void
    {
        $constraint = new LogicalNot(
            new IsEqualWithDelta(
                $expected,
                $delta
            )
        );

        static::assertThat($actual, $constraint, $message);
    }

    /**
     * @throws ExpectationFailedException
     */
    public static function assertObjectEquals(object $expected, object $actual, string $method = 'equals', string $message = ''): void
    {
        static::assertThat(
            $actual,
            static::objectEquals($expected, $method),
            $message
        );
    }

    /**
     * Asserts that a variable is empty.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert empty $actual
     */
    public static function assertEmpty($actual, string $message = ''): void
    {
        static::assertThat($actual, static::isEmpty(), $message);
    }

    /**
     * Asserts that a variable is not empty.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert !empty $actual
     */
    public static function assertNotEmpty($actual, string $message = ''): void
    {
        static::assertThat($actual, static::logicalNot(static::isEmpty()), $message);
    }

    /**
     * Asserts that a value is greater than another value.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertGreaterThan($expected, $actual, string $message = ''): void
    {
        static::assertThat($actual, static::greaterThan($expected), $message);
    }

    /**
     * Asserts that a value is greater than or equal to another value.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertGreaterThanOrEqual($expected, $actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            static::greaterThanOrEqual($expected),
            $message
        );
    }

    /**
     * Asserts that a value is smaller than another value.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertLessThan($expected, $actual, string $message = ''): void
    {
        static::assertThat($actual, static::lessThan($expected), $message);
    }

    /**
     * Asserts that a value is smaller than or equal to another value.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertLessThanOrEqual($expected, $actual, string $message = ''): void
    {
        static::assertThat($actual, static::lessThanOrEqual($expected), $message);
    }

    /**
     * Asserts that the contents of one file is equal to the contents of another
     * file.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertFileEquals(string $expected, string $actual, string $message = ''): void
    {
        static::assertFileExists($expected, $message);
        static::assertFileExists($actual, $message);

        $constraint = new IsEqual(file_get_contents($expected));

        static::assertThat(file_get_contents($actual), $constraint, $message);
    }

    /**
     * Asserts that the contents of one file is equal to the contents of another
     * file (canonicalizing).
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertFileEqualsCanonicalizing(string $expected, string $actual, string $message = ''): void
    {
        static::assertFileExists($expected, $message);
        static::assertFileExists($actual, $message);

        $constraint = new IsEqualCanonicalizing(
            file_get_contents($expected)
        );

        static::assertThat(file_get_contents($actual), $constraint, $message);
    }

    /**
     * Asserts that the contents of one file is equal to the contents of another
     * file (ignoring case).
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertFileEqualsIgnoringCase(string $expected, string $actual, string $message = ''): void
    {
        static::assertFileExists($expected, $message);
        static::assertFileExists($actual, $message);

        $constraint = new IsEqualIgnoringCase(file_get_contents($expected));

        static::assertThat(file_get_contents($actual), $constraint, $message);
    }

    /**
     * Asserts that the contents of one file is not equal to the contents of
     * another file.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertFileNotEquals(string $expected, string $actual, string $message = ''): void
    {
        static::assertFileExists($expected, $message);
        static::assertFileExists($actual, $message);

        $constraint = new LogicalNot(
            new IsEqual(file_get_contents($expected))
        );

        static::assertThat(file_get_contents($actual), $constraint, $message);
    }

    /**
     * Asserts that the contents of one file is not equal to the contents of another
     * file (canonicalizing).
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertFileNotEqualsCanonicalizing(string $expected, string $actual, string $message = ''): void
    {
        static::assertFileExists($expected, $message);
        static::assertFileExists($actual, $message);

        $constraint = new LogicalNot(
            new IsEqualCanonicalizing(file_get_contents($expected))
        );

        static::assertThat(file_get_contents($actual), $constraint, $message);
    }

    /**
     * Asserts that the contents of one file is not equal to the contents of another
     * file (ignoring case).
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertFileNotEqualsIgnoringCase(string $expected, string $actual, string $message = ''): void
    {
        static::assertFileExists($expected, $message);
        static::assertFileExists($actual, $message);

        $constraint = new LogicalNot(
            new IsEqualIgnoringCase(file_get_contents($expected))
        );

        static::assertThat(file_get_contents($actual), $constraint, $message);
    }

    /**
     * Asserts that the contents of a string is equal
     * to the contents of a file.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertStringEqualsFile(string $expectedFile, string $actualString, string $message = ''): void
    {
        static::assertFileExists($expectedFile, $message);

        $constraint = new IsEqual(file_get_contents($expectedFile));

        static::assertThat($actualString, $constraint, $message);
    }

    /**
     * Asserts that the contents of a string is equal
     * to the contents of a file (canonicalizing).
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertStringEqualsFileCanonicalizing(string $expectedFile, string $actualString, string $message = ''): void
    {
        static::assertFileExists($expectedFile, $message);

        $constraint = new IsEqualCanonicalizing(file_get_contents($expectedFile));

        static::assertThat($actualString, $constraint, $message);
    }

    /**
     * Asserts that the contents of a string is equal
     * to the contents of a file (ignoring case).
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertStringEqualsFileIgnoringCase(string $expectedFile, string $actualString, string $message = ''): void
    {
        static::assertFileExists($expectedFile, $message);

        $constraint = new IsEqualIgnoringCase(file_get_contents($expectedFile));

        static::assertThat($actualString, $constraint, $message);
    }

    /**
     * Asserts that the contents of a string is not equal
     * to the contents of a file.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertStringNotEqualsFile(string $expectedFile, string $actualString, string $message = ''): void
    {
        static::assertFileExists($expectedFile, $message);

        $constraint = new LogicalNot(
            new IsEqual(file_get_contents($expectedFile))
        );

        static::assertThat($actualString, $constraint, $message);
    }

    /**
     * Asserts that the contents of a string is not equal
     * to the contents of a file (canonicalizing).
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertStringNotEqualsFileCanonicalizing(string $expectedFile, string $actualString, string $message = ''): void
    {
        static::assertFileExists($expectedFile, $message);

        $constraint = new LogicalNot(
            new IsEqualCanonicalizing(file_get_contents($expectedFile))
        );

        static::assertThat($actualString, $constraint, $message);
    }

    /**
     * Asserts that the contents of a string is not equal
     * to the contents of a file (ignoring case).
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertStringNotEqualsFileIgnoringCase(string $expectedFile, string $actualString, string $message = ''): void
    {
        static::assertFileExists($expectedFile, $message);

        $constraint = new LogicalNot(
            new IsEqualIgnoringCase(file_get_contents($expectedFile))
        );

        static::assertThat($actualString, $constraint, $message);
    }

    /**
     * Asserts that a file/dir is readable.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertIsReadable(string $filename, string $message = ''): void
    {
        static::assertThat($filename, new IsReadable, $message);
    }

    /**
     * Asserts that a file/dir exists and is not readable.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertIsNotReadable(string $filename, string $message = ''): void
    {
        static::assertThat($filename, new LogicalNot(new IsReadable), $message);
    }

    /**
     * Asserts that a file/dir exists and is not readable.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @codeCoverageIgnore
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/4062
     */
    public static function assertNotIsReadable(string $filename, string $message = ''): void
    {
        self::createWarning('assertNotIsReadable() is deprecated and will be removed in PHPUnit 10. Refactor your code to use assertIsNotReadable() instead.');

        static::assertThat($filename, new LogicalNot(new IsReadable), $message);
    }

    /**
     * Asserts that a file/dir exists and is writable.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertIsWritable(string $filename, string $message = ''): void
    {
        static::assertThat($filename, new IsWritable, $message);
    }

    /**
     * Asserts that a file/dir exists and is not writable.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertIsNotWritable(string $filename, string $message = ''): void
    {
        static::assertThat($filename, new LogicalNot(new IsWritable), $message);
    }

    /**
     * Asserts that a file/dir exists and is not writable.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @codeCoverageIgnore
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/4065
     */
    public static function assertNotIsWritable(string $filename, string $message = ''): void
    {
        self::createWarning('assertNotIsWritable() is deprecated and will be removed in PHPUnit 10. Refactor your code to use assertIsNotWritable() instead.');

        static::assertThat($filename, new LogicalNot(new IsWritable), $message);
    }

    /**
     * Asserts that a directory exists.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertDirectoryExists(string $directory, string $message = ''): void
    {
        static::assertThat($directory, new DirectoryExists, $message);
    }

    /**
     * Asserts that a directory does not exist.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertDirectoryDoesNotExist(string $directory, string $message = ''): void
    {
        static::assertThat($directory, new LogicalNot(new DirectoryExists), $message);
    }

    /**
     * Asserts that a directory does not exist.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @codeCoverageIgnore
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/4068
     */
    public static function assertDirectoryNotExists(string $directory, string $message = ''): void
    {
        self::createWarning('assertDirectoryNotExists() is deprecated and will be removed in PHPUnit 10. Refactor your code to use assertDirectoryDoesNotExist() instead.');

        static::assertThat($directory, new LogicalNot(new DirectoryExists), $message);
    }

    /**
     * Asserts that a directory exists and is readable.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertDirectoryIsReadable(string $directory, string $message = ''): void
    {
        self::assertDirectoryExists($directory, $message);
        self::assertIsReadable($directory, $message);
    }

    /**
     * Asserts that a directory exists and is not readable.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertDirectoryIsNotReadable(string $directory, string $message = ''): void
    {
        self::assertDirectoryExists($directory, $message);
        self::assertIsNotReadable($directory, $message);
    }

    /**
     * Asserts that a directory exists and is not readable.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @codeCoverageIgnore
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/4071
     */
    public static function assertDirectoryNotIsReadable(string $directory, string $message = ''): void
    {
        self::createWarning('assertDirectoryNotIsReadable() is deprecated and will be removed in PHPUnit 10. Refactor your code to use assertDirectoryIsNotReadable() instead.');

        self::assertDirectoryExists($directory, $message);
        self::assertIsNotReadable($directory, $message);
    }

    /**
     * Asserts that a directory exists and is writable.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertDirectoryIsWritable(string $directory, string $message = ''): void
    {
        self::assertDirectoryExists($directory, $message);
        self::assertIsWritable($directory, $message);
    }

    /**
     * Asserts that a directory exists and is not writable.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertDirectoryIsNotWritable(string $directory, string $message = ''): void
    {
        self::assertDirectoryExists($directory, $message);
        self::assertIsNotWritable($directory, $message);
    }

    /**
     * Asserts that a directory exists and is not writable.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @codeCoverageIgnore
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/4074
     */
    public static function assertDirectoryNotIsWritable(string $directory, string $message = ''): void
    {
        self::createWarning('assertDirectoryNotIsWritable() is deprecated and will be removed in PHPUnit 10. Refactor your code to use assertDirectoryIsNotWritable() instead.');

        self::assertDirectoryExists($directory, $message);
        self::assertIsNotWritable($directory, $message);
    }

    /**
     * Asserts that a file exists.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertFileExists(string $filename, string $message = ''): void
    {
        static::assertThat($filename, new FileExists, $message);
    }

    /**
     * Asserts that a file does not exist.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertFileDoesNotExist(string $filename, string $message = ''): void
    {
        static::assertThat($filename, new LogicalNot(new FileExists), $message);
    }

    /**
     * Asserts that a file does not exist.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @codeCoverageIgnore
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/4077
     */
    public static function assertFileNotExists(string $filename, string $message = ''): void
    {
        self::createWarning('assertFileNotExists() is deprecated and will be removed in PHPUnit 10. Refactor your code to use assertFileDoesNotExist() instead.');

        static::assertThat($filename, new LogicalNot(new FileExists), $message);
    }

    /**
     * Asserts that a file exists and is readable.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertFileIsReadable(string $file, string $message = ''): void
    {
        self::assertFileExists($file, $message);
        self::assertIsReadable($file, $message);
    }

    /**
     * Asserts that a file exists and is not readable.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertFileIsNotReadable(string $file, string $message = ''): void
    {
        self::assertFileExists($file, $message);
        self::assertIsNotReadable($file, $message);
    }

    /**
     * Asserts that a file exists and is not readable.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @codeCoverageIgnore
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/4080
     */
    public static function assertFileNotIsReadable(string $file, string $message = ''): void
    {
        self::createWarning('assertFileNotIsReadable() is deprecated and will be removed in PHPUnit 10. Refactor your code to use assertFileIsNotReadable() instead.');

        self::assertFileExists($file, $message);
        self::assertIsNotReadable($file, $message);
    }

    /**
     * Asserts that a file exists and is writable.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertFileIsWritable(string $file, string $message = ''): void
    {
        self::assertFileExists($file, $message);
        self::assertIsWritable($file, $message);
    }

    /**
     * Asserts that a file exists and is not writable.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertFileIsNotWritable(string $file, string $message = ''): void
    {
        self::assertFileExists($file, $message);
        self::assertIsNotWritable($file, $message);
    }

    /**
     * Asserts that a file exists and is not writable.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @codeCoverageIgnore
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/4083
     */
    public static function assertFileNotIsWritable(string $file, string $message = ''): void
    {
        self::createWarning('assertFileNotIsWritable() is deprecated and will be removed in PHPUnit 10. Refactor your code to use assertFileIsNotWritable() instead.');

        self::assertFileExists($file, $message);
        self::assertIsNotWritable($file, $message);
    }

    /**
     * Asserts that a condition is true.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert true $condition
     */
    public static function assertTrue($condition, string $message = ''): void
    {
        static::assertThat($condition, static::isTrue(), $message);
    }

    /**
     * Asserts that a condition is not true.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert !true $condition
     */
    public static function assertNotTrue($condition, string $message = ''): void
    {
        static::assertThat($condition, static::logicalNot(static::isTrue()), $message);
    }

    /**
     * Asserts that a condition is false.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert false $condition
     */
    public static function assertFalse($condition, string $message = ''): void
    {
        static::assertThat($condition, static::isFalse(), $message);
    }

    /**
     * Asserts that a condition is not false.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert !false $condition
     */
    public static function assertNotFalse($condition, string $message = ''): void
    {
        static::assertThat($condition, static::logicalNot(static::isFalse()), $message);
    }

    /**
     * Asserts that a variable is null.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert null $actual
     */
    public static function assertNull($actual, string $message = ''): void
    {
        static::assertThat($actual, static::isNull(), $message);
    }

    /**
     * Asserts that a variable is not null.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert !null $actual
     */
    public static function assertNotNull($actual, string $message = ''): void
    {
        static::assertThat($actual, static::logicalNot(static::isNull()), $message);
    }

    /**
     * Asserts that a variable is finite.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertFinite($actual, string $message = ''): void
    {
        static::assertThat($actual, static::isFinite(), $message);
    }

    /**
     * Asserts that a variable is infinite.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertInfinite($actual, string $message = ''): void
    {
        static::assertThat($actual, static::isInfinite(), $message);
    }

    /**
     * Asserts that a variable is nan.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertNan($actual, string $message = ''): void
    {
        static::assertThat($actual, static::isNan(), $message);
    }

    /**
     * Asserts that a class has a specified attribute.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws ExpectationFailedException
     */
    public static function assertClassHasAttribute(string $attributeName, string $className, string $message = ''): void
    {
        if (!self::isValidClassAttributeName($attributeName)) {
            throw InvalidArgumentException::create(1, 'valid attribute name');
        }

        if (!class_exists($className)) {
            throw InvalidArgumentException::create(2, 'class name');
        }

        static::assertThat($className, new ClassHasAttribute($attributeName), $message);
    }

    /**
     * Asserts that a class does not have a specified attribute.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws ExpectationFailedException
     */
    public static function assertClassNotHasAttribute(string $attributeName, string $className, string $message = ''): void
    {
        if (!self::isValidClassAttributeName($attributeName)) {
            throw InvalidArgumentException::create(1, 'valid attribute name');
        }

        if (!class_exists($className)) {
            throw InvalidArgumentException::create(2, 'class name');
        }

        static::assertThat(
            $className,
            new LogicalNot(
                new ClassHasAttribute($attributeName)
            ),
            $message
        );
    }

    /**
     * Asserts that a class has a specified static attribute.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws ExpectationFailedException
     */
    public static function assertClassHasStaticAttribute(string $attributeName, string $className, string $message = ''): void
    {
        if (!self::isValidClassAttributeName($attributeName)) {
            throw InvalidArgumentException::create(1, 'valid attribute name');
        }

        if (!class_exists($className)) {
            throw InvalidArgumentException::create(2, 'class name');
        }

        static::assertThat(
            $className,
            new ClassHasStaticAttribute($attributeName),
            $message
        );
    }

    /**
     * Asserts that a class does not have a specified static attribute.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws ExpectationFailedException
     */
    public static function assertClassNotHasStaticAttribute(string $attributeName, string $className, string $message = ''): void
    {
        if (!self::isValidClassAttributeName($attributeName)) {
            throw InvalidArgumentException::create(1, 'valid attribute name');
        }

        if (!class_exists($className)) {
            throw InvalidArgumentException::create(2, 'class name');
        }

        static::assertThat(
            $className,
            new LogicalNot(
                new ClassHasStaticAttribute($attributeName)
            ),
            $message
        );
    }

    /**
     * Asserts that an object has a specified attribute.
     *
     * @param object $object
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws ExpectationFailedException
     */
    public static function assertObjectHasAttribute(string $attributeName, $object, string $message = ''): void
    {
        if (!self::isValidObjectAttributeName($attributeName)) {
            throw InvalidArgumentException::create(1, 'valid attribute name');
        }

        if (!is_object($object)) {
            throw InvalidArgumentException::create(2, 'object');
        }

        static::assertThat(
            $object,
            new ObjectHasAttribute($attributeName),
            $message
        );
    }

    /**
     * Asserts that an object does not have a specified attribute.
     *
     * @param object $object
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws ExpectationFailedException
     */
    public static function assertObjectNotHasAttribute(string $attributeName, $object, string $message = ''): void
    {
        if (!self::isValidObjectAttributeName($attributeName)) {
            throw InvalidArgumentException::create(1, 'valid attribute name');
        }

        if (!is_object($object)) {
            throw InvalidArgumentException::create(2, 'object');
        }

        static::assertThat(
            $object,
            new LogicalNot(
                new ObjectHasAttribute($attributeName)
            ),
            $message
        );
    }

    /**
     * Asserts that two variables have the same type and value.
     * Used on objects, it asserts that two variables reference
     * the same object.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-template ExpectedType
     * @psalm-param ExpectedType $expected
     * @psalm-assert =ExpectedType $actual
     */
    public static function assertSame($expected, $actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new IsIdentical($expected),
            $message
        );
    }

    /**
     * Asserts that two variables do not have the same type and value.
     * Used on objects, it asserts that two variables do not reference
     * the same object.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertNotSame($expected, $actual, string $message = ''): void
    {
        if (is_bool($expected) && is_bool($actual)) {
            static::assertNotEquals($expected, $actual, $message);
        }

        static::assertThat(
            $actual,
            new LogicalNot(
                new IsIdentical($expected)
            ),
            $message
        );
    }

    /**
     * Asserts that a variable is of a given type.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @psalm-template ExpectedType of object
     * @psalm-param class-string<ExpectedType> $expected
     * @psalm-assert =ExpectedType $actual
     */
    public static function assertInstanceOf(string $expected, $actual, string $message = ''): void
    {
        if (!class_exists($expected) && !interface_exists($expected)) {
            throw InvalidArgumentException::create(1, 'class or interface name');
        }

        static::assertThat(
            $actual,
            new IsInstanceOf($expected),
            $message
        );
    }

    /**
     * Asserts that a variable is not of a given type.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws ExpectationFailedException
     *
     * @psalm-template ExpectedType of object
     * @psalm-param class-string<ExpectedType> $expected
     * @psalm-assert !ExpectedType $actual
     */
    public static function assertNotInstanceOf(string $expected, $actual, string $message = ''): void
    {
        if (!class_exists($expected) && !interface_exists($expected)) {
            throw InvalidArgumentException::create(1, 'class or interface name');
        }

        static::assertThat(
            $actual,
            new LogicalNot(
                new IsInstanceOf($expected)
            ),
            $message
        );
    }

    /**
     * Asserts that a variable is of type array.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert array $actual
     */
    public static function assertIsArray($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new IsType(IsType::TYPE_ARRAY),
            $message
        );
    }

    /**
     * Asserts that a variable is of type bool.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert bool $actual
     */
    public static function assertIsBool($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new IsType(IsType::TYPE_BOOL),
            $message
        );
    }

    /**
     * Asserts that a variable is of type float.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert float $actual
     */
    public static function assertIsFloat($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new IsType(IsType::TYPE_FLOAT),
            $message
        );
    }

    /**
     * Asserts that a variable is of type int.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert int $actual
     */
    public static function assertIsInt($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new IsType(IsType::TYPE_INT),
            $message
        );
    }

    /**
     * Asserts that a variable is of type numeric.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert numeric $actual
     */
    public static function assertIsNumeric($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new IsType(IsType::TYPE_NUMERIC),
            $message
        );
    }

    /**
     * Asserts that a variable is of type object.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert object $actual
     */
    public static function assertIsObject($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new IsType(IsType::TYPE_OBJECT),
            $message
        );
    }

    /**
     * Asserts that a variable is of type resource.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert resource $actual
     */
    public static function assertIsResource($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new IsType(IsType::TYPE_RESOURCE),
            $message
        );
    }

    /**
     * Asserts that a variable is of type resource and is closed.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert resource $actual
     */
    public static function assertIsClosedResource($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new IsType(IsType::TYPE_CLOSED_RESOURCE),
            $message
        );
    }

    /**
     * Asserts that a variable is of type string.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert string $actual
     */
    public static function assertIsString($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new IsType(IsType::TYPE_STRING),
            $message
        );
    }

    /**
     * Asserts that a variable is of type scalar.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert scalar $actual
     */
    public static function assertIsScalar($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new IsType(IsType::TYPE_SCALAR),
            $message
        );
    }

    /**
     * Asserts that a variable is of type callable.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert callable $actual
     */
    public static function assertIsCallable($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new IsType(IsType::TYPE_CALLABLE),
            $message
        );
    }

    /**
     * Asserts that a variable is of type iterable.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert iterable $actual
     */
    public static function assertIsIterable($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new IsType(IsType::TYPE_ITERABLE),
            $message
        );
    }

    /**
     * Asserts that a variable is not of type array.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert !array $actual
     */
    public static function assertIsNotArray($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new LogicalNot(new IsType(IsType::TYPE_ARRAY)),
            $message
        );
    }

    /**
     * Asserts that a variable is not of type bool.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert !bool $actual
     */
    public static function assertIsNotBool($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new LogicalNot(new IsType(IsType::TYPE_BOOL)),
            $message
        );
    }

    /**
     * Asserts that a variable is not of type float.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert !float $actual
     */
    public static function assertIsNotFloat($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new LogicalNot(new IsType(IsType::TYPE_FLOAT)),
            $message
        );
    }

    /**
     * Asserts that a variable is not of type int.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert !int $actual
     */
    public static function assertIsNotInt($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new LogicalNot(new IsType(IsType::TYPE_INT)),
            $message
        );
    }

    /**
     * Asserts that a variable is not of type numeric.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert !numeric $actual
     */
    public static function assertIsNotNumeric($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new LogicalNot(new IsType(IsType::TYPE_NUMERIC)),
            $message
        );
    }

    /**
     * Asserts that a variable is not of type object.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert !object $actual
     */
    public static function assertIsNotObject($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new LogicalNot(new IsType(IsType::TYPE_OBJECT)),
            $message
        );
    }

    /**
     * Asserts that a variable is not of type resource.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert !resource $actual
     */
    public static function assertIsNotResource($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new LogicalNot(new IsType(IsType::TYPE_RESOURCE)),
            $message
        );
    }

    /**
     * Asserts that a variable is not of type resource.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert !resource $actual
     */
    public static function assertIsNotClosedResource($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new LogicalNot(new IsType(IsType::TYPE_CLOSED_RESOURCE)),
            $message
        );
    }

    /**
     * Asserts that a variable is not of type string.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert !string $actual
     */
    public static function assertIsNotString($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new LogicalNot(new IsType(IsType::TYPE_STRING)),
            $message
        );
    }

    /**
     * Asserts that a variable is not of type scalar.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert !scalar $actual
     */
    public static function assertIsNotScalar($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new LogicalNot(new IsType(IsType::TYPE_SCALAR)),
            $message
        );
    }

    /**
     * Asserts that a variable is not of type callable.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert !callable $actual
     */
    public static function assertIsNotCallable($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new LogicalNot(new IsType(IsType::TYPE_CALLABLE)),
            $message
        );
    }

    /**
     * Asserts that a variable is not of type iterable.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-assert !iterable $actual
     */
    public static function assertIsNotIterable($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new LogicalNot(new IsType(IsType::TYPE_ITERABLE)),
            $message
        );
    }

    /**
     * Asserts that a string matches a given regular expression.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertMatchesRegularExpression(string $pattern, string $string, string $message = ''): void
    {
        static::assertThat($string, new RegularExpression($pattern), $message);
    }

    /**
     * Asserts that a string matches a given regular expression.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @codeCoverageIgnore
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/4086
     */
    public static function assertRegExp(string $pattern, string $string, string $message = ''): void
    {
        self::createWarning('assertRegExp() is deprecated and will be removed in PHPUnit 10. Refactor your code to use assertMatchesRegularExpression() instead.');

        static::assertThat($string, new RegularExpression($pattern), $message);
    }

    /**
     * Asserts that a string does not match a given regular expression.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertDoesNotMatchRegularExpression(string $pattern, string $string, string $message = ''): void
    {
        static::assertThat(
            $string,
            new LogicalNot(
                new RegularExpression($pattern)
            ),
            $message
        );
    }

    /**
     * Asserts that a string does not match a given regular expression.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @codeCoverageIgnore
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/4089
     */
    public static function assertNotRegExp(string $pattern, string $string, string $message = ''): void
    {
        self::createWarning('assertNotRegExp() is deprecated and will be removed in PHPUnit 10. Refactor your code to use assertDoesNotMatchRegularExpression() instead.');

        static::assertThat(
            $string,
            new LogicalNot(
                new RegularExpression($pattern)
            ),
            $message
        );
    }

    /**
     * Assert that the size of two arrays (or `Countable` or `Traversable` objects)
     * is the same.
     *
     * @param Countable|iterable $expected
     * @param Countable|iterable $actual
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws ExpectationFailedException
     */
    public static function assertSameSize($expected, $actual, string $message = ''): void
    {
        if (!$expected instanceof Countable && !is_iterable($expected)) {
            throw InvalidArgumentException::create(1, 'countable or iterable');
        }

        if (!$actual instanceof Countable && !is_iterable($actual)) {
            throw InvalidArgumentException::create(2, 'countable or iterable');
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
     * @param Countable|iterable $expected
     * @param Countable|iterable $actual
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws ExpectationFailedException
     */
    public static function assertNotSameSize($expected, $actual, string $message = ''): void
    {
        if (!$expected instanceof Countable && !is_iterable($expected)) {
            throw InvalidArgumentException::create(1, 'countable or iterable');
        }

        if (!$actual instanceof Countable && !is_iterable($actual)) {
            throw InvalidArgumentException::create(2, 'countable or iterable');
        }

        static::assertThat(
            $actual,
            new LogicalNot(
                new SameSize($expected)
            ),
            $message
        );
    }

    /**
     * Asserts that a string matches a given format string.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertStringMatchesFormat(string $format, string $string, string $message = ''): void
    {
        static::assertThat($string, new StringMatchesFormatDescription($format), $message);
    }

    /**
     * Asserts that a string does not match a given format string.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertStringNotMatchesFormat(string $format, string $string, string $message = ''): void
    {
        static::assertThat(
            $string,
            new LogicalNot(
                new StringMatchesFormatDescription($format)
            ),
            $message
        );
    }

    /**
     * Asserts that a string matches a given format file.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertStringMatchesFormatFile(string $formatFile, string $string, string $message = ''): void
    {
        static::assertFileExists($formatFile, $message);

        static::assertThat(
            $string,
            new StringMatchesFormatDescription(
                file_get_contents($formatFile)
            ),
            $message
        );
    }

    /**
     * Asserts that a string does not match a given format string.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertStringNotMatchesFormatFile(string $formatFile, string $string, string $message = ''): void
    {
        static::assertFileExists($formatFile, $message);

        static::assertThat(
            $string,
            new LogicalNot(
                new StringMatchesFormatDescription(
                    file_get_contents($formatFile)
                )
            ),
            $message
        );
    }

    /**
     * Asserts that a string starts with a given prefix.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertStringStartsWith(string $prefix, string $string, string $message = ''): void
    {
        static::assertThat($string, new StringStartsWith($prefix), $message);
    }

    /**
     * Asserts that a string starts not with a given prefix.
     *
     * @param string $prefix
     * @param string $string
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertStringStartsNotWith($prefix, $string, string $message = ''): void
    {
        static::assertThat(
            $string,
            new LogicalNot(
                new StringStartsWith($prefix)
            ),
            $message
        );
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertStringContainsString(string $needle, string $haystack, string $message = ''): void
    {
        $constraint = new StringContains($needle, false);

        static::assertThat($haystack, $constraint, $message);
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertStringContainsStringIgnoringCase(string $needle, string $haystack, string $message = ''): void
    {
        $constraint = new StringContains($needle, true);

        static::assertThat($haystack, $constraint, $message);
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertStringNotContainsString(string $needle, string $haystack, string $message = ''): void
    {
        $constraint = new LogicalNot(new StringContains($needle));

        static::assertThat($haystack, $constraint, $message);
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertStringNotContainsStringIgnoringCase(string $needle, string $haystack, string $message = ''): void
    {
        $constraint = new LogicalNot(new StringContains($needle, true));

        static::assertThat($haystack, $constraint, $message);
    }

    /**
     * Asserts that a string ends with a given suffix.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertStringEndsWith(string $suffix, string $string, string $message = ''): void
    {
        static::assertThat($string, new StringEndsWith($suffix), $message);
    }

    /**
     * Asserts that a string ends not with a given suffix.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertStringEndsNotWith(string $suffix, string $string, string $message = ''): void
    {
        static::assertThat(
            $string,
            new LogicalNot(
                new StringEndsWith($suffix)
            ),
            $message
        );
    }

    /**
     * Asserts that two XML files are equal.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws ExpectationFailedException
     */
    public static function assertXmlFileEqualsXmlFile(string $expectedFile, string $actualFile, string $message = ''): void
    {
        $expected = (new XmlLoader)->loadFile($expectedFile);
        $actual   = (new XmlLoader)->loadFile($actualFile);

        static::assertEquals($expected, $actual, $message);
    }

    /**
     * Asserts that two XML files are not equal.
     *
     * @throws \PHPUnit\Util\Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertXmlFileNotEqualsXmlFile(string $expectedFile, string $actualFile, string $message = ''): void
    {
        $expected = (new XmlLoader)->loadFile($expectedFile);
        $actual   = (new XmlLoader)->loadFile($actualFile);

        static::assertNotEquals($expected, $actual, $message);
    }

    /**
     * Asserts that two XML documents are equal.
     *
     * @param DOMDocument|string $actualXml
     *
     * @throws \PHPUnit\Util\Xml\Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertXmlStringEqualsXmlFile(string $expectedFile, $actualXml, string $message = ''): void
    {
        if (!is_string($actualXml)) {
            self::createWarning('Passing an argument of type DOMDocument for the $actualXml parameter is deprecated. Support for this will be removed in PHPUnit 10.');

            $actual = $actualXml;
        } else {
            $actual = (new XmlLoader)->load($actualXml);
        }

        $expected = (new XmlLoader)->loadFile($expectedFile);

        static::assertEquals($expected, $actual, $message);
    }

    /**
     * Asserts that two XML documents are not equal.
     *
     * @param DOMDocument|string $actualXml
     *
     * @throws \PHPUnit\Util\Xml\Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertXmlStringNotEqualsXmlFile(string $expectedFile, $actualXml, string $message = ''): void
    {
        if (!is_string($actualXml)) {
            self::createWarning('Passing an argument of type DOMDocument for the $actualXml parameter is deprecated. Support for this will be removed in PHPUnit 10.');

            $actual = $actualXml;
        } else {
            $actual = (new XmlLoader)->load($actualXml);
        }

        $expected = (new XmlLoader)->loadFile($expectedFile);

        static::assertNotEquals($expected, $actual, $message);
    }

    /**
     * Asserts that two XML documents are equal.
     *
     * @param DOMDocument|string $expectedXml
     * @param DOMDocument|string $actualXml
     *
     * @throws \PHPUnit\Util\Xml\Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertXmlStringEqualsXmlString($expectedXml, $actualXml, string $message = ''): void
    {
        if (!is_string($expectedXml)) {
            self::createWarning('Passing an argument of type DOMDocument for the $expectedXml parameter is deprecated. Support for this will be removed in PHPUnit 10.');

            $expected = $expectedXml;
        } else {
            $expected = (new XmlLoader)->load($expectedXml);
        }

        if (!is_string($actualXml)) {
            self::createWarning('Passing an argument of type DOMDocument for the $actualXml parameter is deprecated. Support for this will be removed in PHPUnit 10.');

            $actual = $actualXml;
        } else {
            $actual = (new XmlLoader)->load($actualXml);
        }

        static::assertEquals($expected, $actual, $message);
    }

    /**
     * Asserts that two XML documents are not equal.
     *
     * @param DOMDocument|string $expectedXml
     * @param DOMDocument|string $actualXml
     *
     * @throws \PHPUnit\Util\Xml\Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertXmlStringNotEqualsXmlString($expectedXml, $actualXml, string $message = ''): void
    {
        if (!is_string($expectedXml)) {
            self::createWarning('Passing an argument of type DOMDocument for the $expectedXml parameter is deprecated. Support for this will be removed in PHPUnit 10.');

            $expected = $expectedXml;
        } else {
            $expected = (new XmlLoader)->load($expectedXml);
        }

        if (!is_string($actualXml)) {
            self::createWarning('Passing an argument of type DOMDocument for the $actualXml parameter is deprecated. Support for this will be removed in PHPUnit 10.');

            $actual = $actualXml;
        } else {
            $actual = (new XmlLoader)->load($actualXml);
        }

        static::assertNotEquals($expected, $actual, $message);
    }

    /**
     * Asserts that a hierarchy of DOMElements matches.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws AssertionFailedError
     * @throws ExpectationFailedException
     *
     * @codeCoverageIgnore
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/4091
     */
    public static function assertEqualXMLStructure(DOMElement $expectedElement, DOMElement $actualElement, bool $checkAttributes = false, string $message = ''): void
    {
        self::createWarning('assertEqualXMLStructure() is deprecated and will be removed in PHPUnit 10.');

        $expectedElement = Xml::import($expectedElement);
        $actualElement   = Xml::import($actualElement);

        static::assertSame(
            $expectedElement->tagName,
            $actualElement->tagName,
            $message
        );

        if ($checkAttributes) {
            static::assertSame(
                $expectedElement->attributes->length,
                $actualElement->attributes->length,
                sprintf(
                    '%s%sNumber of attributes on node "%s" does not match',
                    $message,
                    !empty($message) ? "\n" : '',
                    $expectedElement->tagName
                )
            );

            for ($i = 0; $i < $expectedElement->attributes->length; $i++) {
                $expectedAttribute = $expectedElement->attributes->item($i);
                $actualAttribute   = $actualElement->attributes->getNamedItem($expectedAttribute->name);

                assert($expectedAttribute instanceof DOMAttr);

                if (!$actualAttribute) {
                    static::fail(
                        sprintf(
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

        static::assertSame(
            $expectedElement->childNodes->length,
            $actualElement->childNodes->length,
            sprintf(
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
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertThat($value, Constraint $constraint, string $message = ''): void
    {
        self::$count += count($constraint);

        $constraint->evaluate($value, $message);
    }

    /**
     * Asserts that a string is a valid JSON string.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertJson(string $actualJson, string $message = ''): void
    {
        static::assertThat($actualJson, static::isJson(), $message);
    }

    /**
     * Asserts that two given JSON encoded objects or arrays are equal.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertJsonStringEqualsJsonString(string $expectedJson, string $actualJson, string $message = ''): void
    {
        static::assertJson($expectedJson, $message);
        static::assertJson($actualJson, $message);

        static::assertThat($actualJson, new JsonMatches($expectedJson), $message);
    }

    /**
     * Asserts that two given JSON encoded objects or arrays are not equal.
     *
     * @param string $expectedJson
     * @param string $actualJson
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertJsonStringNotEqualsJsonString($expectedJson, $actualJson, string $message = ''): void
    {
        static::assertJson($expectedJson, $message);
        static::assertJson($actualJson, $message);

        static::assertThat(
            $actualJson,
            new LogicalNot(
                new JsonMatches($expectedJson)
            ),
            $message
        );
    }

    /**
     * Asserts that the generated JSON encoded object and the content of the given file are equal.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertJsonStringEqualsJsonFile(string $expectedFile, string $actualJson, string $message = ''): void
    {
        static::assertFileExists($expectedFile, $message);
        $expectedJson = file_get_contents($expectedFile);

        static::assertJson($expectedJson, $message);
        static::assertJson($actualJson, $message);

        static::assertThat($actualJson, new JsonMatches($expectedJson), $message);
    }

    /**
     * Asserts that the generated JSON encoded object and the content of the given file are not equal.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertJsonStringNotEqualsJsonFile(string $expectedFile, string $actualJson, string $message = ''): void
    {
        static::assertFileExists($expectedFile, $message);
        $expectedJson = file_get_contents($expectedFile);

        static::assertJson($expectedJson, $message);
        static::assertJson($actualJson, $message);

        static::assertThat(
            $actualJson,
            new LogicalNot(
                new JsonMatches($expectedJson)
            ),
            $message
        );
    }

    /**
     * Asserts that two JSON files are equal.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertJsonFileEqualsJsonFile(string $expectedFile, string $actualFile, string $message = ''): void
    {
        static::assertFileExists($expectedFile, $message);
        static::assertFileExists($actualFile, $message);

        $actualJson   = file_get_contents($actualFile);
        $expectedJson = file_get_contents($expectedFile);

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
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertJsonFileNotEqualsJsonFile(string $expectedFile, string $actualFile, string $message = ''): void
    {
        static::assertFileExists($expectedFile, $message);
        static::assertFileExists($actualFile, $message);

        $actualJson   = file_get_contents($actualFile);
        $expectedJson = file_get_contents($expectedFile);

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
     * @throws Exception
     */
    public static function logicalAnd(): LogicalAnd
    {
        $constraints = func_get_args();

        $constraint = new LogicalAnd;
        $constraint->setConstraints($constraints);

        return $constraint;
    }

    public static function logicalOr(): LogicalOr
    {
        $constraints = func_get_args();

        $constraint = new LogicalOr;
        $constraint->setConstraints($constraints);

        return $constraint;
    }

    public static function logicalNot(Constraint $constraint): LogicalNot
    {
        return new LogicalNot($constraint);
    }

    public static function logicalXor(): LogicalXor
    {
        $constraints = func_get_args();

        $constraint = new LogicalXor;
        $constraint->setConstraints($constraints);

        return $constraint;
    }

    public static function anything(): IsAnything
    {
        return new IsAnything;
    }

    public static function isTrue(): IsTrue
    {
        return new IsTrue;
    }

    /**
     * @psalm-template CallbackInput of mixed
     *
     * @psalm-param callable(CallbackInput $callback): bool $callback
     *
     * @psalm-return Callback<CallbackInput>
     */
    public static function callback(callable $callback): Callback
    {
        return new Callback($callback);
    }

    public static function isFalse(): IsFalse
    {
        return new IsFalse;
    }

    public static function isJson(): IsJson
    {
        return new IsJson;
    }

    public static function isNull(): IsNull
    {
        return new IsNull;
    }

    public static function isFinite(): IsFinite
    {
        return new IsFinite;
    }

    public static function isInfinite(): IsInfinite
    {
        return new IsInfinite;
    }

    public static function isNan(): IsNan
    {
        return new IsNan;
    }

    public static function containsEqual($value): TraversableContainsEqual
    {
        return new TraversableContainsEqual($value);
    }

    public static function containsIdentical($value): TraversableContainsIdentical
    {
        return new TraversableContainsIdentical($value);
    }

    public static function containsOnly(string $type): TraversableContainsOnly
    {
        return new TraversableContainsOnly($type);
    }

    public static function containsOnlyInstancesOf(string $className): TraversableContainsOnly
    {
        return new TraversableContainsOnly($className, false);
    }

    /**
     * @param int|string $key
     */
    public static function arrayHasKey($key): ArrayHasKey
    {
        return new ArrayHasKey($key);
    }

    public static function equalTo($value): IsEqual
    {
        return new IsEqual($value, 0.0, false, false);
    }

    public static function equalToCanonicalizing($value): IsEqualCanonicalizing
    {
        return new IsEqualCanonicalizing($value);
    }

    public static function equalToIgnoringCase($value): IsEqualIgnoringCase
    {
        return new IsEqualIgnoringCase($value);
    }

    public static function equalToWithDelta($value, float $delta): IsEqualWithDelta
    {
        return new IsEqualWithDelta($value, $delta);
    }

    public static function isEmpty(): IsEmpty
    {
        return new IsEmpty;
    }

    public static function isWritable(): IsWritable
    {
        return new IsWritable;
    }

    public static function isReadable(): IsReadable
    {
        return new IsReadable;
    }

    public static function directoryExists(): DirectoryExists
    {
        return new DirectoryExists;
    }

    public static function fileExists(): FileExists
    {
        return new FileExists;
    }

    public static function greaterThan($value): GreaterThan
    {
        return new GreaterThan($value);
    }

    public static function greaterThanOrEqual($value): LogicalOr
    {
        return static::logicalOr(
            new IsEqual($value),
            new GreaterThan($value)
        );
    }

    public static function classHasAttribute(string $attributeName): ClassHasAttribute
    {
        return new ClassHasAttribute($attributeName);
    }

    public static function classHasStaticAttribute(string $attributeName): ClassHasStaticAttribute
    {
        return new ClassHasStaticAttribute($attributeName);
    }

    public static function objectHasAttribute($attributeName): ObjectHasAttribute
    {
        return new ObjectHasAttribute($attributeName);
    }

    public static function identicalTo($value): IsIdentical
    {
        return new IsIdentical($value);
    }

    public static function isInstanceOf(string $className): IsInstanceOf
    {
        return new IsInstanceOf($className);
    }

    public static function isType(string $type): IsType
    {
        return new IsType($type);
    }

    public static function lessThan($value): LessThan
    {
        return new LessThan($value);
    }

    public static function lessThanOrEqual($value): LogicalOr
    {
        return static::logicalOr(
            new IsEqual($value),
            new LessThan($value)
        );
    }

    public static function matchesRegularExpression(string $pattern): RegularExpression
    {
        return new RegularExpression($pattern);
    }

    public static function matches(string $string): StringMatchesFormatDescription
    {
        return new StringMatchesFormatDescription($string);
    }

    public static function stringStartsWith($prefix): StringStartsWith
    {
        return new StringStartsWith($prefix);
    }

    public static function stringContains(string $string, bool $case = true): StringContains
    {
        return new StringContains($string, $case);
    }

    public static function stringEndsWith(string $suffix): StringEndsWith
    {
        return new StringEndsWith($suffix);
    }

    public static function countOf(int $count): Count
    {
        return new Count($count);
    }

    public static function objectEquals(object $object, string $method = 'equals'): ObjectEquals
    {
        return new ObjectEquals($object, $method);
    }

    /**
     * Fails a test with the given message.
     *
     * @throws AssertionFailedError
     *
     * @psalm-return never-return
     */
    public static function fail(string $message = ''): void
    {
        self::$count++;

        throw new AssertionFailedError($message);
    }

    /**
     * Mark the test as incomplete.
     *
     * @throws IncompleteTestError
     *
     * @psalm-return never-return
     */
    public static function markTestIncomplete(string $message = ''): void
    {
        throw new IncompleteTestError($message);
    }

    /**
     * Mark the test as skipped.
     *
     * @throws SkippedTestError
     * @throws SyntheticSkippedError
     *
     * @psalm-return never-return
     */
    public static function markTestSkipped(string $message = ''): void
    {
        if ($hint = self::detectLocationHint($message)) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            array_unshift($trace, $hint);

            throw new SyntheticSkippedError($hint['message'], 0, $hint['file'], (int) $hint['line'], $trace);
        }

        throw new SkippedTestError($message);
    }

    /**
     * Return the current assertion count.
     */
    public static function getCount(): int
    {
        return self::$count;
    }

    /**
     * Reset the assertion counter.
     */
    public static function resetCount(): void
    {
        self::$count = 0;
    }

    private static function detectLocationHint(string $message): ?array
    {
        $hint  = null;
        $lines = preg_split('/\r\n|\r|\n/', $message);

        while (strpos($lines[0], '__OFFSET') !== false) {
            $offset = explode('=', array_shift($lines));

            if ($offset[0] === '__OFFSET_FILE') {
                $hint['file'] = $offset[1];
            }

            if ($offset[0] === '__OFFSET_LINE') {
                $hint['line'] = $offset[1];
            }
        }

        if ($hint) {
            $hint['message'] = implode(PHP_EOL, $lines);
        }

        return $hint;
    }

    private static function isValidObjectAttributeName(string $attributeName): bool
    {
        return (bool) preg_match('/[^\x00-\x1f\x7f-\x9f]+/', $attributeName);
    }

    private static function isValidClassAttributeName(string $attributeName): bool
    {
        return (bool) preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $attributeName);
    }

    /**
     * @codeCoverageIgnore
     */
    private static function createWarning(string $warning): void
    {
        foreach (debug_backtrace() as $step) {
            if (isset($step['object']) && $step['object'] instanceof TestCase) {
                assert($step['object'] instanceof TestCase);

                $step['object']->addWarning($warning);

                break;
            }
        }
    }
}
