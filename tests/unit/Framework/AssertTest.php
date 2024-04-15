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

use const DIRECTORY_SEPARATOR;
use const INF;
use const NAN;
use const PHP_OS_FAMILY;
use function acos;
use function array_merge;
use function chmod;
use function fclose;
use function file_get_contents;
use function fopen;
use function json_encode;
use function log;
use function mkdir;
use function octdec;
use function PHPUnit\TestFixture\Generator\f;
use function rmdir;
use function sys_get_temp_dir;
use function tempnam;
use function uniqid;
use function unlink;
use ArrayIterator;
use ArrayObject;
use DateTime;
use DateTimeZone;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\IgnorePhpunitDeprecations;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\TestFixture\Author;
use PHPUnit\TestFixture\Book;
use PHPUnit\TestFixture\ClassWithToString;
use PHPUnit\TestFixture\ObjectEquals\ValueObject;
use PHPUnit\TestFixture\SampleArrayAccess;
use PHPUnit\TestFixture\SampleClass;
use PHPUnit\TestFixture\Struct;
use PHPUnit\Util\Xml\Loader as XmlLoader;
use PHPUnit\Util\Xml\XmlException;
use SplObjectStorage;
use stdClass;

#[CoversClass(Assert::class)]
#[CoversClass(GeneratorNotSupportedException::class)]
#[Small]
final class AssertTest extends TestCase
{
    public static function validInvalidJsonProvider(): array
    {
        return [
            'error syntax in expected JSON' => ['{"Mascott"::}', '{"Mascott" : "Tux"}'],
            'error UTF-8 in actual JSON'    => ['{"Mascott" : "Tux"}', '{"Mascott" : :}'],
        ];
    }

    public static function equalProvider(): array
    {
        // same |= equal
        return array_merge(self::equalValues(), self::sameValues());
    }

    public static function notEqualProvider(): array
    {
        return self::notEqualValues();
    }

    public static function sameProvider(): array
    {
        return self::sameValues();
    }

    public static function notSameProvider(): array
    {
        // not equal |= not same
        // equal, ¬same |= not same
        return array_merge(self::notEqualValues(), self::equalValues());
    }

    public static function assertStringContainsStringIgnoringLineEndingsProvider(): array
    {
        return [
            ["b\nc", "b\r\nc"],
            ["b\nc", "a\r\nb\r\nc\r\nd"],
        ];
    }

    public static function assertStringEqualsStringIgnoringLineEndingsProvider(): array
    {
        return [
            'lf-crlf'   => ["a\nb", "a\r\nb"],
            'cr-crlf'   => ["a\rb", "a\r\nb"],
            'crlf-crlf' => ["a\r\nb", "a\r\nb"],
            'lf-cr'     => ["a\nb", "a\rb"],
            'cr-cr'     => ["a\rb", "a\rb"],
            'crlf-cr'   => ["a\r\nb", "a\rb"],
            'lf-lf'     => ["a\nb", "a\nb"],
            'cr-lf'     => ["a\rb", "a\nb"],
            'crlf-lf'   => ["a\r\nb", "a\nb"],
        ];
    }

    public static function assertStringEqualsStringIgnoringLineEndingsProviderNegative(): array
    {
        return [
            ["a\nb", 'ab'],
            ["a\rb", 'ab'],
            ["a\r\nb", 'ab'],
        ];
    }

    public function testFail(): void
    {
        try {
            $this->fail();
        } catch (AssertionFailedError) {
            return;
        }

        throw new AssertionFailedError('Fail did not throw fail exception');
    }

    public function testAssertContainsOnlyInstancesOf(): void
    {
        $test = [new Book, new Book];

        $this->assertContainsOnlyInstancesOf(Book::class, $test);
        $this->assertContainsOnlyInstancesOf(stdClass::class, [new stdClass]);

        $test2 = [new Author('Test')];

        $this->expectException(AssertionFailedError::class);

        $this->assertContainsOnlyInstancesOf(Book::class, $test2);
    }

