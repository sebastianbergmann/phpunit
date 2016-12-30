<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\AssertionFailedError;

/**
 * @since      Class available since Release 2.0.0
 */
class Framework_AssertTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $filesDirectory;

    protected function setUp()
    {
        $this->filesDirectory = dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR;
    }

    /**
     * @covers PHPUnit\Framework\Assert::fail
     */
    public function testFail()
    {
        try {
            $this->fail();
        } catch (AssertionFailedError $e) {
            return;
        }

        throw new AssertionFailedError('Fail did not throw fail exception');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertContains
     */
    public function testAssertSplObjectStorageContainsObject()
    {
        $a = new stdClass;
        $b = new stdClass;
        $c = new SplObjectStorage;
        $c->attach($a);

        $this->assertContains($a, $c);

        $this->expectException(AssertionFailedError::class);

        $this->assertContains($b, $c);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertContains
     */
    public function testAssertArrayContainsObject()
    {
        $a = new stdClass;
        $b = new stdClass;

        $this->assertContains($a, [$a]);

        $this->expectException(AssertionFailedError::class);

        $this->assertContains($a, [$b]);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertContains
     */
    public function testAssertArrayContainsString()
    {
        $this->assertContains('foo', ['foo']);

        $this->expectException(AssertionFailedError::class);

        $this->assertContains('foo', ['bar']);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertContains
     */
    public function testAssertArrayContainsNonObject()
    {
        $this->assertContains('foo', [true]);

        $this->expectException(AssertionFailedError::class);

        $this->assertContains('foo', [true], '', false, true, true);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertContainsOnlyInstancesOf
     */
    public function testAssertContainsOnlyInstancesOf()
    {
        $test = [new Book, new Book];

        $this->assertContainsOnlyInstancesOf('Book', $test);
        $this->assertContainsOnlyInstancesOf('stdClass', [new stdClass()]);

        $test2 = [new Author('Test')];

        $this->expectException(AssertionFailedError::class);

        $this->assertContainsOnlyInstancesOf('Book', $test2);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertArrayHasKey
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertArrayHasKeyThrowsExceptionForInvalidFirstArgument()
    {
        $this->assertArrayHasKey(null, []);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertArrayHasKey
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertArrayHasKeyThrowsExceptionForInvalidSecondArgument()
    {
        $this->assertArrayHasKey(0, null);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertArrayHasKey
     */
    public function testAssertArrayHasIntegerKey()
    {
        $this->assertArrayHasKey(0, ['foo']);

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayHasKey(1, ['foo']);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertArraySubset
     * @covers PHPUnit_Framework_Constraint_ArraySubset
     */
    public function testAssertArraySubset()
    {
        $array = [
            'a' => 'item a',
            'b' => 'item b',
            'c' => ['a2' => 'item a2', 'b2' => 'item b2'],
            'd' => ['a2' => ['a3' => 'item a3', 'b3' => 'item b3']]
        ];

        $this->assertArraySubset(['a' => 'item a', 'c' => ['a2' => 'item a2']], $array);
        $this->assertArraySubset(['a' => 'item a', 'd' => ['a2' => ['b3' => 'item b3']]], $array);

        try {
            $this->assertArraySubset(['a' => 'bad value'], $array);
        } catch (AssertionFailedError $e) {
        }

        try {
            $this->assertArraySubset(['d' => ['a2' => ['bad index' => 'item b3']]], $array);
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertArraySubset
     * @covers PHPUnit_Framework_Constraint_ArraySubset
     */
    public function testAssertArraySubsetWithDeepNestedArrays()
    {
        $array = [
            'path' => [
                'to' => [
                    'the' => [
                        'cake' => 'is a lie'
                    ]
                ]
            ]
        ];

        $this->assertArraySubset(['path' => []], $array);
        $this->assertArraySubset(['path' => ['to' => []]], $array);
        $this->assertArraySubset(['path' => ['to' => ['the' => []]]], $array);
        $this->assertArraySubset(['path' => ['to' => ['the' => ['cake' => 'is a lie']]]], $array);

        $this->expectException(AssertionFailedError::class);

        $this->assertArraySubset(['path' => ['to' => ['the' => ['cake' => 'is not a lie']]]], $array);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertArraySubset
     * @covers PHPUnit_Framework_Constraint_ArraySubset
     */
    public function testAssertArraySubsetWithNoStrictCheckAndObjects()
    {
        $obj       = new \stdClass;
        $reference = &$obj;
        $array     = ['a' => $obj];

        $this->assertArraySubset(['a' => $reference], $array);
        $this->assertArraySubset(['a' => new \stdClass], $array);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertArraySubset
     * @covers PHPUnit_Framework_Constraint_ArraySubset
     */
    public function testAssertArraySubsetWithStrictCheckAndObjects()
    {
        $obj       = new \stdClass;
        $reference = &$obj;
        $array     = ['a' => $obj];

        $this->assertArraySubset(['a' => $reference], $array, true);

        $this->expectException(AssertionFailedError::class);

        $this->assertArraySubset(['a' => new \stdClass], $array, true);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertArraySubset
     * @covers PHPUnit_Framework_Constraint_ArraySubset
     * @expectedException PHPUnit_Framework_Exception
     * @expectedExceptionMessage array or ArrayAccess
     * @dataProvider assertArraySubsetInvalidArgumentProvider
     */
    public function testAssertArraySubsetRaisesExceptionForInvalidArguments($partial, $subject)
    {
        $this->assertArraySubset($partial, $subject);
    }

    /**
     * @return array
     */
    public function assertArraySubsetInvalidArgumentProvider()
    {
        return [
            [false, []],
            [[], false],
        ];
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertArrayNotHasKey
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertArrayNotHasKeyThrowsExceptionForInvalidFirstArgument()
    {
        $this->assertArrayNotHasKey(null, []);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertArrayNotHasKey
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertArrayNotHasKeyThrowsExceptionForInvalidSecondArgument()
    {
        $this->assertArrayNotHasKey(0, null);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertArrayNotHasKey
     */
    public function testAssertArrayNotHasIntegerKey()
    {
        $this->assertArrayNotHasKey(1, ['foo']);

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayNotHasKey(0, ['foo']);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertArrayHasKey
     */
    public function testAssertArrayHasStringKey()
    {
        $this->assertArrayHasKey('foo', ['foo' => 'bar']);

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayHasKey('bar', ['foo' => 'bar']);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertArrayNotHasKey
     */
    public function testAssertArrayNotHasStringKey()
    {
        $this->assertArrayNotHasKey('bar', ['foo' => 'bar']);

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayNotHasKey('foo', ['foo' => 'bar']);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertArrayHasKey
     */
    public function testAssertArrayHasKeyAcceptsArrayObjectValue()
    {
        $array        = new ArrayObject;
        $array['foo'] = 'bar';

        $this->assertArrayHasKey('foo', $array);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertArrayHasKey
     */
    public function testAssertArrayHasKeyProperlyFailsWithArrayObjectValue()
    {
        $array        = new ArrayObject;
        $array['bar'] = 'bar';

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayHasKey('foo', $array);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertArrayHasKey
     */
    public function testAssertArrayHasKeyAcceptsArrayAccessValue()
    {
        $array        = new SampleArrayAccess;
        $array['foo'] = 'bar';

        $this->assertArrayHasKey('foo', $array);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertArrayHasKey
     */
    public function testAssertArrayHasKeyProperlyFailsWithArrayAccessValue()
    {
        $array        = new SampleArrayAccess;
        $array['bar'] = 'bar';

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayHasKey('foo', $array);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertArrayNotHasKey
     */
    public function testAssertArrayNotHasKeyAcceptsArrayAccessValue()
    {
        $array        = new ArrayObject;
        $array['foo'] = 'bar';

        $this->assertArrayNotHasKey('bar', $array);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertArrayNotHasKey
     */
    public function testAssertArrayNotHasKeyPropertlyFailsWithArrayAccessValue()
    {
        $array        = new ArrayObject;
        $array['bar'] = 'bar';

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayNotHasKey('bar', $array);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertContains
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertContainsThrowsException()
    {
        $this->assertContains(null, null);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertContains
     */
    public function testAssertIteratorContainsObject()
    {
        $foo = new stdClass;

        $this->assertContains($foo, new TestIterator([$foo]));

        $this->expectException(AssertionFailedError::class);

        $this->assertContains($foo, new TestIterator([new stdClass]));
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertContains
     */
    public function testAssertIteratorContainsString()
    {
        $this->assertContains('foo', new TestIterator(['foo']));

        $this->expectException(AssertionFailedError::class);

        $this->assertContains('foo', new TestIterator(['bar']));
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertContains
     */
    public function testAssertStringContainsString()
    {
        $this->assertContains('foo', 'foobar');

        $this->expectException(AssertionFailedError::class);

        $this->assertContains('foo', 'bar');
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertNotContains
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertNotContainsThrowsException()
    {
        $this->assertNotContains(null, null);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotContains
     */
    public function testAssertSplObjectStorageNotContainsObject()
    {
        $a = new stdClass;
        $b = new stdClass;
        $c = new SplObjectStorage;
        $c->attach($a);

        $this->assertNotContains($b, $c);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotContains($a, $c);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotContains
     */
    public function testAssertArrayNotContainsObject()
    {
        $a = new stdClass;
        $b = new stdClass;

        $this->assertNotContains($a, [$b]);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotContains($a, [$a]);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotContains
     */
    public function testAssertArrayNotContainsString()
    {
        $this->assertNotContains('foo', ['bar']);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotContains('foo', ['foo']);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotContains
     */
    public function testAssertArrayNotContainsNonObject()
    {
        $this->assertNotContains('foo', [true], '', false, true, true);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotContains('foo', [true]);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotContains
     */
    public function testAssertStringNotContainsString()
    {
        $this->assertNotContains('foo', 'bar');

        $this->expectException(AssertionFailedError::class);

        $this->assertNotContains('foo', 'foo');
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertContainsOnly
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertContainsOnlyThrowsException()
    {
        $this->assertContainsOnly(null, null);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertNotContainsOnly
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertNotContainsOnlyThrowsException()
    {
        $this->assertNotContainsOnly(null, null);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertContainsOnlyInstancesOf
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertContainsOnlyInstancesOfThrowsException()
    {
        $this->assertContainsOnlyInstancesOf(null, null);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertContainsOnly
     */
    public function testAssertArrayContainsOnlyIntegers()
    {
        $this->assertContainsOnly('integer', [1, 2, 3]);

        $this->expectException(AssertionFailedError::class);

        $this->assertContainsOnly('integer', ['1', 2, 3]);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotContainsOnly
     */
    public function testAssertArrayNotContainsOnlyIntegers()
    {
        $this->assertNotContainsOnly('integer', ['1', 2, 3]);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotContainsOnly('integer', [1, 2, 3]);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertContainsOnly
     */
    public function testAssertArrayContainsOnlyStdClass()
    {
        $this->assertContainsOnly('StdClass', [new stdClass]);

        $this->expectException(AssertionFailedError::class);

        $this->assertContainsOnly('StdClass', ['StdClass']);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotContainsOnly
     */
    public function testAssertArrayNotContainsOnlyStdClass()
    {
        $this->assertNotContainsOnly('StdClass', ['StdClass']);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotContainsOnly('StdClass', [new stdClass]);
    }

    protected function sameValues()
    {
        $object = new SampleClass(4, 8, 15);
        // cannot use $filesDirectory, because neither setUp() nor
        // setUpBeforeClass() are executed before the data providers
        $file     = dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'foo.xml';
        $resource = fopen($file, 'r');

        return [
            // null
            [null, null],
            // strings
            ['a', 'a'],
            // integers
            [0, 0],
            // floats
            [2.3, 2.3],
            [1/3, 1 - 2/3],
            [log(0), log(0)],
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

    protected function notEqualValues()
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

        // cannot use $filesDirectory, because neither setUp() nor
        // setUpBeforeClass() are executed before the data providers
        $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'foo.xml';

        return [
            // strings
            ['a', 'b'],
            ['a', 'A'],
            // https://github.com/sebastianbergmann/phpunit/issues/1023
            ['9E6666666','9E7777777'],
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
            // NAN
            [NAN, NAN],
            // arrays
            [[], [0 => 1]],
            [[0     => 1], []],
            [[0     => null], []],
            [[0     => 1, 1 => 2], [0     => 1, 1 => 3]],
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
                PHPUnit_Util_XML::load('<root></root>'),
                PHPUnit_Util_XML::load('<bar/>'),
            ],
            [
                PHPUnit_Util_XML::load('<foo attr1="bar"/>'),
                PHPUnit_Util_XML::load('<foo attr1="foobar"/>'),
            ],
            [
                PHPUnit_Util_XML::load('<foo> bar </foo>'),
                PHPUnit_Util_XML::load('<foo />'),
            ],
            [
                PHPUnit_Util_XML::load('<foo xmlns="urn:myns:bar"/>'),
                PHPUnit_Util_XML::load('<foo xmlns="urn:notmyns:bar"/>'),
            ],
            [
                PHPUnit_Util_XML::load('<foo> bar </foo>'),
                PHPUnit_Util_XML::load('<foo> bir </foo>'),
            ],
            [
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 03:13:35', new DateTimeZone('America/New_York')),
            ],
            [
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 03:13:35', new DateTimeZone('America/New_York')),
                3500
            ],
            [
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 05:13:35', new DateTimeZone('America/New_York')),
                3500
            ],
            [
                new DateTime('2013-03-29', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-30', new DateTimeZone('America/New_York')),
            ],
            [
                new DateTime('2013-03-29', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-30', new DateTimeZone('America/New_York')),
                43200
            ],
            [
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/Chicago')),
            ],
            [
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/Chicago')),
                3500
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
            //array(new Exception('Exception 1'), new Exception('Exception 2')),
            // different types
            [new SampleClass(4, 8, 15), false],
            [false, new SampleClass(4, 8, 15)],
            [[0        => 1, 1 => 2], false],
            [false, [0 => 1, 1 => 2]],
            [[], new stdClass],
            [new stdClass, []],
            // PHP: 0 == 'Foobar' => true!
            // We want these values to differ
            [0, 'Foobar'],
            ['Foobar', 0],
            [3, acos(8)],
            [acos(8), 3]
        ];
    }

    protected function equalValues()
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
            // strings
            ['a', 'A', 0, false, true], // ignore case
            // arrays
            [['a' => 1, 'b' => 2], ['b' => 2, 'a' => 1]],
            [[1], ['1']],
            [[3, 2, 1], [2, 3, 1], 0, true], // canonicalized comparison
            // floats
            [2.3, 2.5, 0.5],
            [[2.3], [2.5], 0.5],
            [[[2.3]], [[2.5]], 0.5],
            [new Struct(2.3), new Struct(2.5), 0.5],
            [[new Struct(2.3)], [new Struct(2.5)], 0.5],
            // numeric with delta
            [1, 2, 1],
            // objects
            [$object1, $object2],
            [$book1, $book2],
            // SplObjectStorage
            [$storage1, $storage2],
            // DOMDocument
            [
                PHPUnit_Util_XML::load('<root></root>'),
                PHPUnit_Util_XML::load('<root/>'),
            ],
            [
                PHPUnit_Util_XML::load('<root attr="bar"></root>'),
                PHPUnit_Util_XML::load('<root attr="bar"/>'),
            ],
            [
                PHPUnit_Util_XML::load('<root><foo attr="bar"></foo></root>'),
                PHPUnit_Util_XML::load('<root><foo attr="bar"/></root>'),
            ],
            [
                PHPUnit_Util_XML::load("<root>\n  <child/>\n</root>"),
                PHPUnit_Util_XML::load('<root><child/></root>'),
            ],
            [
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
            ],
            [
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 04:13:25', new DateTimeZone('America/New_York')),
                10
            ],
            [
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 04:14:40', new DateTimeZone('America/New_York')),
                65
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
                new DateTime('2013-03-29 04:13:35', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 03:13:49', new DateTimeZone('America/Chicago')),
                15
            ],
            [
                new DateTime('2013-03-30', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 23:00:00', new DateTimeZone('America/Chicago')),
            ],
            [
                new DateTime('2013-03-30', new DateTimeZone('America/New_York')),
                new DateTime('2013-03-29 23:01:30', new DateTimeZone('America/Chicago')),
                100
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
            //array(new Exception('Exception 1'), new Exception('Exception 1')),
            // mixed types
            [0, '0'],
            ['0', 0],
            [2.3, '2.3'],
            ['2.3', 2.3],
            [(string) (1/3), 1 - 2/3],
            [1/3, (string) (1 - 2/3)],
            ['string representation', new ClassWithToString],
            [new ClassWithToString, 'string representation'],
        ];
    }

    public function equalProvider()
    {
        // same |= equal
        return array_merge($this->equalValues(), $this->sameValues());
    }

    public function notEqualProvider()
    {
        return $this->notEqualValues();
    }

    public function sameProvider()
    {
        return $this->sameValues();
    }

    public function notSameProvider()
    {
        // not equal |= not same
        // equal, ¬same |= not same
        return array_merge($this->notEqualValues(), $this->equalValues());
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertEquals
     * @dataProvider equalProvider
     */
    public function testAssertEqualsSucceeds($a, $b, $delta = 0.0, $canonicalize = false, $ignoreCase = false)
    {
        $this->assertEquals($a, $b, '', $delta, 10, $canonicalize, $ignoreCase);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertEquals
     * @dataProvider notEqualProvider
     */
    public function testAssertEqualsFails($a, $b, $delta = 0.0, $canonicalize = false, $ignoreCase = false)
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertEquals($a, $b, '', $delta, 10, $canonicalize, $ignoreCase);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotEquals
     * @dataProvider notEqualProvider
     */
    public function testAssertNotEqualsSucceeds($a, $b, $delta = 0.0, $canonicalize = false, $ignoreCase = false)
    {
        $this->assertNotEquals($a, $b, '', $delta, 10, $canonicalize, $ignoreCase);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotEquals
     * @dataProvider equalProvider
     */
    public function testAssertNotEqualsFails($a, $b, $delta = 0.0, $canonicalize = false, $ignoreCase = false)
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertNotEquals($a, $b, '', $delta, 10, $canonicalize, $ignoreCase);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertSame
     * @dataProvider sameProvider
     */
    public function testAssertSameSucceeds($a, $b)
    {
        $this->assertSame($a, $b);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertSame
     * @dataProvider notSameProvider
     */
    public function testAssertSameFails($a, $b)
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertSame($a, $b);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotSame
     * @dataProvider notSameProvider
     */
    public function testAssertNotSameSucceeds($a, $b)
    {
        $this->assertNotSame($a, $b);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotSame
     * @dataProvider sameProvider
     */
    public function testAssertNotSameFails($a, $b)
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertNotSame($a, $b);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertXmlFileEqualsXmlFile
     */
    public function testAssertXmlFileEqualsXmlFile()
    {
        $this->assertXmlFileEqualsXmlFile(
            $this->filesDirectory . 'foo.xml',
            $this->filesDirectory . 'foo.xml'
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertXmlFileEqualsXmlFile(
            $this->filesDirectory . 'foo.xml',
            $this->filesDirectory . 'bar.xml'
        );
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertXmlFileNotEqualsXmlFile
     */
    public function testAssertXmlFileNotEqualsXmlFile()
    {
        $this->assertXmlFileNotEqualsXmlFile(
            $this->filesDirectory . 'foo.xml',
            $this->filesDirectory . 'bar.xml'
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertXmlFileNotEqualsXmlFile(
            $this->filesDirectory . 'foo.xml',
            $this->filesDirectory . 'foo.xml'
        );
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertXmlStringEqualsXmlFile
     */
    public function testAssertXmlStringEqualsXmlFile()
    {
        $this->assertXmlStringEqualsXmlFile(
            $this->filesDirectory . 'foo.xml',
            file_get_contents($this->filesDirectory . 'foo.xml')
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertXmlStringEqualsXmlFile(
            $this->filesDirectory . 'foo.xml',
            file_get_contents($this->filesDirectory . 'bar.xml')
        );
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertXmlStringNotEqualsXmlFile
     */
    public function testXmlStringNotEqualsXmlFile()
    {
        $this->assertXmlStringNotEqualsXmlFile(
            $this->filesDirectory . 'foo.xml',
            file_get_contents($this->filesDirectory . 'bar.xml')
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertXmlStringNotEqualsXmlFile(
            $this->filesDirectory . 'foo.xml',
            file_get_contents($this->filesDirectory . 'foo.xml')
        );
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertXmlStringEqualsXmlString
     */
    public function testAssertXmlStringEqualsXmlString()
    {
        $this->assertXmlStringEqualsXmlString('<root/>', '<root/>');

        $this->expectException(AssertionFailedError::class);

        $this->assertXmlStringEqualsXmlString('<foo/>', '<bar/>');
    }

    /**
     * @expectedException PHPUnit_Framework_Exception
     * @covers            PHPUnit\Framework\Assert::assertXmlStringEqualsXmlString
     * @ticket            1860
     */
    public function testAssertXmlStringEqualsXmlString2()
    {
        $this->assertXmlStringEqualsXmlString('<a></b>', '<c></d>');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertXmlStringEqualsXmlString
     * @ticket 1860
     */
    public function testAssertXmlStringEqualsXmlString3()
    {
        $expected = <<<XML
<?xml version="1.0"?>
<root>
    <node />
</root>
XML;

        $actual = <<<XML
<?xml version="1.0"?>
<root>
<node />
</root>
XML;

        $this->assertXmlStringEqualsXmlString($expected, $actual);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertXmlStringNotEqualsXmlString
     */
    public function testAssertXmlStringNotEqualsXmlString()
    {
        $this->assertXmlStringNotEqualsXmlString('<foo/>', '<bar/>');

        $this->expectException(AssertionFailedError::class);

        $this->assertXmlStringNotEqualsXmlString('<root/>', '<root/>');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertEqualXMLStructure
     */
    public function testXMLStructureIsSame()
    {
        $expected = new DOMDocument;
        $expected->load($this->filesDirectory . 'structureExpected.xml');

        $actual = new DOMDocument;
        $actual->load($this->filesDirectory . 'structureExpected.xml');

        $this->assertEqualXMLStructure(
            $expected->firstChild, $actual->firstChild, true
        );
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertEqualXMLStructure
     * @expectedException PHPUnit_Framework_ExpectationFailedException
     */
    public function testXMLStructureWrongNumberOfAttributes()
    {
        $expected = new DOMDocument;
        $expected->load($this->filesDirectory . 'structureExpected.xml');

        $actual = new DOMDocument;
        $actual->load($this->filesDirectory . 'structureWrongNumberOfAttributes.xml');

        $this->assertEqualXMLStructure(
            $expected->firstChild, $actual->firstChild, true
        );
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertEqualXMLStructure
     * @expectedException PHPUnit_Framework_ExpectationFailedException
     */
    public function testXMLStructureWrongNumberOfNodes()
    {
        $expected = new DOMDocument;
        $expected->load($this->filesDirectory . 'structureExpected.xml');

        $actual = new DOMDocument;
        $actual->load($this->filesDirectory . 'structureWrongNumberOfNodes.xml');

        $this->assertEqualXMLStructure(
            $expected->firstChild, $actual->firstChild, true
        );
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertEqualXMLStructure
     */
    public function testXMLStructureIsSameButDataIsNot()
    {
        $expected = new DOMDocument;
        $expected->load($this->filesDirectory . 'structureExpected.xml');

        $actual = new DOMDocument;
        $actual->load($this->filesDirectory . 'structureIsSameButDataIsNot.xml');

        $this->assertEqualXMLStructure(
            $expected->firstChild, $actual->firstChild, true
        );
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertEqualXMLStructure
     */
    public function testXMLStructureAttributesAreSameButValuesAreNot()
    {
        $expected = new DOMDocument;
        $expected->load($this->filesDirectory . 'structureExpected.xml');

        $actual = new DOMDocument;
        $actual->load($this->filesDirectory . 'structureAttributesAreSameButValuesAreNot.xml');

        $this->assertEqualXMLStructure(
            $expected->firstChild, $actual->firstChild, true
        );
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertEqualXMLStructure
     */
    public function testXMLStructureIgnoreTextNodes()
    {
        $expected = new DOMDocument;
        $expected->load($this->filesDirectory . 'structureExpected.xml');

        $actual = new DOMDocument;
        $actual->load($this->filesDirectory . 'structureIgnoreTextNodes.xml');

        $this->assertEqualXMLStructure(
            $expected->firstChild, $actual->firstChild, true
        );
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertEquals
     */
    public function testAssertStringEqualsNumeric()
    {
        $this->assertEquals('0', 0);

        $this->expectException(AssertionFailedError::class);

        $this->assertEquals('0', 1);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotEquals
     */
    public function testAssertStringEqualsNumeric2()
    {
        $this->assertNotEquals('A', 0);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertIsReadable
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertIsReadableThrowsException()
    {
        $this->assertIsReadable(null);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertIsReadable
     */
    public function testAssertIsReadable()
    {
        $this->assertIsReadable(__FILE__);

        $this->expectException(AssertionFailedError::class);

        $this->assertIsReadable(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertNotIsReadable
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertNotIsReadableThrowsException()
    {
        $this->assertNotIsReadable(null);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotIsReadable
     */
    public function testAssertNotIsReadable()
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertNotIsReadable(__FILE__);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertIsWritable
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertIsWritableThrowsException()
    {
        $this->assertIsWritable(null);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertIsWritable
     */
    public function testAssertIsWritable()
    {
        $this->assertIsWritable(__FILE__);

        $this->expectException(AssertionFailedError::class);

        $this->assertIsWritable(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertNotIsWritable
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertNotIsWritableThrowsException()
    {
        $this->assertNotIsWritable(null);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotIsWritable
     */
    public function testAssertNotIsWritable()
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertNotIsWritable(__FILE__);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertDirectoryExists
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertDirectoryExistsThrowsException()
    {
        $this->assertDirectoryExists(null);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertDirectoryExists
     */
    public function testAssertDirectoryExists()
    {
        $this->assertDirectoryExists(__DIR__);

        $this->expectException(AssertionFailedError::class);

        $this->assertDirectoryExists(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertDirectoryNotExists
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertDirectoryNotExistsThrowsException()
    {
        $this->assertDirectoryNotExists(null);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertDirectoryNotExists
     */
    public function testAssertDirectoryNotExists()
    {
        $this->assertDirectoryNotExists(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');

        $this->expectException(AssertionFailedError::class);

        $this->assertDirectoryNotExists(__DIR__);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertDirectoryIsReadable
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertDirectoryIsReadableThrowsException()
    {
        $this->assertDirectoryIsReadable(null);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertDirectoryIsReadable
     */
    public function testAssertDirectoryIsReadable()
    {
        $this->assertDirectoryIsReadable(__DIR__);

        $this->expectException(AssertionFailedError::class);

        $this->assertDirectoryIsReadable(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertDirectoryNotIsReadable
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertDirectoryNotIsReadableThrowsException()
    {
        $this->assertDirectoryNotIsReadable(null);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertDirectoryIsWritable
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertDirectoryIsWritableThrowsException()
    {
        $this->assertDirectoryIsWritable(null);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertDirectoryIsWritable
     */
    public function testAssertDirectoryIsWritable()
    {
        $this->assertDirectoryIsWritable(__DIR__);

        $this->expectException(AssertionFailedError::class);

        $this->assertDirectoryIsWritable(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertDirectoryNotIsWritable
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertDirectoryNotIsWritableThrowsException()
    {
        $this->assertDirectoryNotIsWritable(null);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertFileExists
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertFileExistsThrowsException()
    {
        $this->assertFileExists(null);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertFileExists
     */
    public function testAssertFileExists()
    {
        $this->assertFileExists(__FILE__);

        $this->expectException(AssertionFailedError::class);

        $this->assertFileExists(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertFileNotExists
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertFileNotExistsThrowsException()
    {
        $this->assertFileNotExists(null);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertFileNotExists
     */
    public function testAssertFileNotExists()
    {
        $this->assertFileNotExists(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');

        $this->expectException(AssertionFailedError::class);

        $this->assertFileNotExists(__FILE__);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertFileIsReadable
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertFileIsReadableThrowsException()
    {
        $this->assertFileIsReadable(null);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertFileIsReadable
     */
    public function testAssertFileIsReadable()
    {
        $this->assertFileIsReadable(__FILE__);

        $this->expectException(AssertionFailedError::class);

        $this->assertFileIsReadable(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertFileNotIsReadable
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertFileNotIsReadableThrowsException()
    {
        $this->assertFileNotIsReadable(null);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertFileIsWritable
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertFileIsWritableThrowsException()
    {
        $this->assertFileIsWritable(null);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertFileIsWritable
     */
    public function testAssertFileIsWritable()
    {
        $this->assertFileIsWritable(__FILE__);

        $this->expectException(AssertionFailedError::class);

        $this->assertFileIsWritable(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertFileNotIsWritable
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertFileNotIsWritableThrowsException()
    {
        $this->assertFileNotIsWritable(null);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertObjectHasAttribute
     */
    public function testAssertObjectHasAttribute()
    {
        $o = new Author('Terry Pratchett');

        $this->assertObjectHasAttribute('name', $o);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectHasAttribute('foo', $o);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertObjectNotHasAttribute
     */
    public function testAssertObjectNotHasAttribute()
    {
        $o = new Author('Terry Pratchett');

        $this->assertObjectNotHasAttribute('foo', $o);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectNotHasAttribute('name', $o);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertFinite
     */
    public function testAssertFinite()
    {
        $this->assertFinite(1);

        $this->expectException(AssertionFailedError::class);

        $this->assertFinite(INF);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertInfinite
     */
    public function testAssertInfinite()
    {
        $this->assertInfinite(INF);

        $this->expectException(AssertionFailedError::class);

        $this->assertInfinite(1);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNan
     */
    public function testAssertNan()
    {
        $this->assertNan(NAN);

        $this->expectException(AssertionFailedError::class);

        $this->assertNan(1);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNull
     */
    public function testAssertNull()
    {
        $this->assertNull(null);

        $this->expectException(AssertionFailedError::class);

        $this->assertNull(new stdClass);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotNull
     */
    public function testAssertNotNull()
    {
        $this->assertNotNull(new stdClass);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotNull(null);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertTrue
     */
    public function testAssertTrue()
    {
        $this->assertTrue(true);

        $this->expectException(AssertionFailedError::class);

        $this->assertTrue(false);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotTrue
     */
    public function testAssertNotTrue()
    {
        $this->assertNotTrue(false);
        $this->assertNotTrue(1);
        $this->assertNotTrue('true');

        $this->expectException(AssertionFailedError::class);

        $this->assertNotTrue(true);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertFalse
     */
    public function testAssertFalse()
    {
        $this->assertFalse(false);

        $this->expectException(AssertionFailedError::class);

        $this->assertFalse(true);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotFalse
     */
    public function testAssertNotFalse()
    {
        $this->assertNotFalse(true);
        $this->assertNotFalse(0);
        $this->assertNotFalse('');

        $this->expectException(AssertionFailedError::class);

        $this->assertNotFalse(false);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertRegExp
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertRegExpThrowsException()
    {
        $this->assertRegExp(null, null);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertRegExp
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertRegExpThrowsException2()
    {
        $this->assertRegExp('', null);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertNotRegExp
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertNotRegExpThrowsException()
    {
        $this->assertNotRegExp(null, null);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertNotRegExp
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertNotRegExpThrowsException2()
    {
        $this->assertNotRegExp('', null);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertRegExp
     */
    public function testAssertRegExp()
    {
        $this->assertRegExp('/foo/', 'foobar');

        $this->expectException(AssertionFailedError::class);

        $this->assertRegExp('/foo/', 'bar');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotRegExp
     */
    public function testAssertNotRegExp()
    {
        $this->assertNotRegExp('/foo/', 'bar');

        $this->expectException(AssertionFailedError::class);

        $this->assertNotRegExp('/foo/', 'foobar');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertSame
     */
    public function testAssertSame()
    {
        $o = new stdClass;

        $this->assertSame($o, $o);

        $this->expectException(AssertionFailedError::class);

        $this->assertSame(new stdClass, new stdClass);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertSame
     */
    public function testAssertSame2()
    {
        $this->assertSame(true, true);
        $this->assertSame(false, false);

        $this->expectException(AssertionFailedError::class);

        $this->assertSame(true, false);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotSame
     */
    public function testAssertNotSame()
    {
        $this->assertNotSame(
            new stdClass,
            null
        );

        $this->assertNotSame(
            null,
            new stdClass
        );

        $this->assertNotSame(
            new stdClass,
            new stdClass
        );

        $o = new stdClass;

        $this->expectException(AssertionFailedError::class);

        $this->assertNotSame($o, $o);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotSame
     */
    public function testAssertNotSame2()
    {
        $this->assertNotSame(true, false);
        $this->assertNotSame(false, true);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotSame(true, true);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotSame
     */
    public function testAssertNotSameFailsNull()
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertNotSame(null, null);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertGreaterThan
     */
    public function testGreaterThan()
    {
        $this->assertGreaterThan(1, 2);

        $this->expectException(AssertionFailedError::class);

        $this->assertGreaterThan(2, 1);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeGreaterThan
     */
    public function testAttributeGreaterThan()
    {
        $this->assertAttributeGreaterThan(
            1, 'bar', new ClassWithNonPublicAttributes
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeGreaterThan(
            1, 'foo', new ClassWithNonPublicAttributes
        );
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertGreaterThanOrEqual
     */
    public function testGreaterThanOrEqual()
    {
        $this->assertGreaterThanOrEqual(1, 2);

        $this->expectException(AssertionFailedError::class);

        $this->assertGreaterThanOrEqual(2, 1);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeGreaterThanOrEqual
     */
    public function testAttributeGreaterThanOrEqual()
    {
        $this->assertAttributeGreaterThanOrEqual(
            1, 'bar', new ClassWithNonPublicAttributes
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeGreaterThanOrEqual(
            2, 'foo', new ClassWithNonPublicAttributes
        );
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertLessThan
     */
    public function testLessThan()
    {
        $this->assertLessThan(2, 1);

        try {
            $this->assertLessThan(1, 2);
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeLessThan
     */
    public function testAttributeLessThan()
    {
        $this->assertAttributeLessThan(
            2, 'foo', new ClassWithNonPublicAttributes
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeLessThan(
            1, 'bar', new ClassWithNonPublicAttributes
        );
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertLessThanOrEqual
     */
    public function testLessThanOrEqual()
    {
        $this->assertLessThanOrEqual(2, 1);

        $this->expectException(AssertionFailedError::class);

        $this->assertLessThanOrEqual(1, 2);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeLessThanOrEqual
     */
    public function testAttributeLessThanOrEqual()
    {
        $this->assertAttributeLessThanOrEqual(
            2, 'foo', new ClassWithNonPublicAttributes
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeLessThanOrEqual(
            1, 'bar', new ClassWithNonPublicAttributes
        );
    }

    /**
     * @covers PHPUnit\Framework\Assert::readAttribute
     * @covers PHPUnit\Framework\Assert::getStaticAttribute
     * @covers PHPUnit\Framework\Assert::getObjectAttribute
     */
    public function testReadAttribute()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertEquals('foo', $this->readAttribute($obj, 'publicAttribute'));
        $this->assertEquals('bar', $this->readAttribute($obj, 'protectedAttribute'));
        $this->assertEquals('baz', $this->readAttribute($obj, 'privateAttribute'));
        $this->assertEquals('bar', $this->readAttribute($obj, 'protectedParentAttribute'));
        //$this->assertEquals('bar', $this->readAttribute($obj, 'privateParentAttribute'));
    }

    /**
     * @covers PHPUnit\Framework\Assert::readAttribute
     * @covers PHPUnit\Framework\Assert::getStaticAttribute
     * @covers PHPUnit\Framework\Assert::getObjectAttribute
     */
    public function testReadAttribute2()
    {
        $this->assertEquals('foo', $this->readAttribute('ClassWithNonPublicAttributes', 'publicStaticAttribute'));
        $this->assertEquals('bar', $this->readAttribute('ClassWithNonPublicAttributes', 'protectedStaticAttribute'));
        $this->assertEquals('baz', $this->readAttribute('ClassWithNonPublicAttributes', 'privateStaticAttribute'));
        $this->assertEquals('foo', $this->readAttribute('ClassWithNonPublicAttributes', 'protectedStaticParentAttribute'));
        $this->assertEquals('foo', $this->readAttribute('ClassWithNonPublicAttributes', 'privateStaticParentAttribute'));
    }

    /**
     * @covers            PHPUnit\Framework\Assert::readAttribute
     * @covers            PHPUnit\Framework\Assert::getStaticAttribute
     * @covers            PHPUnit\Framework\Assert::getObjectAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testReadAttribute3()
    {
        $this->readAttribute('StdClass', null);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::readAttribute
     * @covers            PHPUnit\Framework\Assert::getStaticAttribute
     * @covers            PHPUnit\Framework\Assert::getObjectAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testReadAttribute4()
    {
        $this->readAttribute('NotExistingClass', 'foo');
    }

    /**
     * @covers            PHPUnit\Framework\Assert::readAttribute
     * @covers            PHPUnit\Framework\Assert::getStaticAttribute
     * @covers            PHPUnit\Framework\Assert::getObjectAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testReadAttribute5()
    {
        $this->readAttribute(null, 'foo');
    }

    /**
     * @covers            PHPUnit\Framework\Assert::readAttribute
     * @covers            PHPUnit\Framework\Assert::getStaticAttribute
     * @covers            PHPUnit\Framework\Assert::getObjectAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testReadAttributeIfAttributeNameIsNotValid()
    {
        $this->readAttribute('StdClass', '2');
    }

    /**
     * @covers PHPUnit\Framework\Assert::getStaticAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testGetStaticAttributeRaisesExceptionForInvalidFirstArgument()
    {
        $this->getStaticAttribute(null, 'foo');
    }

    /**
     * @covers PHPUnit\Framework\Assert::getStaticAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testGetStaticAttributeRaisesExceptionForInvalidFirstArgument2()
    {
        $this->getStaticAttribute('NotExistingClass', 'foo');
    }

    /**
     * @covers PHPUnit\Framework\Assert::getStaticAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testGetStaticAttributeRaisesExceptionForInvalidSecondArgument()
    {
        $this->getStaticAttribute('stdClass', null);
    }

    /**
     * @covers PHPUnit\Framework\Assert::getStaticAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testGetStaticAttributeRaisesExceptionForInvalidSecondArgument2()
    {
        $this->getStaticAttribute('stdClass', '0');
    }

    /**
     * @covers PHPUnit\Framework\Assert::getStaticAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testGetStaticAttributeRaisesExceptionForInvalidSecondArgument3()
    {
        $this->getStaticAttribute('stdClass', 'foo');
    }

    /**
     * @covers PHPUnit\Framework\Assert::getObjectAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testGetObjectAttributeRaisesExceptionForInvalidFirstArgument()
    {
        $this->getObjectAttribute(null, 'foo');
    }

    /**
     * @covers PHPUnit\Framework\Assert::getObjectAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testGetObjectAttributeRaisesExceptionForInvalidSecondArgument()
    {
        $this->getObjectAttribute(new stdClass, null);
    }

    /**
     * @covers PHPUnit\Framework\Assert::getObjectAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testGetObjectAttributeRaisesExceptionForInvalidSecondArgument2()
    {
        $this->getObjectAttribute(new stdClass, '0');
    }

    /**
     * @covers PHPUnit\Framework\Assert::getObjectAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testGetObjectAttributeRaisesExceptionForInvalidSecondArgument3()
    {
        $this->getObjectAttribute(new stdClass, 'foo');
    }

    /**
     * @covers PHPUnit\Framework\Assert::getObjectAttribute
     */
    public function testGetObjectAttributeWorksForInheritedAttributes()
    {
        $this->assertEquals(
            'bar',
            $this->getObjectAttribute(new ClassWithNonPublicAttributes, 'privateParentAttribute')
        );
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeContains
     */
    public function testAssertPublicAttributeContains()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeContains('foo', 'publicArray', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeContains('bar', 'publicArray', $obj);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeContainsOnly
     */
    public function testAssertPublicAttributeContainsOnly()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeContainsOnly('string', 'publicArray', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeContainsOnly('integer', 'publicArray', $obj);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeNotContains
     */
    public function testAssertPublicAttributeNotContains()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeNotContains('bar', 'publicArray', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotContains('foo', 'publicArray', $obj);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeNotContainsOnly
     */
    public function testAssertPublicAttributeNotContainsOnly()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeNotContainsOnly('integer', 'publicArray', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotContainsOnly('string', 'publicArray', $obj);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeContains
     */
    public function testAssertProtectedAttributeContains()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeContains('bar', 'protectedArray', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeContains('foo', 'protectedArray', $obj);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeNotContains
     */
    public function testAssertProtectedAttributeNotContains()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeNotContains('foo', 'protectedArray', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotContains('bar', 'protectedArray', $obj);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeContains
     */
    public function testAssertPrivateAttributeContains()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeContains('baz', 'privateArray', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeContains('foo', 'privateArray', $obj);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeNotContains
     */
    public function testAssertPrivateAttributeNotContains()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeNotContains('foo', 'privateArray', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotContains('baz', 'privateArray', $obj);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeContains
     */
    public function testAssertAttributeContainsNonObject()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeContains(true, 'privateArray', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeContains(true, 'privateArray', $obj, '', false, true, true);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeNotContains
     */
    public function testAssertAttributeNotContainsNonObject()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeNotContains(true, 'privateArray', $obj, '', false, true, true);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotContains(true, 'privateArray', $obj);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeEquals
     */
    public function testAssertPublicAttributeEquals()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeEquals('foo', 'publicAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeEquals('bar', 'publicAttribute', $obj);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeNotEquals
     */
    public function testAssertPublicAttributeNotEquals()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeNotEquals('bar', 'publicAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotEquals('foo', 'publicAttribute', $obj);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeSame
     */
    public function testAssertPublicAttributeSame()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeSame('foo', 'publicAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeSame('bar', 'publicAttribute', $obj);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeNotSame
     */
    public function testAssertPublicAttributeNotSame()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeNotSame('bar', 'publicAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotSame('foo', 'publicAttribute', $obj);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeEquals
     */
    public function testAssertProtectedAttributeEquals()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeEquals('bar', 'protectedAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeEquals('foo', 'protectedAttribute', $obj);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeNotEquals
     */
    public function testAssertProtectedAttributeNotEquals()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeNotEquals('foo', 'protectedAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotEquals('bar', 'protectedAttribute', $obj);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeEquals
     */
    public function testAssertPrivateAttributeEquals()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeEquals('baz', 'privateAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeEquals('foo', 'privateAttribute', $obj);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeNotEquals
     */
    public function testAssertPrivateAttributeNotEquals()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertAttributeNotEquals('foo', 'privateAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotEquals('baz', 'privateAttribute', $obj);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeEquals
     */
    public function testAssertPublicStaticAttributeEquals()
    {
        $this->assertAttributeEquals('foo', 'publicStaticAttribute', 'ClassWithNonPublicAttributes');

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeEquals('bar', 'publicStaticAttribute', 'ClassWithNonPublicAttributes');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeNotEquals
     */
    public function testAssertPublicStaticAttributeNotEquals()
    {
        $this->assertAttributeNotEquals('bar', 'publicStaticAttribute', 'ClassWithNonPublicAttributes');

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotEquals('foo', 'publicStaticAttribute', 'ClassWithNonPublicAttributes');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeEquals
     */
    public function testAssertProtectedStaticAttributeEquals()
    {
        $this->assertAttributeEquals('bar', 'protectedStaticAttribute', 'ClassWithNonPublicAttributes');

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeEquals('foo', 'protectedStaticAttribute', 'ClassWithNonPublicAttributes');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeNotEquals
     */
    public function testAssertProtectedStaticAttributeNotEquals()
    {
        $this->assertAttributeNotEquals('foo', 'protectedStaticAttribute', 'ClassWithNonPublicAttributes');

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotEquals('bar', 'protectedStaticAttribute', 'ClassWithNonPublicAttributes');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeEquals
     */
    public function testAssertPrivateStaticAttributeEquals()
    {
        $this->assertAttributeEquals('baz', 'privateStaticAttribute', 'ClassWithNonPublicAttributes');

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeEquals('foo', 'privateStaticAttribute', 'ClassWithNonPublicAttributes');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeNotEquals
     */
    public function testAssertPrivateStaticAttributeNotEquals()
    {
        $this->assertAttributeNotEquals('foo', 'privateStaticAttribute', 'ClassWithNonPublicAttributes');

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotEquals('baz', 'privateStaticAttribute', 'ClassWithNonPublicAttributes');
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertClassHasAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertClassHasAttributeThrowsException()
    {
        $this->assertClassHasAttribute(null, null);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertClassHasAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertClassHasAttributeThrowsException2()
    {
        $this->assertClassHasAttribute('foo', null);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertClassHasAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertClassHasAttributeThrowsExceptionIfAttributeNameIsNotValid()
    {
        $this->assertClassHasAttribute('1', 'ClassWithNonPublicAttributes');
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertClassNotHasAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertClassNotHasAttributeThrowsException()
    {
        $this->assertClassNotHasAttribute(null, null);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertClassNotHasAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertClassNotHasAttributeThrowsException2()
    {
        $this->assertClassNotHasAttribute('foo', null);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertClassNotHasAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertClassNotHasAttributeThrowsExceptionIfAttributeNameIsNotValid()
    {
        $this->assertClassNotHasAttribute('1', 'ClassWithNonPublicAttributes');
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertClassHasStaticAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertClassHasStaticAttributeThrowsException()
    {
        $this->assertClassHasStaticAttribute(null, null);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertClassHasStaticAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertClassHasStaticAttributeThrowsException2()
    {
        $this->assertClassHasStaticAttribute('foo', null);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertClassHasStaticAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertClassHasStaticAttributeThrowsExceptionIfAttributeNameIsNotValid()
    {
        $this->assertClassHasStaticAttribute('1', 'ClassWithNonPublicAttributes');
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertClassNotHasStaticAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertClassNotHasStaticAttributeThrowsException()
    {
        $this->assertClassNotHasStaticAttribute(null, null);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertClassNotHasStaticAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertClassNotHasStaticAttributeThrowsException2()
    {
        $this->assertClassNotHasStaticAttribute('foo', null);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertClassNotHasStaticAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertClassNotHasStaticAttributeThrowsExceptionIfAttributeNameIsNotValid()
    {
        $this->assertClassNotHasStaticAttribute('1', 'ClassWithNonPublicAttributes');
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertObjectHasAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertObjectHasAttributeThrowsException()
    {
        $this->assertObjectHasAttribute(null, null);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertObjectHasAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertObjectHasAttributeThrowsException2()
    {
        $this->assertObjectHasAttribute('foo', null);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertObjectHasAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertObjectHasAttributeThrowsExceptionIfAttributeNameIsNotValid()
    {
        $this->assertObjectHasAttribute('1', 'ClassWithNonPublicAttributes');
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertObjectNotHasAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertObjectNotHasAttributeThrowsException()
    {
        $this->assertObjectNotHasAttribute(null, null);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertObjectNotHasAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertObjectNotHasAttributeThrowsException2()
    {
        $this->assertObjectNotHasAttribute('foo', null);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertObjectNotHasAttribute
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertObjectNotHasAttributeThrowsExceptionIfAttributeNameIsNotValid()
    {
        $this->assertObjectNotHasAttribute('1', 'ClassWithNonPublicAttributes');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertClassHasAttribute
     */
    public function testClassHasPublicAttribute()
    {
        $this->assertClassHasAttribute('publicAttribute', 'ClassWithNonPublicAttributes');

        $this->expectException(AssertionFailedError::class);

        $this->assertClassHasAttribute('attribute', 'ClassWithNonPublicAttributes');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertClassNotHasAttribute
     */
    public function testClassNotHasPublicAttribute()
    {
        $this->assertClassNotHasAttribute('attribute', 'ClassWithNonPublicAttributes');

        $this->expectException(AssertionFailedError::class);

        $this->assertClassNotHasAttribute('publicAttribute', 'ClassWithNonPublicAttributes');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertClassHasStaticAttribute
     */
    public function testClassHasPublicStaticAttribute()
    {
        $this->assertClassHasStaticAttribute('publicStaticAttribute', 'ClassWithNonPublicAttributes');

        $this->expectException(AssertionFailedError::class);

        $this->assertClassHasStaticAttribute('attribute', 'ClassWithNonPublicAttributes');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertClassNotHasStaticAttribute
     */
    public function testClassNotHasPublicStaticAttribute()
    {
        $this->assertClassNotHasStaticAttribute('attribute', 'ClassWithNonPublicAttributes');

        $this->expectException(AssertionFailedError::class);

        $this->assertClassNotHasStaticAttribute('publicStaticAttribute', 'ClassWithNonPublicAttributes');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertObjectHasAttribute
     */
    public function testObjectHasPublicAttribute()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertObjectHasAttribute('publicAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectHasAttribute('attribute', $obj);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertObjectNotHasAttribute
     */
    public function testObjectNotHasPublicAttribute()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertObjectNotHasAttribute('attribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectNotHasAttribute('publicAttribute', $obj);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertObjectHasAttribute
     */
    public function testObjectHasOnTheFlyAttribute()
    {
        $obj      = new stdClass;
        $obj->foo = 'bar';

        $this->assertObjectHasAttribute('foo', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectHasAttribute('bar', $obj);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertObjectNotHasAttribute
     */
    public function testObjectNotHasOnTheFlyAttribute()
    {
        $obj      = new stdClass;
        $obj->foo = 'bar';

        $this->assertObjectNotHasAttribute('bar', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectNotHasAttribute('foo', $obj);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertObjectHasAttribute
     */
    public function testObjectHasProtectedAttribute()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertObjectHasAttribute('protectedAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectHasAttribute('attribute', $obj);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertObjectNotHasAttribute
     */
    public function testObjectNotHasProtectedAttribute()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertObjectNotHasAttribute('attribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectNotHasAttribute('protectedAttribute', $obj);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertObjectHasAttribute
     */
    public function testObjectHasPrivateAttribute()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertObjectHasAttribute('privateAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectHasAttribute('attribute', $obj);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertObjectNotHasAttribute
     */
    public function testObjectNotHasPrivateAttribute()
    {
        $obj = new ClassWithNonPublicAttributes;

        $this->assertObjectNotHasAttribute('attribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectNotHasAttribute('privateAttribute', $obj);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertThat
     * @covers PHPUnit\Framework\Assert::attribute
     * @covers PHPUnit\Framework\Assert::equalTo
     */
    public function testAssertThatAttributeEquals()
    {
        $this->assertThat(
            new ClassWithNonPublicAttributes,
            $this->attribute(
                $this->equalTo('foo'),
                'publicAttribute'
            )
        );
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertThat
     * @covers            PHPUnit\Framework\Assert::attribute
     * @covers            PHPUnit\Framework\Assert::equalTo
     */
    public function testAssertThatAttributeEquals2()
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertThat(
            new ClassWithNonPublicAttributes,
            $this->attribute(
                $this->equalTo('bar'),
                'publicAttribute'
            )
        );
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertThat
     * @covers PHPUnit\Framework\Assert::attribute
     * @covers PHPUnit\Framework\Assert::equalTo
     */
    public function testAssertThatAttributeEqualTo()
    {
        $this->assertThat(
            new ClassWithNonPublicAttributes,
            $this->attributeEqualTo('publicAttribute', 'foo')
        );
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertThat
     * @covers PHPUnit\Framework\Assert::anything
     *
     * @doesNotPerformAssertions
     */
    public function testAssertThatAnything()
    {
        $this->assertThat('anything', $this->anything());
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertThat
     * @covers PHPUnit\Framework\Assert::isTrue
     */
    public function testAssertThatIsTrue()
    {
        $this->assertThat(true, $this->isTrue());
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertThat
     * @covers PHPUnit\Framework\Assert::isFalse
     */
    public function testAssertThatIsFalse()
    {
        $this->assertThat(false, $this->isFalse());
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertThat
     * @covers PHPUnit\Framework\Assert::isJson
     */
    public function testAssertThatIsJson()
    {
        $this->assertThat('{}', $this->isJson());
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertThat
     * @covers PHPUnit\Framework\Assert::anything
     * @covers PHPUnit\Framework\Assert::logicalAnd
     *
     * @doesNotPerformAssertions
     */
    public function testAssertThatAnythingAndAnything()
    {
        $this->assertThat(
            'anything',
            $this->logicalAnd(
                $this->anything(), $this->anything()
            )
        );
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertThat
     * @covers PHPUnit\Framework\Assert::anything
     * @covers PHPUnit\Framework\Assert::logicalOr
     *
     * @doesNotPerformAssertions
     */
    public function testAssertThatAnythingOrAnything()
    {
        $this->assertThat(
            'anything',
            $this->logicalOr(
                $this->anything(), $this->anything()
            )
        );
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertThat
     * @covers PHPUnit\Framework\Assert::anything
     * @covers PHPUnit\Framework\Assert::logicalNot
     * @covers PHPUnit\Framework\Assert::logicalXor
     *
     * @doesNotPerformAssertions
     */
    public function testAssertThatAnythingXorNotAnything()
    {
        $this->assertThat(
            'anything',
            $this->logicalXor(
                $this->anything(),
                $this->logicalNot($this->anything())
            )
        );
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertThat
     * @covers PHPUnit\Framework\Assert::contains
     */
    public function testAssertThatContains()
    {
        $this->assertThat(['foo'], $this->contains('foo'));
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertThat
     * @covers PHPUnit\Framework\Assert::stringContains
     */
    public function testAssertThatStringContains()
    {
        $this->assertThat('barfoobar', $this->stringContains('foo'));
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertThat
     * @covers PHPUnit\Framework\Assert::containsOnly
     */
    public function testAssertThatContainsOnly()
    {
        $this->assertThat(['foo'], $this->containsOnly('string'));
    }
    /**
     * @covers PHPUnit\Framework\Assert::assertThat
     * @covers PHPUnit\Framework\Assert::containsOnlyInstancesOf
     */
    public function testAssertThatContainsOnlyInstancesOf()
    {
        $this->assertThat([new Book], $this->containsOnlyInstancesOf('Book'));
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertThat
     * @covers PHPUnit\Framework\Assert::arrayHasKey
     */
    public function testAssertThatArrayHasKey()
    {
        $this->assertThat(['foo' => 'bar'], $this->arrayHasKey('foo'));
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertThat
     * @covers PHPUnit\Framework\Assert::classHasAttribute
     */
    public function testAssertThatClassHasAttribute()
    {
        $this->assertThat(
            new ClassWithNonPublicAttributes,
            $this->classHasAttribute('publicAttribute')
        );
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertThat
     * @covers PHPUnit\Framework\Assert::classHasStaticAttribute
     */
    public function testAssertThatClassHasStaticAttribute()
    {
        $this->assertThat(
            new ClassWithNonPublicAttributes,
            $this->classHasStaticAttribute('publicStaticAttribute')
        );
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertThat
     * @covers PHPUnit\Framework\Assert::objectHasAttribute
     */
    public function testAssertThatObjectHasAttribute()
    {
        $this->assertThat(
            new ClassWithNonPublicAttributes,
            $this->objectHasAttribute('publicAttribute')
        );
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertThat
     * @covers PHPUnit\Framework\Assert::equalTo
     */
    public function testAssertThatEqualTo()
    {
        $this->assertThat('foo', $this->equalTo('foo'));
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertThat
     * @covers PHPUnit\Framework\Assert::identicalTo
     */
    public function testAssertThatIdenticalTo()
    {
        $value      = new stdClass;
        $constraint = $this->identicalTo($value);

        $this->assertThat($value, $constraint);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertThat
     * @covers PHPUnit\Framework\Assert::isInstanceOf
     */
    public function testAssertThatIsInstanceOf()
    {
        $this->assertThat(new stdClass, $this->isInstanceOf('StdClass'));
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertThat
     * @covers PHPUnit\Framework\Assert::isType
     */
    public function testAssertThatIsType()
    {
        $this->assertThat('string', $this->isType('string'));
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertThat
     * @covers PHPUnit\Framework\Assert::isEmpty
     */
    public function testAssertThatIsEmpty()
    {
        $this->assertThat([], $this->isEmpty());
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertThat
     * @covers PHPUnit\Framework\Assert::fileExists
     */
    public function testAssertThatFileExists()
    {
        $this->assertThat(__FILE__, $this->fileExists());
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertThat
     * @covers PHPUnit\Framework\Assert::greaterThan
     */
    public function testAssertThatGreaterThan()
    {
        $this->assertThat(2, $this->greaterThan(1));
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertThat
     * @covers PHPUnit\Framework\Assert::greaterThanOrEqual
     */
    public function testAssertThatGreaterThanOrEqual()
    {
        $this->assertThat(2, $this->greaterThanOrEqual(1));
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertThat
     * @covers PHPUnit\Framework\Assert::lessThan
     */
    public function testAssertThatLessThan()
    {
        $this->assertThat(1, $this->lessThan(2));
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertThat
     * @covers PHPUnit\Framework\Assert::lessThanOrEqual
     */
    public function testAssertThatLessThanOrEqual()
    {
        $this->assertThat(1, $this->lessThanOrEqual(2));
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertThat
     * @covers PHPUnit\Framework\Assert::matchesRegularExpression
     */
    public function testAssertThatMatchesRegularExpression()
    {
        $this->assertThat('foobar', $this->matchesRegularExpression('/foo/'));
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertThat
     * @covers PHPUnit\Framework\Assert::callback
     */
    public function testAssertThatCallback()
    {
        $this->assertThat(
            null,
            $this->callback(function ($other) {
                return true;
            })
        );
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertThat
     * @covers PHPUnit\Framework\Assert::countOf
     */
    public function testAssertThatCountOf()
    {
        $this->assertThat([1], $this->countOf(1));
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertFileEquals
     */
    public function testAssertFileEquals()
    {
        $this->assertFileEquals(
            $this->filesDirectory . 'foo.xml',
            $this->filesDirectory . 'foo.xml'
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertFileEquals(
            $this->filesDirectory . 'foo.xml',
            $this->filesDirectory . 'bar.xml'
        );
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertFileNotEquals
     */
    public function testAssertFileNotEquals()
    {
        $this->assertFileNotEquals(
            $this->filesDirectory . 'foo.xml',
            $this->filesDirectory . 'bar.xml'
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertFileNotEquals(
            $this->filesDirectory . 'foo.xml',
            $this->filesDirectory . 'foo.xml'
        );
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertStringEqualsFile
     */
    public function testAssertStringEqualsFile()
    {
        $this->assertStringEqualsFile(
            $this->filesDirectory . 'foo.xml',
            file_get_contents($this->filesDirectory . 'foo.xml')
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertStringEqualsFile(
            $this->filesDirectory . 'foo.xml',
            file_get_contents($this->filesDirectory . 'bar.xml')
        );
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertStringNotEqualsFile
     */
    public function testAssertStringNotEqualsFile()
    {
        $this->assertStringNotEqualsFile(
            $this->filesDirectory . 'foo.xml',
            file_get_contents($this->filesDirectory . 'bar.xml')
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertStringNotEqualsFile(
            $this->filesDirectory . 'foo.xml',
            file_get_contents($this->filesDirectory . 'foo.xml')
        );
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertStringStartsWith
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertStringStartsWithThrowsException()
    {
        $this->assertStringStartsWith(null, null);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertStringStartsWith
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertStringStartsWithThrowsException2()
    {
        $this->assertStringStartsWith('', null);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertStringStartsNotWith
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertStringStartsNotWithThrowsException()
    {
        $this->assertStringStartsNotWith(null, null);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertStringStartsNotWith
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertStringStartsNotWithThrowsException2()
    {
        $this->assertStringStartsNotWith('', null);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertStringEndsWith
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertStringEndsWithThrowsException()
    {
        $this->assertStringEndsWith(null, null);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertStringEndsWith
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertStringEndsWithThrowsException2()
    {
        $this->assertStringEndsWith('', null);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertStringEndsNotWith
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertStringEndsNotWithThrowsException()
    {
        $this->assertStringEndsNotWith(null, null);
    }

    /**
     * @covers            PHPUnit\Framework\Assert::assertStringEndsNotWith
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertStringEndsNotWithThrowsException2()
    {
        $this->assertStringEndsNotWith('', null);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertStringStartsWith
     */
    public function testAssertStringStartsWith()
    {
        $this->assertStringStartsWith('prefix', 'prefixfoo');

        $this->expectException(AssertionFailedError::class);

        $this->assertStringStartsWith('prefix', 'foo');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertStringStartsNotWith
     */
    public function testAssertStringStartsNotWith()
    {
        $this->assertStringStartsNotWith('prefix', 'foo');

        $this->expectException(AssertionFailedError::class);

        $this->assertStringStartsNotWith('prefix', 'prefixfoo');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertStringEndsWith
     */
    public function testAssertStringEndsWith()
    {
        $this->assertStringEndsWith('suffix', 'foosuffix');

        $this->expectException(AssertionFailedError::class);

        $this->assertStringEndsWith('suffix', 'foo');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertStringEndsNotWith
     */
    public function testAssertStringEndsNotWith()
    {
        $this->assertStringEndsNotWith('suffix', 'foo');

        $this->expectException(AssertionFailedError::class);

        $this->assertStringEndsNotWith('suffix', 'foosuffix');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertStringMatchesFormat
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertStringMatchesFormatRaisesExceptionForInvalidFirstArgument()
    {
        $this->assertStringMatchesFormat(null, '');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertStringMatchesFormat
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertStringMatchesFormatRaisesExceptionForInvalidSecondArgument()
    {
        $this->assertStringMatchesFormat('', null);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertStringMatchesFormat
     */
    public function testAssertStringMatchesFormat()
    {
        $this->assertStringMatchesFormat('*%s*', '***');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertStringMatchesFormat
     */
    public function testAssertStringMatchesFormatFailure()
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertStringMatchesFormat('*%s*', '**');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertStringNotMatchesFormat
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertStringNotMatchesFormatRaisesExceptionForInvalidFirstArgument()
    {
        $this->assertStringNotMatchesFormat(null, '');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertStringNotMatchesFormat
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertStringNotMatchesFormatRaisesExceptionForInvalidSecondArgument()
    {
        $this->assertStringNotMatchesFormat('', null);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertStringNotMatchesFormat
     */
    public function testAssertStringNotMatchesFormat()
    {
        $this->assertStringNotMatchesFormat('*%s*', '**');

        $this->expectException(AssertionFailedError::class);

        $this->assertStringMatchesFormat('*%s*', '**');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertEmpty
     */
    public function testAssertEmpty()
    {
        $this->assertEmpty([]);

        $this->expectException(AssertionFailedError::class);

        $this->assertEmpty(['foo']);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotEmpty
     */
    public function testAssertNotEmpty()
    {
        $this->assertNotEmpty(['foo']);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotEmpty([]);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeEmpty
     */
    public function testAssertAttributeEmpty()
    {
        $o    = new stdClass;
        $o->a = [];

        $this->assertAttributeEmpty('a', $o);

        $o->a = ['b'];

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeEmpty('a', $o);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeNotEmpty
     */
    public function testAssertAttributeNotEmpty()
    {
        $o    = new stdClass;
        $o->a = ['b'];

        $this->assertAttributeNotEmpty('a', $o);

        $o->a = [];

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotEmpty('a', $o);
    }

    /**
     * @covers PHPUnit\Framework\Assert::markTestIncomplete
     */
    public function testMarkTestIncomplete()
    {
        try {
            $this->markTestIncomplete('incomplete');
        } catch (PHPUnit_Framework_IncompleteTestError $e) {
            $this->assertEquals('incomplete', $e->getMessage());

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit\Framework\Assert::markTestSkipped
     */
    public function testMarkTestSkipped()
    {
        try {
            $this->markTestSkipped('skipped');
        } catch (PHPUnit_Framework_SkippedTestError $e) {
            $this->assertEquals('skipped', $e->getMessage());

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertCount
     */
    public function testAssertCount()
    {
        $this->assertCount(2, [1, 2]);

        $this->expectException(AssertionFailedError::class);

        $this->assertCount(2, [1, 2, 3]);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertCount
     */
    public function testAssertCountTraversable()
    {
        $this->assertCount(2, new ArrayIterator([1, 2]));

        $this->expectException(AssertionFailedError::class);

        $this->assertCount(2, new ArrayIterator([1, 2, 3]));
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertCount
     */
    public function testAssertCountThrowsExceptionIfExpectedCountIsNoInteger()
    {
        try {
            $this->assertCount('a', []);
        } catch (PHPUnit_Framework_Exception $e) {
            $this->assertEquals('Argument #1 (No Value) of PHPUnit\Framework\Assert::assertCount() must be a integer', $e->getMessage());

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertCount
     */
    public function testAssertCountThrowsExceptionIfElementIsNotCountable()
    {
        try {
            $this->assertCount(2, '');
        } catch (PHPUnit_Framework_Exception $e) {
            $this->assertEquals('Argument #2 (No Value) of PHPUnit\Framework\Assert::assertCount() must be a countable or traversable', $e->getMessage());

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeCount
     */
    public function testAssertAttributeCount()
    {
        $o    = new stdClass;
        $o->a = [];

        $this->assertAttributeCount(0, 'a', $o);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotCount
     */
    public function testAssertNotCount()
    {
        $this->assertNotCount(2, [1, 2, 3]);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotCount(2, [1, 2]);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotCount
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertNotCountThrowsExceptionIfExpectedCountIsNoInteger()
    {
        $this->assertNotCount('a', []);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotCount
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertNotCountThrowsExceptionIfElementIsNotCountable()
    {
        $this->assertNotCount(2, '');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeNotCount
     */
    public function testAssertAttributeNotCount()
    {
        $o    = new stdClass;
        $o->a = [];

        $this->assertAttributeNotCount(1, 'a', $o);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertSameSize
     */
    public function testAssertSameSize()
    {
        $this->assertSameSize([1, 2], [3, 4]);

        $this->expectException(AssertionFailedError::class);

        $this->assertSameSize([1, 2], [1, 2, 3]);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertSameSize
     */
    public function testAssertSameSizeThrowsExceptionIfExpectedIsNotCountable()
    {
        try {
            $this->assertSameSize('a', []);
        } catch (PHPUnit_Framework_Exception $e) {
            $this->assertEquals('Argument #1 (No Value) of PHPUnit\Framework\Assert::assertSameSize() must be a countable or traversable', $e->getMessage());

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertSameSize
     */
    public function testAssertSameSizeThrowsExceptionIfActualIsNotCountable()
    {
        try {
            $this->assertSameSize([], '');
        } catch (PHPUnit_Framework_Exception $e) {
            $this->assertEquals('Argument #2 (No Value) of PHPUnit\Framework\Assert::assertSameSize() must be a countable or traversable', $e->getMessage());

            return;
        }

        $this->fail();
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotSameSize
     */
    public function testAssertNotSameSize()
    {
        $this->assertNotSameSize([1, 2], [1, 2, 3]);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotSameSize([1, 2], [3, 4]);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotSameSize
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertNotSameSizeThrowsExceptionIfExpectedIsNotCountable()
    {
        $this->assertNotSameSize('a', []);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotSameSize
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertNotSameSizeThrowsExceptionIfActualIsNotCountable()
    {
        $this->assertNotSameSize([], '');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertJson
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertJsonRaisesExceptionForInvalidArgument()
    {
        $this->assertJson(null);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertJson
     */
    public function testAssertJson()
    {
        $this->assertJson('{}');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertJsonStringEqualsJsonString
     */
    public function testAssertJsonStringEqualsJsonString()
    {
        $expected = '{"Mascott" : "Tux"}';
        $actual   = '{"Mascott" : "Tux"}';
        $message  = 'Given Json strings do not match';

        $this->assertJsonStringEqualsJsonString($expected, $actual, $message);
    }

    /**
     * @dataProvider validInvalidJsonDataprovider
     * @covers PHPUnit\Framework\Assert::assertJsonStringEqualsJsonString
     */
    public function testAssertJsonStringEqualsJsonStringErrorRaised($expected, $actual)
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertJsonStringNotEqualsJsonString
     */
    public function testAssertJsonStringNotEqualsJsonString()
    {
        $expected = '{"Mascott" : "Beastie"}';
        $actual   = '{"Mascott" : "Tux"}';
        $message  = 'Given Json strings do match';

        $this->assertJsonStringNotEqualsJsonString($expected, $actual, $message);
    }

    /**
     * @dataProvider validInvalidJsonDataprovider
     * @covers PHPUnit\Framework\Assert::assertJsonStringNotEqualsJsonString
     */
    public function testAssertJsonStringNotEqualsJsonStringErrorRaised($expected, $actual)
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertJsonStringNotEqualsJsonString($expected, $actual);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertJsonStringEqualsJsonFile
     */
    public function testAssertJsonStringEqualsJsonFile()
    {
        $file    = __DIR__ . '/../_files/JsonData/simpleObject.json';
        $actual  = json_encode(['Mascott' => 'Tux']);
        $message = '';

        $this->assertJsonStringEqualsJsonFile($file, $actual, $message);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertJsonStringEqualsJsonFile
     */
    public function testAssertJsonStringEqualsJsonFileExpectingExpectationFailedException()
    {
        $file    = __DIR__ . '/../_files/JsonData/simpleObject.json';
        $actual  = json_encode(['Mascott' => 'Beastie']);
        $message = '';

        try {
            $this->assertJsonStringEqualsJsonFile($file, $actual, $message);
        } catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->assertEquals(
                'Failed asserting that \'{"Mascott":"Beastie"}\' matches JSON string "{"Mascott":"Tux"}".',
                $e->getMessage()
            );

            return;
        }

        $this->fail('Expected Exception not thrown.');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertJsonStringEqualsJsonFile
     */
    public function testAssertJsonStringEqualsJsonFileExpectingException()
    {
        $file = __DIR__ . '/../_files/JsonData/simpleObject.json';

        try {
            $this->assertJsonStringEqualsJsonFile($file, null);
        } catch (PHPUnit_Framework_Exception $e) {
            return;
        }

        $this->fail('Expected Exception not thrown.');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertJsonStringNotEqualsJsonFile
     */
    public function testAssertJsonStringNotEqualsJsonFile()
    {
        $file    = __DIR__ . '/../_files/JsonData/simpleObject.json';
        $actual  = json_encode(['Mascott' => 'Beastie']);
        $message = '';

        $this->assertJsonStringNotEqualsJsonFile($file, $actual, $message);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertJsonStringNotEqualsJsonFile
     */
    public function testAssertJsonStringNotEqualsJsonFileExpectingException()
    {
        $file = __DIR__ . '/../_files/JsonData/simpleObject.json';

        try {
            $this->assertJsonStringNotEqualsJsonFile($file, null);
        } catch (PHPUnit_Framework_Exception $e) {
            return;
        }

        $this->fail('Expected exception not found.');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertJsonFileNotEqualsJsonFile
     */
    public function testAssertJsonFileNotEqualsJsonFile()
    {
        $fileExpected = __DIR__ . '/../_files/JsonData/simpleObject.json';
        $fileActual   = __DIR__ . '/../_files/JsonData/arrayObject.json';
        $message      = '';

        $this->assertJsonFileNotEqualsJsonFile($fileExpected, $fileActual, $message);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertJsonFileEqualsJsonFile
     */
    public function testAssertJsonFileEqualsJsonFile()
    {
        $file    = __DIR__ . '/../_files/JsonData/simpleObject.json';
        $message = '';

        $this->assertJsonFileEqualsJsonFile($file, $file, $message);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertInstanceOf
     */
    public function testAssertInstanceOf()
    {
        $this->assertInstanceOf('stdClass', new stdClass);

        $this->expectException(AssertionFailedError::class);

        $this->assertInstanceOf('Exception', new stdClass);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertInstanceOf
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertInstanceOfThrowsExceptionForInvalidArgument()
    {
        $this->assertInstanceOf(null, new stdClass);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeInstanceOf
     */
    public function testAssertAttributeInstanceOf()
    {
        $o    = new stdClass;
        $o->a = new stdClass;

        $this->assertAttributeInstanceOf('stdClass', 'a', $o);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotInstanceOf
     */
    public function testAssertNotInstanceOf()
    {
        $this->assertNotInstanceOf('Exception', new stdClass);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotInstanceOf('stdClass', new stdClass);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotInstanceOf
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertNotInstanceOfThrowsExceptionForInvalidArgument()
    {
        $this->assertNotInstanceOf(null, new stdClass);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeNotInstanceOf
     */
    public function testAssertAttributeNotInstanceOf()
    {
        $o    = new stdClass;
        $o->a = new stdClass;

        $this->assertAttributeNotInstanceOf('Exception', 'a', $o);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertInternalType
     */
    public function testAssertInternalType()
    {
        $this->assertInternalType('integer', 1);

        $this->expectException(AssertionFailedError::class);

        $this->assertInternalType('string', 1);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertInternalType
     */
    public function testAssertInternalTypeDouble()
    {
        $this->assertInternalType('double', 1.0);

        $this->expectException(AssertionFailedError::class);

        $this->assertInternalType('double', 1);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertInternalType
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertInternalTypeThrowsExceptionForInvalidArgument()
    {
        $this->assertInternalType(null, 1);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeInternalType
     */
    public function testAssertAttributeInternalType()
    {
        $o    = new stdClass;
        $o->a = 1;

        $this->assertAttributeInternalType('integer', 'a', $o);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotInternalType
     */
    public function testAssertNotInternalType()
    {
        $this->assertNotInternalType('string', 1);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotInternalType('integer', 1);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertNotInternalType
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertNotInternalTypeThrowsExceptionForInvalidArgument()
    {
        $this->assertNotInternalType(null, 1);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertAttributeNotInternalType
     */
    public function testAssertAttributeNotInternalType()
    {
        $o    = new stdClass;
        $o->a = 1;

        $this->assertAttributeNotInternalType('string', 'a', $o);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertStringMatchesFormatFile
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertStringMatchesFormatFileThrowsExceptionForInvalidArgument()
    {
        $this->assertStringMatchesFormatFile('not_existing_file', '');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertStringMatchesFormatFile
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertStringMatchesFormatFileThrowsExceptionForInvalidArgument2()
    {
        $this->assertStringMatchesFormatFile($this->filesDirectory . 'expectedFileFormat.txt', null);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertStringMatchesFormatFile
     */
    public function testAssertStringMatchesFormatFile()
    {
        $this->assertStringMatchesFormatFile($this->filesDirectory . 'expectedFileFormat.txt', "FOO\n");

        $this->expectException(AssertionFailedError::class);

        $this->assertStringMatchesFormatFile($this->filesDirectory . 'expectedFileFormat.txt', "BAR\n");
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertStringNotMatchesFormatFile
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertStringNotMatchesFormatFileThrowsExceptionForInvalidArgument()
    {
        $this->assertStringNotMatchesFormatFile('not_existing_file', '');
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertStringNotMatchesFormatFile
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testAssertStringNotMatchesFormatFileThrowsExceptionForInvalidArgument2()
    {
        $this->assertStringNotMatchesFormatFile($this->filesDirectory . 'expectedFileFormat.txt', null);
    }

    /**
     * @covers PHPUnit\Framework\Assert::assertStringNotMatchesFormatFile
     */
    public function testAssertStringNotMatchesFormatFile()
    {
        $this->assertStringNotMatchesFormatFile($this->filesDirectory . 'expectedFileFormat.txt', "BAR\n");

        $this->expectException(AssertionFailedError::class);

        $this->assertStringNotMatchesFormatFile($this->filesDirectory . 'expectedFileFormat.txt', "FOO\n");
    }

    /**
     * @return array
     */
    public static function validInvalidJsonDataprovider()
    {
        return [
            'error syntax in expected JSON' => ['{"Mascott"::}', '{"Mascott" : "Tux"}'],
            'error UTF-8 in actual JSON'    => ['{"Mascott" : "Tux"}', '{"Mascott" : :}'],
        ];
    }
}
