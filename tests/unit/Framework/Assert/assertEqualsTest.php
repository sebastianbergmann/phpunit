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

use const NAN;
use function acos;
use function array_merge;
use function fopen;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\TestFixture\Author;
use PHPUnit\TestFixture\Book;
use PHPUnit\TestFixture\ClassWithToString;
use PHPUnit\TestFixture\SampleClass;
use PHPUnit\TestFixture\Struct;
use PHPUnit\Util\Xml\Loader as XmlLoader;
use SplObjectStorage;
use stdClass;

#[CoversMethod(Assert::class, 'assertEquals')]
#[TestDox('assertEquals()')]
#[Small]
final class assertEqualsTest extends TestCase
{
    /**
     * @return non-empty-list<array{0: mixed, 1: mixed}>
     */
    public static function successProvider(): array
    {
        return array_merge(self::equalValues(), assertSameTest::sameValues());
    }

    /**
     * @return non-empty-list<array{0: mixed, 1: mixed}>
     */
    public static function failureProvider(): array
    {
        return self::notEqualValues();
    }

    /**
     * @return non-empty-list<array{0: mixed, 1: mixed}>
     */
    public static function equalValues(): array
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
        $storage1->offsetSet($object1);
        $storage2 = new SplObjectStorage;
        $storage2->offsetSet($object1);

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
                new DateTimeImmutable('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTimeImmutable('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
            ],
            [
                new DateTimeImmutable('2013-03-29', new DateTimeZone('America/New_York')),
                new DateTimeImmutable('2013-03-29', new DateTimeZone('America/New_York')),
            ],
            [
                new DateTimeImmutable('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTimeImmutable('2013-03-29 03:13:35', new DateTimeZone('America/Chicago')),
            ],
            [
                new DateTimeImmutable('2013-03-30', new DateTimeZone('America/New_York')),
                new DateTimeImmutable('2013-03-29 23:00:00', new DateTimeZone('America/Chicago')),
            ],
            [
                new DateTimeImmutable('@1364616000'),
                new DateTimeImmutable('2013-03-29 23:00:00', new DateTimeZone('America/Chicago')),
            ],
            [
                new DateTimeImmutable('2013-03-29T05:13:35-0500'),
                new DateTimeImmutable('2013-03-29T04:13:35-0600'),
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

    /**
     * @return non-empty-list<array{0: mixed, 1: mixed}>
     */
    public static function notEqualValues(): array
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
        $storage1->offsetSet($object1);
        $storage2 = new SplObjectStorage;
        $storage2->offsetSet($object3); // same content, different object

        $file = TEST_FILES_PATH . 'foo.xml';

        return [
            [true, false],
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
            [2.3, 4.2],
            [[2.3], [4.2]],
            [[[2.3]], [[4.2]]],
            [new Struct(2.3), new Struct(4.2)],
            [[new Struct(2.3)], [new Struct(4.2)]],
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
                new DateTimeImmutable('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTimeImmutable('2013-03-29 03:13:35', new DateTimeZone('America/New_York')),
            ],
            [
                new DateTimeImmutable('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTimeImmutable('2013-03-29 03:13:35', new DateTimeZone('America/New_York')),
            ],
            [
                new DateTimeImmutable('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTimeImmutable('2013-03-29 05:13:35', new DateTimeZone('America/New_York')),
            ],
            [
                new DateTimeImmutable('2013-03-29', new DateTimeZone('America/New_York')),
                new DateTimeImmutable('2013-03-30', new DateTimeZone('America/New_York')),
            ],
            [
                new DateTimeImmutable('2013-03-29', new DateTimeZone('America/New_York')),
                new DateTimeImmutable('2013-03-30', new DateTimeZone('America/New_York')),
            ],
            [
                new DateTimeImmutable('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTimeImmutable('2013-03-29 04:13:35', new DateTimeZone('America/Chicago')),
            ],
            [
                new DateTimeImmutable('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTimeImmutable('2013-03-29 04:13:35', new DateTimeZone('America/Chicago')),
            ],
            [
                new DateTimeImmutable('2013-03-30', new DateTimeZone('America/New_York')),
                new DateTimeImmutable('2013-03-30', new DateTimeZone('America/Chicago')),
            ],
            [
                new DateTimeImmutable('2013-03-29T05:13:35-0600'),
                new DateTimeImmutable('2013-03-29T04:13:35-0600'),
            ],
            [
                new DateTimeImmutable('2013-03-29T05:13:35-0600'),
                new DateTimeImmutable('2013-03-29T05:13:35-0500'),
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

    #[DataProvider('successProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(mixed $expected, mixed $actual): void
    {
        $this->assertEquals($expected, $actual);
    }

    #[DataProvider('failureProvider')]
    public function testFailsWhenConstraintEvaluatesToFalse(mixed $expected, mixed $actual): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertEquals($expected, $actual);
    }
}