    public function testAssertArrayIsEqualToArrayOnlyConsideringListOfKeys(): void
    {
        $expected = ['a' => 'b', 'b' => 'c', 0 => 1, 1 => 2];
        $actual   = ['a' => 'b', 'b' => 'b', 0 => 1, 1 => 3];

        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected, $actual, ['a', 0]);

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected, $actual, ['b']);
    }

    public function testAssertArrayIsEqualToArrayIgnoringListOfKeys(): void
    {
        $expected = ['a' => 'b', 'b' => 'c', 0 => 1, 1 => 2];
        $actual   = ['a' => 'b', 'b' => 'b', 0 => 1, 1 => 3];

        $this->assertArrayIsEqualToArrayIgnoringListOfKeys($expected, $actual, ['b', 1]);

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayIsEqualToArrayIgnoringListOfKeys($expected, $actual, ['b']);
    }

    public function testAssertArrayIsIdenticalToArrayOnlyConsideringListOfKeys(): void
    {
        $expected = ['a' => 'b', 'b' => 'c', 0 => 1, 1 => 2];
        $actual   = ['a' => 'b', 'b' => 'b', 0 => 1, 1 => 3];

        $this->assertArrayIsIdenticalToArrayOnlyConsideringListOfKeys($expected, $actual, ['a', 0]);

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayIsIdenticalToArrayOnlyConsideringListOfKeys($expected, $actual, ['b']);
    }

    public function testAssertArrayIsIdenticalToArrayIgnoringListOfKeys(): void
    {
        $expected = ['a' => 'b', 'b' => 'c', 0 => 1, 1 => 2];
        $actual   = ['a' => 'b', 'b' => 'b', 0 => 1, 1 => 3];

        $this->assertArrayIsIdenticalToArrayIgnoringListOfKeys($expected, $actual, ['b', 1]);

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayIsIdenticalToArrayIgnoringListOfKeys($expected, $actual, ['b']);
    }

    public function testAssertArrayIsEqualToArrayOnlyConsideringListOfKeysInterpretsKeysSameAsPHP(): void
    {
        // Effective keys: int 0, int 1, int 2, string '3.0'.
        $expected = [0 => 1, '1' => 2, 2.0 => 3, '3.0' => 4];
        $actual   = [0 => 1, '1' => 2, 2.0 => 2, '3.0' => 4];

        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected, $actual, [0, '1', '3.0']);

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected, $actual, ['1', 2.0, '3.0']);
    }

    public function testAssertArrayIsEqualToArrayIgnoringListOfKeysInterpretsKeysSameAsPHP(): void
    {
        // Effective keys: int 0, int 1, int 2, string '3.0'.
        $expected = [0 => 1, '1' => 2, 2.0 => 3, '3.0' => 4];
        $actual   = [0 => 1, '1' => 2, 2.0 => 2, '3.0' => 4];

        $this->assertArrayIsEqualToArrayIgnoringListOfKeys($expected, $actual, [2.0]);

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayIsEqualToArrayIgnoringListOfKeys($expected, $actual, ['1']);
    }

    public function testAssertArrayIsIdenticalToArrayOnlyConsideringListOfKeysInterpretsKeysSameAsPHP(): void
    {
        // Effective keys: int 0, int 1, int 2, string '3.0'.
        $expected = [0 => 1, '1' => 2, 2.0 => 3, '3.0' => 4];
        $actual   = [0 => 1, '1' => 2, 2.0 => 2, '3.0' => 4];

        $this->assertArrayIsIdenticalToArrayOnlyConsideringListOfKeys($expected, $actual, [0, '1', '3.0']);

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayIsIdenticalToArrayOnlyConsideringListOfKeys($expected, $actual, ['1', 2.0, '3.0']);
    }

    public function testAssertArrayIsIdenticalToArrayIgnoringListOfKeysInterpretsKeysSameAsPHP(): void
    {
        // Effective keys: int 0, int 1, int 2, string '3.0'.
        $expected = [0 => 1, '1' => 2, 2.0 => 3, '3.0' => 4];
        $actual   = [0 => 1, '1' => 2, 2.0 => 2, '3.0' => 4];

        $this->assertArrayIsIdenticalToArrayIgnoringListOfKeys($expected, $actual, [2.0]);

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayIsIdenticalToArrayIgnoringListOfKeys($expected, $actual, ['1']);
    }

    public function testAssertArrayIsEqualButNotIdenticalToArrayOnlyConsideringListOfKeys(): void
    {
        $expected = ['a' => 'b', 'b' => 'c', 0 => 1, 1 => 2];
        $actual   = [0 => 1, 1 => 3, 'a' => 'b', 'b' => 'b'];

        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected, $actual, ['a', 0]);

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayIsIdenticalToArrayOnlyConsideringListOfKeys($expected, $actual, ['a', 0]);
    }

    public function testAssertArrayIsEqualButNotIdenticalToArrayIgnoringListOfKeys(): void
    {
        $expected = ['a' => 'b', 'b' => 'c', 0 => 1, 1 => 2];
        $actual   = [0 => 1, 1 => 3, 'a' => 'b', 'b' => 'b'];

        $this->assertArrayIsEqualToArrayIgnoringListOfKeys($expected, $actual, ['b', 1]);

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayIsIdenticalToArrayIgnoringListOfKeys($expected, $actual, ['b', 1]);
    }

    public function testAssertArrayHasIntegerKey(): void
    {
        $this->assertArrayHasKey(0, ['foo']);

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayHasKey(1, ['foo']);
    }

    public function testAssertArrayNotHasIntegerKey(): void
    {
        $this->assertArrayNotHasKey(1, ['foo']);

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayNotHasKey(0, ['foo']);
    }

    public function testAssertArrayHasStringKey(): void
    {
        $this->assertArrayHasKey('foo', ['foo' => 'bar']);

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayHasKey('bar', ['foo' => 'bar']);
    }

    public function testAssertArrayNotHasStringKey(): void
    {
        $this->assertArrayNotHasKey('bar', ['foo' => 'bar']);

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayNotHasKey('foo', ['foo' => 'bar']);
    }

    public function testAssertArrayHasKeyAcceptsArrayObjectValue(): void
    {
        $array        = new ArrayObject;
        $array['foo'] = 'bar';

        $this->assertArrayHasKey('foo', $array);
    }

    public function testAssertArrayHasKeyProperlyFailsWithArrayObjectValue(): void
    {
        $array        = new ArrayObject;
        $array['bar'] = 'bar';

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayHasKey('foo', $array);
    }

    public function testAssertArrayHasKeyAcceptsArrayAccessValue(): void
    {
        $array        = new SampleArrayAccess;
        $array['foo'] = 'bar';

        $this->assertArrayHasKey('foo', $array);
    }

    public function testAssertArrayHasKeyProperlyFailsWithArrayAccessValue(): void
    {
        $array        = new SampleArrayAccess;
        $array['bar'] = 'bar';

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayHasKey('foo', $array);
    }

    public function testAssertArrayNotHasKeyAcceptsArrayAccessValue(): void
    {
        $array        = new ArrayObject;
        $array['foo'] = 'bar';

        $this->assertArrayNotHasKey('bar', $array);
    }

    public function testAssertArrayNotHasKeyProperlyFailsWithArrayAccessValue(): void
    {
        $array        = new ArrayObject;
        $array['bar'] = 'bar';

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayNotHasKey('bar', $array);
    }

    public function testAssertIsList(): void
    {
        $this->assertIsList([0, 1, 2]);

        $this->expectException(AssertionFailedError::class);

        $this->assertIsList([0 => 0, 2 => 2, 3 => 3]);
    }

    public function testAssertIsListWithEmptyArray(): void
    {
        $this->assertIsList([]);
    }

    public function testAssertIsListFailsWithStringKeys(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertIsList(['string' => 0]);
    }

    public function testAssertIsListFailsForTypesOtherThanArray(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertIsList(null);
    }

    public function testAssertArrayContainsOnlyIntegers(): void
    {
        $this->assertContainsOnly('integer', [1, 2, 3]);

        $this->expectException(AssertionFailedError::class);

        $this->assertContainsOnly('integer', ['1', 2, 3]);
    }

    public function testAssertArrayNotContainsOnlyIntegers(): void
    {
        $this->assertNotContainsOnly('integer', ['1', 2, 3]);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotContainsOnly('integer', [1, 2, 3]);
    }

    public function testAssertArrayContainsOnlyStdClass(): void
    {
        $this->assertContainsOnly(stdClass::class, [new stdClass]);

        $this->expectException(AssertionFailedError::class);

        $this->assertContainsOnly(stdClass::class, [stdClass::class]);
    }

    public function testAssertArrayNotContainsOnlyStdClass(): void
    {
        $this->assertNotContainsOnly(stdClass::class, [stdClass::class]);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotContainsOnly(stdClass::class, [new stdClass]);
    }

    #[DataProvider('equalProvider')]
    public function testAssertEqualsSucceeds(mixed $a, mixed $b): void
    {
        $this->assertEquals($a, $b);
    }

    #[DataProvider('notEqualProvider')]
    public function testAssertEqualsFails(mixed $a, mixed $b): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertEquals($a, $b);
    }

    #[DataProvider('notEqualProvider')]
    public function testAssertNotEqualsSucceeds(mixed $a, mixed $b): void
    {
        $this->assertNotEquals($a, $b);
    }

    #[DataProvider('equalProvider')]
    public function testAssertNotEqualsFails(mixed $a, mixed $b): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertNotEquals($a, $b);
    }

    #[DataProvider('sameProvider')]
    public function testAssertSameSucceeds(mixed $a, mixed $b): void
    {
        $this->assertSame($a, $b);
    }

    #[DataProvider('notSameProvider')]
    public function testAssertSameFails(mixed $a, mixed $b): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertSame($a, $b);
    }

    #[DataProvider('notSameProvider')]
    public function testAssertNotSameSucceeds(mixed $a, mixed $b): void
    {
        $this->assertNotSame($a, $b);
    }

    #[DataProvider('sameProvider')]
    public function testAssertNotSameFails(mixed $a, mixed $b): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertNotSame($a, $b);
    }

    public function testAssertXmlFileEqualsXmlFile(): void
    {
        $this->assertXmlFileEqualsXmlFile(
            TEST_FILES_PATH . 'foo.xml',
            TEST_FILES_PATH . 'foo.xml',
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertXmlFileEqualsXmlFile(
            TEST_FILES_PATH . 'foo.xml',
            TEST_FILES_PATH . 'bar.xml',
        );
    }

    public function testAssertXmlFileNotEqualsXmlFile(): void
    {
        $this->assertXmlFileNotEqualsXmlFile(
            TEST_FILES_PATH . 'foo.xml',
            TEST_FILES_PATH . 'bar.xml',
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertXmlFileNotEqualsXmlFile(
            TEST_FILES_PATH . 'foo.xml',
            TEST_FILES_PATH . 'foo.xml',
        );
    }

    public function testAssertXmlStringEqualsXmlFile(): void
    {
        $this->assertXmlStringEqualsXmlFile(
            TEST_FILES_PATH . 'foo.xml',
            file_get_contents(TEST_FILES_PATH . 'foo.xml'),
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertXmlStringEqualsXmlFile(
            TEST_FILES_PATH . 'foo.xml',
            file_get_contents(TEST_FILES_PATH . 'bar.xml'),
        );
    }

    public function testXmlStringNotEqualsXmlFile(): void
    {
        $this->assertXmlStringNotEqualsXmlFile(
            TEST_FILES_PATH . 'foo.xml',
            file_get_contents(TEST_FILES_PATH . 'bar.xml'),
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertXmlStringNotEqualsXmlFile(
            TEST_FILES_PATH . 'foo.xml',
            file_get_contents(TEST_FILES_PATH . 'foo.xml'),
        );
    }

    public function testAssertXmlStringEqualsXmlString(): void
    {
        $this->assertXmlStringEqualsXmlString('<root/>', '<root/>');

        $this->expectException(AssertionFailedError::class);

        $this->assertXmlStringEqualsXmlString('<foo/>', '<bar/>');
    }

    public function testAssertXmlStringEqualsXmlString2(): void
    {
        $this->expectException(XmlException::class);

        $this->assertXmlStringEqualsXmlString('<a></b>', '<c></d>');
    }

    public function testAssertXmlStringEqualsXmlString3(): void
    {
        $expected = <<<'XML'
<?xml version="1.0"?>
<root>
    <node />
</root>
XML;

        $actual = <<<'XML'
<?xml version="1.0"?>
<root>
<node />
</root>
XML;

        $this->assertXmlStringEqualsXmlString($expected, $actual);
    }

    public function testAssertXmlStringNotEqualsXmlString(): void
    {
        $this->assertXmlStringNotEqualsXmlString('<foo/>', '<bar/>');

        $this->expectException(AssertionFailedError::class);

        $this->assertXmlStringNotEqualsXmlString('<root/>', '<root/>');
    }

    public function testAssertStringEqualsNumeric(): void
    {
        $this->assertEquals('0', 0);

        $this->expectException(AssertionFailedError::class);

        $this->assertEquals('0', 1);
    }

    public function testAssertStringEqualsNumeric2(): void
    {
        $this->assertNotEquals('A', 0);
    }

    public function testAssertIsReadable(): void
    {
        $this->assertIsReadable(__FILE__);

        $this->expectException(AssertionFailedError::class);

        $this->assertIsReadable(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');
    }

    public function testAssertIsNotReadable(): void
    {
        $this->assertIsNotReadable(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');

        $this->expectException(AssertionFailedError::class);

        $this->assertIsNotReadable(__FILE__);
    }

    public function testAssertIsWritable(): void
    {
        $this->assertIsWritable(__FILE__);

        $this->expectException(AssertionFailedError::class);

        $this->assertIsWritable(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');
    }

    public function testAssertNotIsWritable(): void
    {
        $this->assertIsNotWritable(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');

        $this->expectException(AssertionFailedError::class);

        $this->assertIsNotWritable(__FILE__);
    }

    public function testAssertDirectoryExists(): void
    {
        $this->assertDirectoryExists(__DIR__);

        $this->expectException(AssertionFailedError::class);

        $this->assertDirectoryExists(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');
    }

    public function testAssertDirectoryNotExists(): void
    {
        $this->assertDirectoryDoesNotExist(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');

        $this->expectException(AssertionFailedError::class);

        $this->assertDirectoryDoesNotExist(__DIR__);
    }

    public function testAssertDirectoryIsReadable(): void
    {
        $this->assertDirectoryIsReadable(__DIR__);

        $this->expectException(AssertionFailedError::class);

        $this->assertDirectoryIsReadable(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');
    }

    public function testAssertDirectoryIsNotReadable(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Cannot test this behaviour on Windows');
        }

        $dirName = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('unreadable_dir_', true);
        mkdir($dirName, octdec('0'));

        $this->assertDirectoryIsNotReadable($dirName);

        chmod($dirName, octdec('444'));

        try {
            $this->assertDirectoryIsNotReadable($dirName);
        } catch (AssertionFailedError) {
        }

        rmdir($dirName);
    }

    public function testAssertDirectoryIsWritable(): void
    {
        $this->assertDirectoryIsWritable(__DIR__);

        $this->expectException(AssertionFailedError::class);

        $this->assertDirectoryIsWritable(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');
    }

    public function testAssertDirectoryIsNotWritable(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Cannot test this behaviour on Windows');
        }

        $dirName = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('not_writable_dir_', true);
        mkdir($dirName, octdec('444'));

        $this->assertDirectoryIsNotWritable($dirName);

        chmod($dirName, octdec('755'));

        try {
            $this->assertDirectoryIsNotWritable($dirName);
        } catch (AssertionFailedError) {
        }

        rmdir($dirName);
    }

    public function testAssertFileExists(): void
    {
        $this->assertFileExists(__FILE__);

        $this->expectException(AssertionFailedError::class);

        $this->assertFileExists(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');
    }

    public function testAssertFileNotExists(): void
    {
        $this->assertFileDoesNotExist(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');

        $this->expectException(AssertionFailedError::class);

        $this->assertFileDoesNotExist(__FILE__);
    }

    public function testAssertFileIsReadable(): void
    {
        $this->assertFileIsReadable(__FILE__);

        $this->expectException(AssertionFailedError::class);

        $this->assertFileIsReadable(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');
    }

    public function testAssertFileIsNotReadable(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Cannot test this behaviour on Windows');
        }

        $tempFile = tempnam(
            sys_get_temp_dir(),
            'unreadable',
        );

        chmod($tempFile, octdec('0'));

        $this->assertFileIsNotReadable($tempFile);

        chmod($tempFile, octdec('755'));

        try {
            $this->assertFileIsNotReadable($tempFile);
        } catch (AssertionFailedError) {
        }

        unlink($tempFile);
    }

    public function testAssertFileIsNotWritable(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'not_writable');

        chmod($tempFile, octdec('0'));

        $this->assertFileIsNotWritable($tempFile);

        chmod($tempFile, octdec('755'));

        try {
            $this->assertFileIsNotWritable($tempFile);
        } catch (AssertionFailedError) {
        }

        unlink($tempFile);
    }

    public function testAssertFileIsWritable(): void
    {
        $this->assertFileIsWritable(__FILE__);

        $this->expectException(AssertionFailedError::class);

        $this->assertFileIsWritable(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');
    }

    public function testAssertFinite(): void
    {
        $this->assertFinite(1);

        $this->expectException(AssertionFailedError::class);

        $this->assertFinite(INF);
    }

    public function testAssertInfinite(): void
    {
        $this->assertInfinite(INF);

        $this->expectException(AssertionFailedError::class);

        $this->assertInfinite(1);
    }

    public function testAssertNan(): void
    {
        $this->assertNan(NAN);

        $this->expectException(AssertionFailedError::class);

        $this->assertNan(1);
    }

    public function testAssertNull(): void
    {
        $this->assertNull(null);

        $this->expectException(AssertionFailedError::class);

        $this->assertNull(new stdClass);
    }

    public function testAssertNotNull(): void
    {
        $this->assertNotNull(new stdClass);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotNull(null);
    }

    public function testAssertTrue(): void
    {
        $this->assertTrue(true);

        $this->expectException(AssertionFailedError::class);

        /* @noinspection PhpUnitAssertCanBeReplacedWithFailInspection */
        /* @noinspection PhpUnitAssertTrueWithIncompatibleTypeArgumentInspection */
        $this->assertTrue(false);
    }

    public function testAssertNotTrue(): void
    {
        $this->assertNotTrue(false);
        $this->assertNotTrue(1);
        $this->assertNotTrue('true');

        $this->expectException(AssertionFailedError::class);

        $this->assertNotTrue(true);
    }

    public function testAssertFalse(): void
    {
        $this->assertFalse(false);

        $this->expectException(AssertionFailedError::class);

        /* @noinspection PhpUnitAssertCanBeReplacedWithFailInspection */
        $this->assertFalse(true);
    }

    public function testAssertNotFalse(): void
    {
        $this->assertNotFalse(true);
        $this->assertNotFalse(0);
        $this->assertNotFalse('');

        $this->expectException(AssertionFailedError::class);

        $this->assertNotFalse(false);
    }

    public function testAssertMatchesRegularExpression(): void
    {
        $this->assertMatchesRegularExpression('/foo/', 'foobar');

        $this->expectException(AssertionFailedError::class);

        $this->assertMatchesRegularExpression('/foo/', 'bar');
    }

    public function testAssertDoesNotMatchRegularExpression(): void
    {
        $this->assertDoesNotMatchRegularExpression('/foo/', 'bar');

        $this->expectException(AssertionFailedError::class);

        $this->assertDoesNotMatchRegularExpression('/foo/', 'foobar');
    }

    public function testAssertSame(): void
    {
        $o = new stdClass;

        $this->assertSame($o, $o);

        $this->expectException(AssertionFailedError::class);

        $this->assertSame(new stdClass, new stdClass);
    }

    public function testAssertSame2(): void
    {
        $this->assertSame(true, true);
        $this->assertSame(false, false);

        $this->expectException(AssertionFailedError::class);

        $this->assertSame(true, false);
    }

    public function testAssertNotSame(): void
    {
        $this->assertNotSame(
            new stdClass,
            null,
        );

        $this->assertNotSame(
            null,
            new stdClass,
        );

        $this->assertNotSame(
            new stdClass,
            new stdClass,
        );

        $o = new stdClass;

        $this->expectException(AssertionFailedError::class);

        $this->assertNotSame($o, $o);
    }

    public function testAssertNotSame2(): void
    {
        $this->assertNotSame(true, false);
        $this->assertNotSame(false, true);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotSame(true, true);
    }

    public function testAssertNotSameFailsNull(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertNotSame(null, null);
    }

    public function testGreaterThan(): void
    {
        $this->assertGreaterThan(1, 2);

        $this->expectException(AssertionFailedError::class);

        $this->assertGreaterThan(2, 1);
    }

    public function testGreaterThanOrEqual(): void
    {
        $this->assertGreaterThanOrEqual(1, 2);

        $this->expectException(AssertionFailedError::class);

        $this->assertGreaterThanOrEqual(2, 1);
    }

    public function testLessThan(): void
    {
        $this->assertLessThan(2, 1);

        try {
            $this->assertLessThan(1, 2);
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testLessThanOrEqual(): void
    {
        $this->assertLessThanOrEqual(2, 1);

        $this->expectException(AssertionFailedError::class);

        $this->assertLessThanOrEqual(1, 2);
    }

    #[DoesNotPerformAssertions]
    public function testAssertThatAnything(): void
    {
        $this->assertThat('anything', $this->anything());
    }

    public function testAssertThatIsTrue(): void
    {
        $this->assertThat(true, $this->isTrue());
    }

    public function testAssertThatIsFalse(): void
    {
        $this->assertThat(false, $this->isFalse());
    }

    public function testAssertThatIsJson(): void
    {
        $this->assertThat('{}', $this->isJson());
    }

    #[DoesNotPerformAssertions]
    public function testAssertThatAnythingAndAnything(): void
    {
        $this->assertThat(
            'anything',
            $this->logicalAnd(
                $this->anything(),
                $this->anything(),
            ),
        );
    }

    #[DoesNotPerformAssertions]
    public function testAssertThatAnythingOrAnything(): void
    {
        $this->assertThat(
            'anything',
            $this->logicalOr(
                $this->anything(),
                $this->anything(),
            ),
        );
    }

    #[DoesNotPerformAssertions]
    public function testAssertThatAnythingXorNotAnything(): void
    {
        $this->assertThat(
            'anything',
            $this->logicalXor(
                $this->anything(),
                $this->logicalNot($this->anything()),
            ),
        );
    }

    public function testAssertThatContains(): void
    {
        $this->assertThat(['foo'], $this->containsIdentical('foo'));
    }

    public function testAssertThatStringContains(): void
    {
        $this->assertThat('barfoobar', $this->stringContains('foo'));
    }

    public function testAssertThatContainsOnly(): void
    {
        $this->assertThat(['foo'], $this->containsOnly('string'));
    }

    public function testAssertThatContainsOnlyInstancesOf(): void
    {
        $this->assertThat([new Book], $this->containsOnlyInstancesOf(Book::class));
    }

    public function testAssertThatArrayHasKey(): void
    {
        $this->assertThat(['foo' => 'bar'], $this->arrayHasKey('foo'));
    }

    public function testAssertThatArrayIsList(): void
    {
        $this->assertThat([0, 1, 2], $this->isList());
    }

    public function testAssertThatEqualTo(): void
    {
        $this->assertThat('foo', $this->equalTo('foo'));
    }

    public function testAssertThatIdenticalTo(): void
    {
        $value      = new stdClass;
        $constraint = $this->identicalTo($value);

        $this->assertThat($value, $constraint);
    }

    public function testAssertThatIsInstanceOf(): void
    {
        $this->assertThat(new stdClass, $this->isInstanceOf(stdClass::class));
    }

    public function testAssertThatIsType(): void
    {
        $this->assertThat('string', $this->isType('string'));
    }

    public function testAssertThatIsEmpty(): void
    {
        $this->assertThat([], $this->isEmpty());
    }

    public function testAssertThatFileExists(): void
    {
        $this->assertThat(__FILE__, $this->fileExists());
    }

    public function testAssertThatGreaterThan(): void
    {
        $this->assertThat(2, $this->greaterThan(1));
    }

    public function testAssertThatGreaterThanOrEqual(): void
    {
        $this->assertThat(2, $this->greaterThanOrEqual(1));
    }

    public function testAssertThatLessThan(): void
    {
        $this->assertThat(1, $this->lessThan(2));
    }

    public function testAssertThatLessThanOrEqual(): void
    {
        $this->assertThat(1, $this->lessThanOrEqual(2));
    }

    public function testAssertThatMatchesRegularExpression(): void
    {
        $this->assertThat('foobar', $this->matchesRegularExpression('/foo/'));
    }

    public function testAssertThatCallback(): void
    {
        $this->assertThat(
            null,
            $this->callback(static fn ($other) => true),
        );
    }

    public function testAssertThatCountOf(): void
    {
        $this->assertThat([1], $this->countOf(1));
    }

    public function testAssertFileEquals(): void
    {
        $this->assertFileEquals(
            TEST_FILES_PATH . 'foo.xml',
            TEST_FILES_PATH . 'foo.xml',
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertFileEquals(
            TEST_FILES_PATH . 'foo.xml',
            TEST_FILES_PATH . 'bar.xml',
        );
    }

    public function testAssertFileNotEquals(): void
    {
        $this->assertFileNotEquals(
            TEST_FILES_PATH . 'foo.xml',
            TEST_FILES_PATH . 'bar.xml',
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertFileNotEquals(
            TEST_FILES_PATH . 'foo.xml',
            TEST_FILES_PATH . 'foo.xml',
        );
    }

    public function testAssertStringEqualsFile(): void
    {
        $this->assertStringEqualsFile(
            TEST_FILES_PATH . 'foo.xml',
            file_get_contents(TEST_FILES_PATH . 'foo.xml'),
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertStringEqualsFile(
            TEST_FILES_PATH . 'foo.xml',
            file_get_contents(TEST_FILES_PATH . 'bar.xml'),
        );
    }

    public function testAssertStringEqualsFileIgnoringCase(): void
    {
        $this->assertStringEqualsFileIgnoringCase(
            TEST_FILES_PATH . 'foo.xml',
            file_get_contents(TEST_FILES_PATH . 'fooUppercase.xml'),
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertStringEqualsFileIgnoringCase(
            TEST_FILES_PATH . 'foo.xml',
            file_get_contents(TEST_FILES_PATH . 'bar.xml'),
        );
    }

    public function testAssertStringNotEqualsFile(): void
    {
        $this->assertStringNotEqualsFile(
            TEST_FILES_PATH . 'foo.xml',
            file_get_contents(TEST_FILES_PATH . 'bar.xml'),
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertStringNotEqualsFile(
            TEST_FILES_PATH . 'foo.xml',
            file_get_contents(TEST_FILES_PATH . 'foo.xml'),
        );
    }

    public function testAssertStringNotEqualsFileIgnoringCase(): void
    {
        $this->assertStringNotEqualsFileIgnoringCase(
            TEST_FILES_PATH . 'foo.xml',
            file_get_contents(TEST_FILES_PATH . 'bar.xml'),
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertStringNotEqualsFileIgnoringCase(
            TEST_FILES_PATH . 'foo.xml',
            file_get_contents(TEST_FILES_PATH . 'fooUppercase.xml'),
        );
    }

    public function testAssertFileEqualsIgnoringCase(): void
    {
        $this->assertFileEqualsIgnoringCase(
            TEST_FILES_PATH . 'foo.xml',
            TEST_FILES_PATH . 'fooUppercase.xml',
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertFileEqualsIgnoringCase(
            TEST_FILES_PATH . 'foo.xml',
            TEST_FILES_PATH . 'bar.xml',
        );
    }

    public function testAssertFileNotEqualsIgnoringCase(): void
    {
        $this->assertFileNotEqualsIgnoringCase(
            TEST_FILES_PATH . 'foo.xml',
            TEST_FILES_PATH . 'bar.xml',
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertFileNotEqualsIgnoringCase(
            TEST_FILES_PATH . 'foo.xml',
            TEST_FILES_PATH . 'fooUppercase.xml',
        );
    }

    public function testAssertStringStartsWith(): void
    {
        $this->assertStringStartsWith('prefix', 'prefixfoo');

        $this->expectException(AssertionFailedError::class);

        $this->assertStringStartsWith('prefix', 'foo');
    }

    public function testAssertStringStartsNotWith(): void
    {
        $this->assertStringStartsNotWith('prefix', 'foo');

        $this->expectException(AssertionFailedError::class);

        $this->assertStringStartsNotWith('prefix', 'prefixfoo');
    }

    public function testAssertStringEndsWith(): void
    {
        $this->assertStringEndsWith('suffix', 'foosuffix');

        $this->expectException(AssertionFailedError::class);

        $this->assertStringEndsWith('suffix', 'foo');
    }

    public function testAssertStringEndsNotWith(): void
    {
        $this->assertStringEndsNotWith('suffix', 'foo');

        $this->expectException(AssertionFailedError::class);

        $this->assertStringEndsNotWith('suffix', 'foosuffix');
    }

    #[DataProvider('assertStringContainsStringIgnoringLineEndingsProvider')]
    public function testAssertStringContainsStringIgnoringLineEndings(string $needle, string $haystack): void
    {
        $this->assertStringContainsStringIgnoringLineEndings($needle, $haystack);
    }

    public function testNotAssertStringContainsStringIgnoringLineEndings(): void
    {
        $this->expectException(ExpectationFailedException::class);

        $this->assertStringContainsStringIgnoringLineEndings("b\nc", "\r\nc\r\n");
    }

    #[DataProvider('assertStringEqualsStringIgnoringLineEndingsProvider')]
    public function testAssertStringEqualsStringIgnoringLineEndings(string $expected, string $actual): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings($expected, $actual);
    }

    #[DataProvider('assertStringEqualsStringIgnoringLineEndingsProviderNegative')]
    public function testAssertStringEqualsStringIgnoringLineEndingsNegative(string $expected, string $actual): void
    {
        $this->expectException(ExpectationFailedException::class);

        $this->assertStringEqualsStringIgnoringLineEndings($expected, $actual);
    }

    public function testAssertStringMatchesFormat(): void
    {
        $this->assertStringMatchesFormat('*%s*', '***');
    }

    public function testAssertStringMatchesFormatFailure(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertStringMatchesFormat('*%s*', '**');
    }

    #[IgnorePhpunitDeprecations]
    public function testAssertStringNotMatchesFormat(): void
    {
        $this->assertStringNotMatchesFormat('*%s*', '**');

        $this->expectException(AssertionFailedError::class);

        $this->assertStringMatchesFormat('*%s*', '**');
    }

    public function testAssertEmpty(): void
    {
        $this->assertEmpty([]);

        $this->expectException(AssertionFailedError::class);

        $this->assertEmpty(['foo']);
    }

    public function testAssertEmptyGenerator(): void
    {
        $generator = f();

        $this->expectException(GeneratorNotSupportedException::class);
        $this->expectExceptionMessage('Passing an argument of type Generator for the $actual parameter is not supported');

        $this->assertEmpty($generator);
    }

    public function testAssertNotEmpty(): void
    {
        $this->assertNotEmpty(['foo']);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotEmpty([]);
    }

    public function testAssertNotEmptyGenerator(): void
    {
        $generator = f();

        $this->expectException(GeneratorNotSupportedException::class);
        $this->expectExceptionMessage('Passing an argument of type Generator for the $actual parameter is not supported');

        $this->assertNotEmpty($generator);
    }

    public function testMarkTestIncomplete(): void
    {
        try {
            $this->markTestIncomplete('incomplete');
        } catch (IncompleteTestError $e) {
            $this->assertEquals('incomplete', $e->getMessage());

            return;
        }

        $this->fail();
    }

    public function testMarkTestSkipped(): void
    {
        try {
            $this->markTestSkipped('skipped');
        } catch (SkippedTest $e) {
            $this->assertEquals('skipped', $e->getMessage());

            return;
        }

        $this->fail();
    }

    public function testAssertCount(): void
    {
        $this->assertCount(2, [1, 2]);

        $this->expectException(AssertionFailedError::class);

        $this->assertCount(2, [1, 2, 3]);
    }

    public function testAssertCountGenerator(): void
    {
        $generator = f();

        $this->expectException(GeneratorNotSupportedException::class);
        $this->expectExceptionMessage('Passing an argument of type Generator for the $haystack parameter is not supported');

        $this->assertCount(0, $generator);
    }

    public function testAssertCountTraversable(): void
    {
        $this->assertCount(2, new ArrayIterator([1, 2]));

        $this->expectException(AssertionFailedError::class);

        $this->assertCount(2, new ArrayIterator([1, 2, 3]));
    }

    public function testAssertNotCount(): void
    {
        $this->assertNotCount(2, [1, 2, 3]);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotCount(2, [1, 2]);
    }

    public function testAssertNotCountGenerator(): void
    {
        $generator = f();

        $this->expectException(GeneratorNotSupportedException::class);
        $this->expectExceptionMessage('Passing an argument of type Generator for the $haystack parameter is not supported');

        $this->assertNotCount(0, $generator);
    }

    public function testAssertSameSize(): void
    {
        $this->assertSameSize([1, 2], [3, 4]);

        $this->expectException(AssertionFailedError::class);

        $this->assertSameSize([1, 2], [1, 2, 3]);
    }

    public function testAssertSameSizeGenerator(): void
    {
        $generator = f();

        $this->expectException(GeneratorNotSupportedException::class);
        $this->expectExceptionMessage('Passing an argument of type Generator for the $expected parameter is not supported');

        $this->assertSameSize($generator, []);
    }

    public function testAssertSameSizeGenerator2(): void
    {
        $generator = f();

        $this->expectException(GeneratorNotSupportedException::class);
        $this->expectExceptionMessage('Passing an argument of type Generator for the $actual parameter is not supported');

        $this->assertSameSize([], $generator);
    }

    public function testAssertNotSameSize(): void
    {
        $this->assertNotSameSize([1, 2], [1, 2, 3]);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotSameSize([1, 2], [3, 4]);
    }

    public function testAssertNotSameSizeGenerator(): void
    {
        $generator = f();

        $this->expectException(GeneratorNotSupportedException::class);
        $this->expectExceptionMessage('Passing an argument of type Generator for the $expected parameter is not supported');

        $this->assertNotSameSize($generator, []);
    }

    public function testAssertNotSameSizeGenerator2(): void
    {
        $generator = f();

        $this->expectException(GeneratorNotSupportedException::class);
        $this->expectExceptionMessage('Passing an argument of type Generator for the $actual parameter is not supported');

        $this->assertNotSameSize([], $generator);
    }

    public function testAssertJson(): void
    {
        $this->assertJson('{}');
    }

    public function testAssertJsonStringEqualsJsonString(): void
    {
        $expected = '{"Mascott" : "Tux"}';
        $actual   = '{"Mascott" : "Tux"}';
        $message  = 'Given Json strings do not match';

        $this->assertJsonStringEqualsJsonString($expected, $actual, $message);
    }

    #[DataProvider('validInvalidJsonProvider')]
    public function testAssertJsonStringEqualsJsonStringErrorRaised(string $expected, string $actual): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function testAssertJsonStringNotEqualsJsonString(): void
    {
        $expected = '{"Mascott" : "Beastie"}';
        $actual   = '{"Mascott" : "Tux"}';
        $message  = 'Given Json strings do match';

        $this->assertJsonStringNotEqualsJsonString($expected, $actual, $message);
    }

    #[DataProvider('validInvalidJsonProvider')]
    public function testAssertJsonStringNotEqualsJsonStringErrorRaised(string $expected, string $actual): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertJsonStringNotEqualsJsonString($expected, $actual);
    }

    public function testAssertJsonStringEqualsJsonFile(): void
    {
        $file    = TEST_FILES_PATH . 'JsonData/simpleObject.json';
        $actual  = json_encode(['Mascott' => 'Tux']);
        $message = '';

        $this->assertJsonStringEqualsJsonFile($file, $actual, $message);
    }

    public function testAssertJsonStringEqualsJsonFileExpectingExpectationFailedException(): void
    {
        $file    = TEST_FILES_PATH . 'JsonData/simpleObject.json';
        $actual  = json_encode(['Mascott' => 'Beastie']);
        $message = '';

        try {
            $this->assertJsonStringEqualsJsonFile($file, $actual, $message);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                'Failed asserting that \'{"Mascott":"Beastie"}\' matches JSON string "{"Mascott":"Tux"}".',
                $e->getMessage(),
            );

            return;
        }

        $this->fail('Expected Exception not thrown.');
    }

    public function testAssertJsonStringNotEqualsJsonFile(): void
    {
        $file    = TEST_FILES_PATH . 'JsonData/simpleObject.json';
        $actual  = json_encode(['Mascott' => 'Beastie']);
        $message = '';

        $this->assertJsonStringNotEqualsJsonFile($file, $actual, $message);
    }

    public function testAssertJsonFileNotEqualsJsonFile(): void
    {
        $fileExpected = TEST_FILES_PATH . 'JsonData/simpleObject.json';
        $fileActual   = TEST_FILES_PATH . 'JsonData/arrayObject.json';
        $message      = '';

        $this->assertJsonFileNotEqualsJsonFile($fileExpected, $fileActual, $message);
    }

    public function testAssertJsonFileEqualsJsonFile(): void
    {
        $file    = TEST_FILES_PATH . 'JsonData/simpleObject.json';
        $message = '';

        $this->assertJsonFileEqualsJsonFile($file, $file, $message);
    }

    public function testAssertInstanceOfThrowsExceptionIfTypeDoesNotExist(): void
    {
        $this->expectException(Exception::class);

        $this->assertInstanceOf(ClassThatDoesNotExist::class, new stdClass);
    }

    public function testAssertInstanceOf(): void
    {
        $this->assertInstanceOf(stdClass::class, new stdClass);

        $this->expectException(AssertionFailedError::class);

        $this->assertInstanceOf(\Exception::class, new stdClass);
    }

    public function testAssertNotInstanceOfThrowsExceptionIfTypeDoesNotExist(): void
    {
        $this->expectException(Exception::class);

        $this->assertNotInstanceOf(ClassThatDoesNotExist::class, new stdClass);
    }

    public function testAssertNotInstanceOf(): void
    {
        $this->assertNotInstanceOf(\Exception::class, new stdClass);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotInstanceOf(stdClass::class, new stdClass);
    }

    public function testAssertFileMatchesFormat(): void
    {
        $this->assertFileMatchesFormat("FOO\n", TEST_FILES_PATH . 'expectedFileFormat.txt');

        $this->expectException(AssertionFailedError::class);

        $this->assertFileMatchesFormat("BAR\n", TEST_FILES_PATH . 'expectedFileFormat.txt');
    }

    public function testAssertFileMatchesFormatFile(): void
    {
        $this->assertFileMatchesFormatFile(
            TEST_FILES_PATH . 'expectedFileFormat.txt',
            TEST_FILES_PATH . 'expectedFileFormat.txt',
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertFileMatchesFormatFile(
            TEST_FILES_PATH . 'expectedFileFormat.txt',
            TEST_FILES_PATH . 'actualFileFormat.txt',
        );
    }

    public function testAssertStringMatchesFormatFile(): void
    {
        $this->assertStringMatchesFormatFile(TEST_FILES_PATH . 'expectedFileFormat.txt', "FOO\n");

        $this->expectException(AssertionFailedError::class);

        $this->assertStringMatchesFormatFile(TEST_FILES_PATH . 'expectedFileFormat.txt', "BAR\n");
    }

    #[IgnorePhpunitDeprecations]
    public function testAssertStringNotMatchesFormatFile(): void
    {
        $this->assertStringNotMatchesFormatFile(TEST_FILES_PATH . 'expectedFileFormat.txt', "BAR\n");

        $this->expectException(AssertionFailedError::class);

        $this->assertStringNotMatchesFormatFile(TEST_FILES_PATH . 'expectedFileFormat.txt', "FOO\n");
    }

    public function testAssertFileEqualsCanonicalizing(): void
    {
        $this->assertFileNotEquals(TEST_FILES_PATH . 'foo.txt', TEST_FILES_PATH . 'bar.txt');
        $this->assertFileEqualsCanonicalizing(TEST_FILES_PATH . 'foo.txt', TEST_FILES_PATH . 'foo.txt');

        $this->expectException(AssertionFailedError::class);

        $this->assertFileEqualsCanonicalizing(TEST_FILES_PATH . 'foo.txt', TEST_FILES_PATH . 'foo.xml');
    }

    public function testAssertStringNotEqualsFileCanonicalizing(): void
    {
        $contents = file_get_contents(TEST_FILES_PATH . 'foo.xml');

        $this->assertStringNotEqualsFileCanonicalizing(TEST_FILES_PATH . 'foo.xml', $contents . ' BAR');

        $this->assertStringNotEqualsFileCanonicalizing(TEST_FILES_PATH . 'foo.xml', 'BAR');

        $this->expectException(AssertionFailedError::class);

        $this->assertStringNotEqualsFileCanonicalizing(TEST_FILES_PATH . 'foo.xml', $contents);
    }

    public function testStringsCanBeComparedForEqualityIgnoringCase(): void
    {
        $this->assertEqualsIgnoringCase('a', 'A');

        $this->assertNotEqualsIgnoringCase('a', 'B');
    }

    public function testArraysOfStringsCanBeComparedForEqualityIgnoringCase(): void
    {
        $this->assertEqualsIgnoringCase(['a'], ['A']);

        $this->assertNotEqualsIgnoringCase(['a'], ['B']);
    }

    public function testStringsCanBeComparedForEqualityWithDelta(): void
    {
        $this->assertEqualsWithDelta(2.3, 2.5, 0.5);

        $this->assertNotEqualsWithDelta(2.3, 3.5, 0.5);
    }

    public function testArraysOfStringsCanBeComparedForEqualityWithDelta(): void
    {
        $this->assertEqualsWithDelta([2.3], [2.5], 0.5);

        $this->assertNotEqualsWithDelta([2.3], [3.5], 0.5);
    }

    public function testArraysCanBeComparedForEqualityWithCanonicalization(): void
    {
        $this->assertEqualsCanonicalizing([3, 2, 1], [2, 3, 1]);

        $this->assertNotEqualsCanonicalizing([3, 2, 1], [2, 3, 4]);
    }

    public function testArrayTypeCanBeAsserted(): void
    {
        $this->assertIsArray([]);

        try {
            $this->assertIsArray(null);
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testBoolTypeCanBeAsserted(): void
    {
        $this->assertIsBool(true);

        try {
            $this->assertIsBool(null);
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testFloatTypeCanBeAsserted(): void
    {
        $this->assertIsFloat(0.0);

        try {
            $this->assertIsFloat(null);
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testIntTypeCanBeAsserted(): void
    {
        $this->assertIsInt(1);

        try {
            $this->assertIsInt(null);
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testNumericTypeCanBeAsserted(): void
    {
        $this->assertIsNumeric('1.0');

        try {
            $this->assertIsNumeric('abc');
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testObjectTypeCanBeAsserted(): void
    {
        $this->assertIsObject(new stdClass);

        try {
            $this->assertIsObject(null);
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testResourceTypeCanBeAsserted(): void
    {
        $this->assertIsResource(fopen(__FILE__, 'r'));

        try {
            $this->assertIsResource(null);
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testClosedResourceTypeCanBeAsserted(): void
    {
        $resource = fopen(__FILE__, 'r');
        fclose($resource);

        $this->assertIsClosedResource($resource);
        $this->assertIsResource($resource);

        try {
            $this->assertIsClosedResource(null);
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testStringTypeCanBeAsserted(): void
    {
        $this->assertIsString('');

        try {
            $this->assertIsString(null);
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testScalarTypeCanBeAsserted(): void
    {
        $this->assertIsScalar(true);

        try {
            $this->assertIsScalar(new stdClass);
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testCallableTypeCanBeAsserted(): void
    {
        $this->assertIsCallable(static function (): void
        {
        });

        try {
            $this->assertIsCallable(null);
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testIterableTypeCanBeAsserted(): void
    {
        $this->assertIsIterable([]);

        try {
            $this->assertIsIterable(null);
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testNotArrayTypeCanBeAsserted(): void
    {
        $this->assertIsNotArray(null);

        try {
            $this->assertIsNotArray([]);
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testNotBoolTypeCanBeAsserted(): void
    {
        $this->assertIsNotBool(null);

        try {
            $this->assertIsNotBool(true);
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testNotFloatTypeCanBeAsserted(): void
    {
        $this->assertIsNotFloat(null);

        try {
            $this->assertIsNotFloat(0.0);
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testNotIntTypeCanBeAsserted(): void
    {
        $this->assertIsNotInt(null);

        try {
            $this->assertIsNotInt(1);
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testNotNumericTypeCanBeAsserted(): void
    {
        $this->assertIsNotNumeric('abc');

        try {
            $this->assertIsNotNumeric('1.0');
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testNotObjectTypeCanBeAsserted(): void
    {
        $this->assertIsNotObject(null);

        try {
            $this->assertIsNotObject(new stdClass);
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testNotResourceTypeCanBeAsserted(): void
    {
        $this->assertIsNotResource(null);

        try {
            $this->assertIsNotResource(fopen(__FILE__, 'r'));
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testNotClosedResourceTypeCanBeAsserted(): void
    {
        $this->assertIsNotClosedResource(null);

        $resource = fopen(__FILE__, 'r');
        fclose($resource);

        try {
            $this->assertIsNotClosedResource($resource);
        } catch (AssertionFailedError) {
            return;
        }

        try {
            $this->assertIsNotResource($resource);
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testNotScalarTypeCanBeAsserted(): void
    {
        $this->assertIsNotScalar(new stdClass);

        try {
            $this->assertIsNotScalar(true);
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testNotStringTypeCanBeAsserted(): void
    {
        $this->assertIsNotString(null);

        try {
            $this->assertIsNotString('');
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testNotCallableTypeCanBeAsserted(): void
    {
        $this->assertIsNotCallable(null);

        try {
            $this->assertIsNotCallable(static function (): void
            {
            });
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testNotIterableTypeCanBeAsserted(): void
    {
        $this->assertIsNotIterable(null);

        try {
            $this->assertIsNotIterable([]);
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testLogicalAnd(): void
    {
        $this->assertThat(
            true,
            $this->logicalAnd(
                $this->isTrue(),
                $this->isTrue(),
            ),
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertThat(
            true,
            $this->logicalAnd(
                $this->isTrue(),
                $this->isFalse(),
            ),
        );
    }

    public function testLogicalOr(): void
    {
        $this->assertThat(
            true,
            $this->logicalOr(
                $this->isTrue(),
                $this->isFalse(),
            ),
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertThat(
            true,
            $this->logicalOr(
                $this->isFalse(),
                $this->isFalse(),
            ),
        );
    }

    public function testLogicalXor(): void
    {
        $this->assertThat(
            true,
            $this->logicalXor(
                $this->isTrue(),
                $this->isFalse(),
            ),
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertThat(
            true,
            $this->logicalXor(
                $this->isTrue(),
                $this->isTrue(),
            ),
        );
    }

    public function testStringContainsStringCanBeAsserted(): void
    {
        $this->assertStringContainsString('bar', 'foobarbaz');

        try {
            $this->assertStringContainsString('barbara', 'foobarbaz');
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testStringNotContainsStringCanBeAsserted(): void
    {
        $this->assertStringNotContainsString('barbara', 'foobarbaz');

        try {
            $this->assertStringNotContainsString('bar', 'foobarbaz');
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testStringContainsStringCanBeAssertedIgnoringCase(): void
    {
        $this->assertStringContainsStringIgnoringCase('BAR', 'foobarbaz');

        try {
            $this->assertStringContainsStringIgnoringCase('BARBARA', 'foobarbaz');
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testStringNotContainsStringCanBeAssertedIgnoringCase(): void
    {
        $this->assertStringNotContainsStringIgnoringCase('BARBARA', 'foobarbaz');

        try {
            $this->assertStringNotContainsStringIgnoringCase('BAR', 'foobarbaz');
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testIterableContainsSameObjectCanBeAsserted(): void
    {
        $object   = new stdClass;
        $iterable = [$object];

        $this->assertContains($object, $iterable);

        try {
            $this->assertContains(new stdClass, $iterable);
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testIterableNotContainsSameObjectCanBeAsserted(): void
    {
        $object   = new stdClass;
        $iterable = [$object];

        $this->assertNotContains(new stdClass, $iterable);

        try {
            $this->assertNotContains($object, $iterable);
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testIterableContainsEqualObjectCanBeAsserted(): void
    {
        $a      = new stdClass;
        $a->foo = 'bar';

        $b      = new stdClass;
        $b->foo = 'baz';

        $this->assertContainsEquals($a, [$a]);

        try {
            $this->assertContainsEquals($b, [$a]);
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testIterableNotContainsEqualObjectCanBeAsserted(): void
    {
        $a      = new stdClass;
        $a->foo = 'bar';

        $b      = new stdClass;
        $b->foo = 'baz';

        $this->assertNotContainsEquals($b, [$a]);

        try {
            $this->assertNotContainsEquals($a, [$a]);
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testTwoObjectsCanBeAssertedToBeEqualUsingComparisonMethod(): void
    {
        $this->assertObjectEquals(new ValueObject(1), new ValueObject(1));

        try {
            $this->assertObjectEquals(new ValueObject(1), new ValueObject(2));
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testTwoObjectsCanBeAssertedToNotBeEqualUsingComparisonMethod(): void
    {
        $this->assertObjectNotEquals(new ValueObject(1), new ValueObject(2));

        try {
            $this->assertObjectNotEquals(new ValueObject(1), new ValueObject(1));
        } catch (AssertionFailedError) {
            return;
        }

        $this->fail();
    }

    public function testObjectHasPropertyCanBeAsserted(): void
    {
        $objectWithProperty              = new stdClass;
        $objectWithProperty->theProperty = 'value';

        $this->assertObjectHasProperty('theProperty', $objectWithProperty);

        try {
            $this->assertObjectHasProperty('doesNotExist', $objectWithProperty);
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testObjectDoesNotHavePropertyCanBeAsserted(): void
    {
        $objectWithProperty              = new stdClass;
        $objectWithProperty->theProperty = 'value';

        $this->assertObjectNotHasProperty('doesNotExist', $objectWithProperty);

        try {
            $this->assertObjectNotHasProperty('theProperty', $objectWithProperty);
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    protected static function sameValues(): array
    {
        $object   = new SampleClass(4, 8, 15);
        $file     = TEST_FILES_PATH . 'foo.xml';
        $resource = fopen($file, 'r');

        return [
            // null
            [null, null],
            // strings
            ['a', 'a'],
            // integers
            [0, 0],
            // floats
            [1.0, 1.0],
            [2.3, 2.3],
            [1 / 3, 1 / 3],
            [1 - 2 / 3, 1 - 2 / 3],
            [5.5E+123, 5.5E+123],
            [5.5E-123, 5.5E-123],
            [log(0), log(0)],
            [INF, INF],
            [-INF, -INF],
            // arrays
            [[], []],
            [[0 => 1], [0 => 1]],
            [[0 => null], [0 => null]],
            [['a', 'b' => [1, 2]], ['a', 'b' => [1, 2]]],
            // objects
            [$object, $object],
            // resources
            [$resource, $resource],
        ];
    }

    protected static function notEqualValues(): array
    {
        // cyclic dependencies
        $book1                  = new Book;
        $book1->author          = new Author('Terry Pratchett');
        $book1->author->books[] = $book1;
        $book2                  = new Book;
        $book2->author          = new Author('Terry Pratch');
        $book2->author->books[] = $book2;

        $book3         = new Book;
        $book3->author = 'Terry Pratchett';
        $book4         = new stdClass;
        $book4->author = 'Terry Pratchett';

        $object1  = new SampleClass(4, 8, 15);
        $object2  = new SampleClass(16, 23, 42);
        $object3  = new SampleClass(4, 8, 15);
        $storage1 = new SplObjectStorage;
        $storage1->attach($object1);
        $storage2 = new SplObjectStorage;
        $storage2->attach($object3); // same content, different object

        $file = TEST_FILES_PATH . 'foo.xml';

        return [
            // strings
            ['a', 'b'],
            ['a', 'A'],
            // https://github.com/sebastianbergmann/phpunit/issues/1023
            ['9E6666666', '9E7777777'],
            // integers
            [1, 2],
            [2, 1],
            // floats
            [2.3, 4.2],
            [2.3, 4.2, 0.5],
            [[2.3], [4.2], 0.5],
            [[[2.3]], [[4.2]], 0.5],
            [new Struct(2.3), new Struct(4.2), 0.5],
            [[new Struct(2.3)], [new Struct(4.2)], 0.5],
            [1 / 3, 1 - 2 / 3],
            [1 / 3, '0.33333333333333337'],
            [1 - 2 / 3, '3333333333333333'],
            [5.5E+123, 5.6E+123],
            [5.5E-123, 5.6E-123],
            [5.5E+123, 5.5E-123],
            // NAN
            [NAN, NAN],
            // arrays
            [[], [0 => 1]],
            [[0 => 1], []],
            [[0 => null], []],
            [[0 => 1, 1 => 2], [0 => 1, 1 => 3]],
            [['a', 'b' => [1, 2]], ['a', 'b' => [2, 1]]],
            // objects
            [new SampleClass(4, 8, 15), new SampleClass(16, 23, 42)],
            [$object1, $object2],
            [$book1, $book2],
            [$book3, $book4], // same content, different class
            // resources
            [fopen($file, 'r'), fopen($file, 'r')],
            // SplObjectStorage
            [$storage1, $storage2],
            // DOMDocument
            [
                (new XmlLoader)->load('<root></root>'),
                (new XmlLoader)->load('<bar/>'),
            ],
            [
                (new XmlLoader)->load('<foo attr1="bar"/>'),
                (new XmlLoader)->load('<foo attr1="foobar"/>'),
            ],
            [
                (new XmlLoader)->load('<foo> bar </foo>'),
                (new XmlLoader)->load('<foo />'),
            ],
            [
                (new XmlLoader)->load('<foo xmlns="urn:myns:bar"/>'),
                (new XmlLoader)->load('<foo xmlns="urn:notmyns:bar"/>'),
            ],
            [
                (new XmlLoader)->load('<foo> bar </foo>'),
                (new XmlLoader)->load('<foo> bir </foo>'),
            ],
            [
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 03:13:35', new DateTimeZone('America/New_York')),
            ],
            [
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 03:13:35', new DateTimeZone('America/New_York')),
                3500,
            ],
            [
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 05:13:35', new DateTimeZone('America/New_York')),
                3500,
            ],
            [
                new DateTime('2013-03-29', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-30', new DateTimeZone('America/New_York')),
            ],
            [
                new DateTime('2013-03-29', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-30', new DateTimeZone('America/New_York')),
                43200,
            ],
            [
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/Chicago')),
            ],
            [
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/Chicago')),
                3500,
            ],
            [
                new DateTime('2013-03-30', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-30', new DateTimeZone('America/Chicago')),
            ],
            [
                new DateTime('2013-03-29T05:13:35-0600'),
                new DateTime('2013-03-29T04:13:35-0600'),
            ],
            [
                new DateTime('2013-03-29T05:13:35-0600'),
                new DateTime('2013-03-29T05:13:35-0500'),
            ],
            // Exception
            // array(new Exception('Exception 1'), new Exception('Exception 2')),
            // different types
            [new SampleClass(4, 8, 15), false],
            [false, new SampleClass(4, 8, 15)],
            [[0 => 1, 1 => 2], false],
            [false, [0 => 1, 1 => 2]],
            [[], new stdClass],
            [new stdClass, []],
            // PHP: 0 == 'Foobar' => true!
            // We want these values to differ
            [0, 'Foobar'],
            ['Foobar', 0],
            [3, acos(8)],
            [acos(8), 3],
        ];
    }

    protected static function equalValues(): array
    {
        // cyclic dependencies
        $book1                  = new Book;
        $book1->author          = new Author('Terry Pratchett');
        $book1->author->books[] = $book1;
        $book2                  = new Book;
        $book2->author          = new Author('Terry Pratchett');
        $book2->author->books[] = $book2;

        $object1  = new SampleClass(4, 8, 15);
        $object2  = new SampleClass(4, 8, 15);
        $storage1 = new SplObjectStorage;
        $storage1->attach($object1);
        $storage2 = new SplObjectStorage;
        $storage2->attach($object1);

        return [
            // arrays
            [['a' => 1, 'b' => 2], ['b' => 2, 'a' => 1]],
            [[1], ['1']],
            // objects
            [$object1, $object2],
            [$book1, $book2],
            // SplObjectStorage
            [$storage1, $storage2],
            // DOMDocument
            [
                (new XmlLoader)->load('<root></root>'),
                (new XmlLoader)->load('<root/>'),
            ],
            [
                (new XmlLoader)->load('<root attr="bar"></root>'),
                (new XmlLoader)->load('<root attr="bar"/>'),
            ],
            [
                (new XmlLoader)->load('<root><foo attr="bar"></foo></root>'),
                (new XmlLoader)->load('<root><foo attr="bar"/></root>'),
            ],
            [
                (new XmlLoader)->load("<root>\n  <child/>\n</root>"),
                (new XmlLoader)->load('<root><child/></root>'),
            ],
            [
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
            ],
            [
                new DateTime('2013-03-29', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29', new DateTimeZone('America/New_York')),
            ],
            [
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 03:13:35', new DateTimeZone('America/Chicago')),
            ],
            [
                new DateTime('2013-03-30', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 23:00:00', new DateTimeZone('America/Chicago')),
            ],
            [
                new DateTime('@1364616000'),
                new DateTime('2013-03-29 23:00:00', new DateTimeZone('America/Chicago')),
            ],
            [
                new DateTime('2013-03-29T05:13:35-0500'),
                new DateTime('2013-03-29T04:13:35-0600'),
            ],
            // Exception
            // array(new Exception('Exception 1'), new Exception('Exception 1')),
            // mixed types
            [0, '0'],
            ['0', 0],
            [2.3, '2.3'],
            ['2.3', 2.3],
            [1, 1.0],
            [1.0, '1'],
            [1 / 3, '0.3333333333333333'],
            [1 - 2 / 3, '0.33333333333333337'],
            [5.5E+123, '5.5E+123'],
            [5.5E-123, '5.5E-123'],
            ['string representation', new ClassWithToString],
            [new ClassWithToString, 'string representation'],
        ];
    }
}
