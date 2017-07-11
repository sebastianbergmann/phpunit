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

use PHPUnit\Util\Xml;

class AssertTest extends TestCase
{
    /**
     * @var string
     */
    private $filesDirectory;

    protected function setUp()
    {
        $this->filesDirectory = \dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR;
    }

    public function testFail()
    {
        try {
            $this->fail();
        } catch (AssertionFailedError $e) {
            return;
        }

        throw new AssertionFailedError('Fail did not throw fail exception');
    }

    public function testAssertSplObjectStorageContainsObject()
    {
        $a = new \stdClass;
        $b = new \stdClass;
        $c = new \SplObjectStorage;
        $c->attach($a);

        $this->assertContains($a, $c);

        $this->expectException(AssertionFailedError::class);

        $this->assertContains($b, $c);
    }

    public function testAssertArrayContainsObject()
    {
        $a = new \stdClass;
        $b = new \stdClass;

        $this->assertContains($a, [$a]);

        $this->expectException(AssertionFailedError::class);

        $this->assertContains($a, [$b]);
    }

    public function testAssertArrayContainsString()
    {
        $this->assertContains('foo', ['foo']);

        $this->expectException(AssertionFailedError::class);

        $this->assertContains('foo', ['bar']);
    }

    public function testAssertArrayContainsNonObject()
    {
        $this->assertContains('foo', [true]);

        $this->expectException(AssertionFailedError::class);

        $this->assertContains('foo', [true], '', false, true, true);
    }

    public function testAssertContainsOnlyInstancesOf()
    {
        $test = [new \Book, new \Book];

        $this->assertContainsOnlyInstancesOf(\Book::class, $test);
        $this->assertContainsOnlyInstancesOf(\stdClass::class, [new \stdClass()]);

        $test2 = [new \Author('Test')];

        $this->expectException(AssertionFailedError::class);

        $this->assertContainsOnlyInstancesOf(\Book::class, $test2);
    }

    public function testAssertArrayHasKeyThrowsExceptionForInvalidFirstArgument()
    {
        $this->expectException(Exception::class);

        $this->assertArrayHasKey(null, []);
    }

    public function testAssertArrayHasKeyThrowsExceptionForInvalidSecondArgument()
    {
        $this->expectException(Exception::class);

        $this->assertArrayHasKey(0, null);
    }

    public function testAssertArrayHasIntegerKey()
    {
        $this->assertArrayHasKey(0, ['foo']);

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayHasKey(1, ['foo']);
    }

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

        $arrayAccessData = new \ArrayObject($array);

        $this->assertArraySubset(['a' => 'item a', 'c' => ['a2' => 'item a2']], $arrayAccessData);
        $this->assertArraySubset(['a' => 'item a', 'd' => ['a2' => ['b3' => 'item b3']]], $arrayAccessData);

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

    public function testAssertArraySubsetWithNoStrictCheckAndObjects()
    {
        $obj       = new \stdClass;
        $reference = &$obj;
        $array     = ['a' => $obj];

        $this->assertArraySubset(['a' => $reference], $array);
        $this->assertArraySubset(['a' => new \stdClass], $array);
    }

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
     * @dataProvider assertArraySubsetInvalidArgumentProvider
     */
    public function testAssertArraySubsetRaisesExceptionForInvalidArguments($partial, $subject)
    {
        $this->expectException(Exception::class);

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

    public function testAssertArrayNotHasKeyThrowsExceptionForInvalidFirstArgument()
    {
        $this->expectException(Exception::class);

        $this->assertArrayNotHasKey(null, []);
    }

    public function testAssertArrayNotHasKeyThrowsExceptionForInvalidSecondArgument()
    {
        $this->expectException(Exception::class);

        $this->assertArrayNotHasKey(0, null);
    }

    public function testAssertArrayNotHasIntegerKey()
    {
        $this->assertArrayNotHasKey(1, ['foo']);

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayNotHasKey(0, ['foo']);
    }

    public function testAssertArrayHasStringKey()
    {
        $this->assertArrayHasKey('foo', ['foo' => 'bar']);

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayHasKey('bar', ['foo' => 'bar']);
    }

    public function testAssertArrayNotHasStringKey()
    {
        $this->assertArrayNotHasKey('bar', ['foo' => 'bar']);

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayNotHasKey('foo', ['foo' => 'bar']);
    }

    public function testAssertArrayHasKeyAcceptsArrayObjectValue()
    {
        $array        = new \ArrayObject;
        $array['foo'] = 'bar';

        $this->assertArrayHasKey('foo', $array);
    }

    public function testAssertArrayHasKeyProperlyFailsWithArrayObjectValue()
    {
        $array        = new \ArrayObject;
        $array['bar'] = 'bar';

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayHasKey('foo', $array);
    }

    public function testAssertArrayHasKeyAcceptsArrayAccessValue()
    {
        $array        = new \SampleArrayAccess;
        $array['foo'] = 'bar';

        $this->assertArrayHasKey('foo', $array);
    }

    public function testAssertArrayHasKeyProperlyFailsWithArrayAccessValue()
    {
        $array        = new \SampleArrayAccess;
        $array['bar'] = 'bar';

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayHasKey('foo', $array);
    }

    public function testAssertArrayNotHasKeyAcceptsArrayAccessValue()
    {
        $array        = new \ArrayObject;
        $array['foo'] = 'bar';

        $this->assertArrayNotHasKey('bar', $array);
    }

    public function testAssertArrayNotHasKeyPropertlyFailsWithArrayAccessValue()
    {
        $array        = new \ArrayObject;
        $array['bar'] = 'bar';

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayNotHasKey('bar', $array);
    }

    public function testAssertContainsThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertContains(null, null);
    }

    public function testAssertIteratorContainsObject()
    {
        $foo = new \stdClass;

        $this->assertContains($foo, new \TestIterator([$foo]));

        $this->expectException(AssertionFailedError::class);

        $this->assertContains($foo, new \TestIterator([new \stdClass]));
    }

    public function testAssertIteratorContainsString()
    {
        $this->assertContains('foo', new \TestIterator(['foo']));

        $this->expectException(AssertionFailedError::class);

        $this->assertContains('foo', new \TestIterator(['bar']));
    }

    public function testAssertStringContainsString()
    {
        $this->assertContains('foo', 'foobar');

        $this->expectException(AssertionFailedError::class);

        $this->assertContains('foo', 'bar');
    }

    public function testAssertStringContainsStringForUtf8()
    {
        $this->assertContains('oryginał', 'oryginał');

        $this->expectException(AssertionFailedError::class);

        $this->assertContains('ORYGINAŁ', 'oryginał');
    }

    public function testAssertStringContainsStringForUtf8WhenIgnoreCase()
    {
        $this->assertContains('oryginał', 'oryginał', '', true);
        $this->assertContains('ORYGINAŁ', 'oryginał', '', true);

        $this->expectException(AssertionFailedError::class);

        $this->assertContains('foo', 'oryginał', '', true);
    }

    public function testAssertNotContainsThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertNotContains(null, null);
    }

    public function testAssertSplObjectStorageNotContainsObject()
    {
        $a = new \stdClass;
        $b = new \stdClass;
        $c = new \SplObjectStorage;
        $c->attach($a);

        $this->assertNotContains($b, $c);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotContains($a, $c);
    }

    public function testAssertArrayNotContainsObject()
    {
        $a = new \stdClass;
        $b = new \stdClass;

        $this->assertNotContains($a, [$b]);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotContains($a, [$a]);
    }

    public function testAssertArrayNotContainsString()
    {
        $this->assertNotContains('foo', ['bar']);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotContains('foo', ['foo']);
    }

    public function testAssertArrayNotContainsNonObject()
    {
        $this->assertNotContains('foo', [true], '', false, true, true);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotContains('foo', [true]);
    }

    public function testAssertStringNotContainsString()
    {
        $this->assertNotContains('foo', 'bar');

        $this->expectException(AssertionFailedError::class);

        $this->assertNotContains('foo', 'foo');
    }

    public function testAssertStringNotContainsStringForUtf8()
    {
        $this->assertNotContains('ORYGINAŁ', 'oryginał');

        $this->expectException(AssertionFailedError::class);

        $this->assertNotContains('oryginał', 'oryginał');
    }

    public function testAssertStringNotContainsStringForUtf8WhenIgnoreCase()
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertNotContains('ORYGINAŁ', 'oryginał', '', true);
    }

    public function testAssertContainsOnlyThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertContainsOnly(null, null);
    }

    public function testAssertNotContainsOnlyThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertNotContainsOnly(null, null);
    }

    public function testAssertContainsOnlyInstancesOfThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertContainsOnlyInstancesOf(null, null);
    }

    public function testAssertArrayContainsOnlyIntegers()
    {
        $this->assertContainsOnly('integer', [1, 2, 3]);

        $this->expectException(AssertionFailedError::class);

        $this->assertContainsOnly('integer', ['1', 2, 3]);
    }

    public function testAssertArrayNotContainsOnlyIntegers()
    {
        $this->assertNotContainsOnly('integer', ['1', 2, 3]);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotContainsOnly('integer', [1, 2, 3]);
    }

    public function testAssertArrayContainsOnlyStdClass()
    {
        $this->assertContainsOnly('StdClass', [new \stdClass]);

        $this->expectException(AssertionFailedError::class);

        $this->assertContainsOnly('StdClass', ['StdClass']);
    }

    public function testAssertArrayNotContainsOnlyStdClass()
    {
        $this->assertNotContainsOnly('StdClass', ['StdClass']);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotContainsOnly('StdClass', [new \stdClass]);
    }

    protected function sameValues()
    {
        $object = new \SampleClass(4, 8, 15);
        // cannot use $filesDirectory, because neither setUp() nor
        // setUpBeforeClass() are executed before the data providers
        $file     = \dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'foo.xml';
        $resource = \fopen($file, 'r');

        return [
            // null
            [null, null],
            // strings
            ['a', 'a'],
            // integers
            [0, 0],
            // floats
            [2.3, 2.3],
            [1 / 3, 1 - 2 / 3],
            [\log(0), \log(0)],
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
        $book1                  = new \Book;
        $book1->author          = new \Author('Terry Pratchett');
        $book1->author->books[] = $book1;
        $book2                  = new \Book;
        $book2->author          = new \Author('Terry Pratch');
        $book2->author->books[] = $book2;

        $book3         = new \Book;
        $book3->author = 'Terry Pratchett';
        $book4         = new \stdClass;
        $book4->author = 'Terry Pratchett';

        $object1  = new \SampleClass(4, 8, 15);
        $object2  = new \SampleClass(16, 23, 42);
        $object3  = new \SampleClass(4, 8, 15);
        $storage1 = new \SplObjectStorage;
        $storage1->attach($object1);
        $storage2 = new \SplObjectStorage;
        $storage2->attach($object3); // same content, different object

        // cannot use $filesDirectory, because neither setUp() nor
        // setUpBeforeClass() are executed before the data providers
        $file = \dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'foo.xml';

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
            [new \Struct(2.3), new \Struct(4.2), 0.5],
            [[new \Struct(2.3)], [new \Struct(4.2)], 0.5],
            // NAN
            [NAN, NAN],
            // arrays
            [[], [0 => 1]],
            [[0     => 1], []],
            [[0     => null], []],
            [[0     => 1, 1 => 2], [0     => 1, 1 => 3]],
            [['a', 'b' => [1, 2]], ['a', 'b' => [2, 1]]],
            // objects
            [new \SampleClass(4, 8, 15), new \SampleClass(16, 23, 42)],
            [$object1, $object2],
            [$book1, $book2],
            [$book3, $book4], // same content, different class
            // resources
            [\fopen($file, 'r'), \fopen($file, 'r')],
            // SplObjectStorage
            [$storage1, $storage2],
            // DOMDocument
            [
                Xml::load('<root></root>'),
                Xml::load('<bar/>'),
            ],
            [
                Xml::load('<foo attr1="bar"/>'),
                Xml::load('<foo attr1="foobar"/>'),
            ],
            [
                Xml::load('<foo> bar </foo>'),
                Xml::load('<foo />'),
            ],
            [
                Xml::load('<foo xmlns="urn:myns:bar"/>'),
                Xml::load('<foo xmlns="urn:notmyns:bar"/>'),
            ],
            [
                Xml::load('<foo> bar </foo>'),
                Xml::load('<foo> bir </foo>'),
            ],
            [
                new \DateTime('2013-03-29 04:13:35', new \DateTimeZone('America/New_York')),
                new \DateTime('2013-03-29 03:13:35', new \DateTimeZone('America/New_York')),
            ],
            [
                new \DateTime('2013-03-29 04:13:35', new \DateTimeZone('America/New_York')),
                new \DateTime('2013-03-29 03:13:35', new \DateTimeZone('America/New_York')),
                3500
            ],
            [
                new \DateTime('2013-03-29 04:13:35', new \DateTimeZone('America/New_York')),
                new \DateTime('2013-03-29 05:13:35', new \DateTimeZone('America/New_York')),
                3500
            ],
            [
                new \DateTime('2013-03-29', new \DateTimeZone('America/New_York')),
                new \DateTime('2013-03-30', new \DateTimeZone('America/New_York')),
            ],
            [
                new \DateTime('2013-03-29', new \DateTimeZone('America/New_York')),
                new \DateTime('2013-03-30', new \DateTimeZone('America/New_York')),
                43200
            ],
            [
                new \DateTime('2013-03-29 04:13:35', new \DateTimeZone('America/New_York')),
                new \DateTime('2013-03-29 04:13:35', new \DateTimeZone('America/Chicago')),
            ],
            [
                new \DateTime('2013-03-29 04:13:35', new \DateTimeZone('America/New_York')),
                new \DateTime('2013-03-29 04:13:35', new \DateTimeZone('America/Chicago')),
                3500
            ],
            [
                new \DateTime('2013-03-30', new \DateTimeZone('America/New_York')),
                new \DateTime('2013-03-30', new \DateTimeZone('America/Chicago')),
            ],
            [
                new \DateTime('2013-03-29T05:13:35-0600'),
                new \DateTime('2013-03-29T04:13:35-0600'),
            ],
            [
                new \DateTime('2013-03-29T05:13:35-0600'),
                new \DateTime('2013-03-29T05:13:35-0500'),
            ],
            // Exception
            //array(new Exception('Exception 1'), new Exception('Exception 2')),
            // different types
            [new \SampleClass(4, 8, 15), false],
            [false, new \SampleClass(4, 8, 15)],
            [[0        => 1, 1 => 2], false],
            [false, [0 => 1, 1 => 2]],
            [[], new \stdClass],
            [new \stdClass, []],
            // PHP: 0 == 'Foobar' => true!
            // We want these values to differ
            [0, 'Foobar'],
            ['Foobar', 0],
            [3, \acos(8)],
            [\acos(8), 3]
        ];
    }

    protected function equalValues()
    {
        // cyclic dependencies
        $book1                  = new \Book;
        $book1->author          = new \Author('Terry Pratchett');
        $book1->author->books[] = $book1;
        $book2                  = new \Book;
        $book2->author          = new \Author('Terry Pratchett');
        $book2->author->books[] = $book2;

        $object1  = new \SampleClass(4, 8, 15);
        $object2  = new \SampleClass(4, 8, 15);
        $storage1 = new \SplObjectStorage;
        $storage1->attach($object1);
        $storage2 = new \SplObjectStorage;
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
            [new \Struct(2.3), new \Struct(2.5), 0.5],
            [[new \Struct(2.3)], [new \Struct(2.5)], 0.5],
            // numeric with delta
            [1, 2, 1],
            // objects
            [$object1, $object2],
            [$book1, $book2],
            // SplObjectStorage
            [$storage1, $storage2],
            // DOMDocument
            [
                Xml::load('<root></root>'),
                Xml::load('<root/>'),
            ],
            [
                Xml::load('<root attr="bar"></root>'),
                Xml::load('<root attr="bar"/>'),
            ],
            [
                Xml::load('<root><foo attr="bar"></foo></root>'),
                Xml::load('<root><foo attr="bar"/></root>'),
            ],
            [
                Xml::load("<root>\n  <child/>\n</root>"),
                Xml::load('<root><child/></root>'),
            ],
            [
                new \DateTime('2013-03-29 04:13:35', new \DateTimeZone('America/New_York')),
                new \DateTime('2013-03-29 04:13:35', new \DateTimeZone('America/New_York')),
            ],
            [
                new \DateTime('2013-03-29 04:13:35', new \DateTimeZone('America/New_York')),
                new \DateTime('2013-03-29 04:13:25', new \DateTimeZone('America/New_York')),
                10
            ],
            [
                new \DateTime('2013-03-29 04:13:35', new \DateTimeZone('America/New_York')),
                new \DateTime('2013-03-29 04:14:40', new \DateTimeZone('America/New_York')),
                65
            ],
            [
                new \DateTime('2013-03-29', new \DateTimeZone('America/New_York')),
                new \DateTime('2013-03-29', new \DateTimeZone('America/New_York')),
            ],
            [
                new \DateTime('2013-03-29 04:13:35', new \DateTimeZone('America/New_York')),
                new \DateTime('2013-03-29 03:13:35', new \DateTimeZone('America/Chicago')),
            ],
            [
                new \DateTime('2013-03-29 04:13:35', new \DateTimeZone('America/New_York')),
                new \DateTime('2013-03-29 03:13:49', new \DateTimeZone('America/Chicago')),
                15
            ],
            [
                new \DateTime('2013-03-30', new \DateTimeZone('America/New_York')),
                new \DateTime('2013-03-29 23:00:00', new \DateTimeZone('America/Chicago')),
            ],
            [
                new \DateTime('2013-03-30', new \DateTimeZone('America/New_York')),
                new \DateTime('2013-03-29 23:01:30', new \DateTimeZone('America/Chicago')),
                100
            ],
            [
                new \DateTime('@1364616000'),
                new \DateTime('2013-03-29 23:00:00', new \DateTimeZone('America/Chicago')),
            ],
            [
                new \DateTime('2013-03-29T05:13:35-0500'),
                new \DateTime('2013-03-29T04:13:35-0600'),
            ],
            // Exception
            //array(new Exception('Exception 1'), new Exception('Exception 1')),
            // mixed types
            [0, '0'],
            ['0', 0],
            [2.3, '2.3'],
            ['2.3', 2.3],
            [(string) (1 / 3), 1 - 2 / 3],
            [1 / 3, (string) (1 - 2 / 3)],
            ['string representation', new \ClassWithToString],
            [new \ClassWithToString, 'string representation'],
        ];
    }

    public function equalProvider()
    {
        // same |= equal
        return \array_merge($this->equalValues(), $this->sameValues());
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
        return \array_merge($this->notEqualValues(), $this->equalValues());
    }

    /**
     * @dataProvider equalProvider
     */
    public function testAssertEqualsSucceeds($a, $b, $delta = 0.0, $canonicalize = false, $ignoreCase = false)
    {
        $this->assertEquals($a, $b, '', $delta, 10, $canonicalize, $ignoreCase);
    }

    /**
     * @dataProvider notEqualProvider
     */
    public function testAssertEqualsFails($a, $b, $delta = 0.0, $canonicalize = false, $ignoreCase = false)
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertEquals($a, $b, '', $delta, 10, $canonicalize, $ignoreCase);
    }

    /**
     * @dataProvider notEqualProvider
     */
    public function testAssertNotEqualsSucceeds($a, $b, $delta = 0.0, $canonicalize = false, $ignoreCase = false)
    {
        $this->assertNotEquals($a, $b, '', $delta, 10, $canonicalize, $ignoreCase);
    }

    /**
     * @dataProvider equalProvider
     */
    public function testAssertNotEqualsFails($a, $b, $delta = 0.0, $canonicalize = false, $ignoreCase = false)
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertNotEquals($a, $b, '', $delta, 10, $canonicalize, $ignoreCase);
    }

    /**
     * @dataProvider sameProvider
     */
    public function testAssertSameSucceeds($a, $b)
    {
        $this->assertSame($a, $b);
    }

    /**
     * @dataProvider notSameProvider
     */
    public function testAssertSameFails($a, $b)
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertSame($a, $b);
    }

    /**
     * @dataProvider notSameProvider
     */
    public function testAssertNotSameSucceeds($a, $b)
    {
        $this->assertNotSame($a, $b);
    }

    /**
     * @dataProvider sameProvider
     */
    public function testAssertNotSameFails($a, $b)
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertNotSame($a, $b);
    }

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

    public function testAssertXmlStringEqualsXmlFile()
    {
        $this->assertXmlStringEqualsXmlFile(
            $this->filesDirectory . 'foo.xml',
            \file_get_contents($this->filesDirectory . 'foo.xml')
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertXmlStringEqualsXmlFile(
            $this->filesDirectory . 'foo.xml',
            \file_get_contents($this->filesDirectory . 'bar.xml')
        );
    }

    public function testXmlStringNotEqualsXmlFile()
    {
        $this->assertXmlStringNotEqualsXmlFile(
            $this->filesDirectory . 'foo.xml',
            \file_get_contents($this->filesDirectory . 'bar.xml')
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertXmlStringNotEqualsXmlFile(
            $this->filesDirectory . 'foo.xml',
            \file_get_contents($this->filesDirectory . 'foo.xml')
        );
    }

    public function testAssertXmlStringEqualsXmlString()
    {
        $this->assertXmlStringEqualsXmlString('<root/>', '<root/>');

        $this->expectException(AssertionFailedError::class);

        $this->assertXmlStringEqualsXmlString('<foo/>', '<bar/>');
    }

    /**
     * @ticket 1860
     */
    public function testAssertXmlStringEqualsXmlString2()
    {
        $this->expectException(Exception::class);

        $this->assertXmlStringEqualsXmlString('<a></b>', '<c></d>');
    }

    /**
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

    public function testAssertXmlStringNotEqualsXmlString()
    {
        $this->assertXmlStringNotEqualsXmlString('<foo/>', '<bar/>');

        $this->expectException(AssertionFailedError::class);

        $this->assertXmlStringNotEqualsXmlString('<root/>', '<root/>');
    }

    public function testXMLStructureIsSame()
    {
        $expected = new \DOMDocument;
        $expected->load($this->filesDirectory . 'structureExpected.xml');

        $actual = new \DOMDocument;
        $actual->load($this->filesDirectory . 'structureExpected.xml');

        $this->assertEqualXMLStructure(
            $expected->firstChild,
            $actual->firstChild,
            true
        );
    }

    public function testXMLStructureWrongNumberOfAttributes()
    {
        $expected = new \DOMDocument;
        $expected->load($this->filesDirectory . 'structureExpected.xml');

        $actual = new \DOMDocument;
        $actual->load($this->filesDirectory . 'structureWrongNumberOfAttributes.xml');

        $this->expectException(ExpectationFailedException::class);

        $this->assertEqualXMLStructure(
            $expected->firstChild,
            $actual->firstChild,
            true
        );
    }

    public function testXMLStructureWrongNumberOfNodes()
    {
        $expected = new \DOMDocument;
        $expected->load($this->filesDirectory . 'structureExpected.xml');

        $actual = new \DOMDocument;
        $actual->load($this->filesDirectory . 'structureWrongNumberOfNodes.xml');

        $this->expectException(ExpectationFailedException::class);

        $this->assertEqualXMLStructure(
            $expected->firstChild,
            $actual->firstChild,
            true
        );
    }

    public function testXMLStructureIsSameButDataIsNot()
    {
        $expected = new \DOMDocument;
        $expected->load($this->filesDirectory . 'structureExpected.xml');

        $actual = new \DOMDocument;
        $actual->load($this->filesDirectory . 'structureIsSameButDataIsNot.xml');

        $this->assertEqualXMLStructure(
            $expected->firstChild,
            $actual->firstChild,
            true
        );
    }

    public function testXMLStructureAttributesAreSameButValuesAreNot()
    {
        $expected = new \DOMDocument;
        $expected->load($this->filesDirectory . 'structureExpected.xml');

        $actual = new \DOMDocument;
        $actual->load($this->filesDirectory . 'structureAttributesAreSameButValuesAreNot.xml');

        $this->assertEqualXMLStructure(
            $expected->firstChild,
            $actual->firstChild,
            true
        );
    }

    public function testXMLStructureIgnoreTextNodes()
    {
        $expected = new \DOMDocument;
        $expected->load($this->filesDirectory . 'structureExpected.xml');

        $actual = new \DOMDocument;
        $actual->load($this->filesDirectory . 'structureIgnoreTextNodes.xml');

        $this->assertEqualXMLStructure(
            $expected->firstChild,
            $actual->firstChild,
            true
        );
    }

    public function testAssertStringEqualsNumeric()
    {
        $this->assertEquals('0', 0);

        $this->expectException(AssertionFailedError::class);

        $this->assertEquals('0', 1);
    }

    public function testAssertStringEqualsNumeric2()
    {
        $this->assertNotEquals('A', 0);
    }

    public function testAssertIsReadableThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertIsReadable(null);
    }

    public function testAssertIsReadable()
    {
        $this->assertIsReadable(__FILE__);

        $this->expectException(AssertionFailedError::class);

        $this->assertIsReadable(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');
    }

    public function testAssertNotIsReadableThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertNotIsReadable(null);
    }

    public function testAssertNotIsReadable()
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertNotIsReadable(__FILE__);
    }

    public function testAssertIsWritableThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertIsWritable(null);
    }

    public function testAssertIsWritable()
    {
        $this->assertIsWritable(__FILE__);

        $this->expectException(AssertionFailedError::class);

        $this->assertIsWritable(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');
    }

    public function testAssertNotIsWritableThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertNotIsWritable(null);
    }

    public function testAssertNotIsWritable()
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertNotIsWritable(__FILE__);
    }

    public function testAssertDirectoryExistsThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertDirectoryExists(null);
    }

    public function testAssertDirectoryExists()
    {
        $this->assertDirectoryExists(__DIR__);

        $this->expectException(AssertionFailedError::class);

        $this->assertDirectoryExists(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');
    }

    public function testAssertDirectoryNotExistsThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertDirectoryNotExists(null);
    }

    public function testAssertDirectoryNotExists()
    {
        $this->assertDirectoryNotExists(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');

        $this->expectException(AssertionFailedError::class);

        $this->assertDirectoryNotExists(__DIR__);
    }

    public function testAssertDirectoryIsReadableThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertDirectoryIsReadable(null);
    }

    public function testAssertDirectoryIsReadable()
    {
        $this->assertDirectoryIsReadable(__DIR__);

        $this->expectException(AssertionFailedError::class);

        $this->assertDirectoryIsReadable(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');
    }

    public function testAssertDirectoryNotIsReadableThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertDirectoryNotIsReadable(null);
    }

    public function testAssertDirectoryIsWritableThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertDirectoryIsWritable(null);
    }

    public function testAssertDirectoryIsWritable()
    {
        $this->assertDirectoryIsWritable(__DIR__);

        $this->expectException(AssertionFailedError::class);

        $this->assertDirectoryIsWritable(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');
    }

    public function testAssertDirectoryNotIsWritableThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertDirectoryNotIsWritable(null);
    }

    public function testAssertFileExistsThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertFileExists(null);
    }

    public function testAssertFileExists()
    {
        $this->assertFileExists(__FILE__);

        $this->expectException(AssertionFailedError::class);

        $this->assertFileExists(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');
    }

    public function testAssertFileNotExistsThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertFileNotExists(null);
    }

    public function testAssertFileNotExists()
    {
        $this->assertFileNotExists(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');

        $this->expectException(AssertionFailedError::class);

        $this->assertFileNotExists(__FILE__);
    }

    public function testAssertFileIsReadableThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertFileIsReadable(null);
    }

    public function testAssertFileIsReadable()
    {
        $this->assertFileIsReadable(__FILE__);

        $this->expectException(AssertionFailedError::class);

        $this->assertFileIsReadable(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');
    }

    public function testAssertFileNotIsReadableThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertFileNotIsReadable(null);
    }

    public function testAssertFileIsWritableThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertFileIsWritable(null);
    }

    public function testAssertFileIsWritable()
    {
        $this->assertFileIsWritable(__FILE__);

        $this->expectException(AssertionFailedError::class);

        $this->assertFileIsWritable(__DIR__ . DIRECTORY_SEPARATOR . 'NotExisting');
    }

    public function testAssertFileNotIsWritableThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertFileNotIsWritable(null);
    }

    public function testAssertObjectHasAttribute()
    {
        $o = new \Author('Terry Pratchett');

        $this->assertObjectHasAttribute('name', $o);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectHasAttribute('foo', $o);
    }

    public function testAssertObjectNotHasAttribute()
    {
        $o = new \Author('Terry Pratchett');

        $this->assertObjectNotHasAttribute('foo', $o);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectNotHasAttribute('name', $o);
    }

    public function testAssertFinite()
    {
        $this->assertFinite(1);

        $this->expectException(AssertionFailedError::class);

        $this->assertFinite(INF);
    }

    public function testAssertInfinite()
    {
        $this->assertInfinite(INF);

        $this->expectException(AssertionFailedError::class);

        $this->assertInfinite(1);
    }

    public function testAssertNan()
    {
        $this->assertNan(NAN);

        $this->expectException(AssertionFailedError::class);

        $this->assertNan(1);
    }

    public function testAssertNull()
    {
        $this->assertNull(null);

        $this->expectException(AssertionFailedError::class);

        $this->assertNull(new \stdClass);
    }

    public function testAssertNotNull()
    {
        $this->assertNotNull(new \stdClass);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotNull(null);
    }

    public function testAssertTrue()
    {
        $this->assertTrue(true);

        $this->expectException(AssertionFailedError::class);

        $this->assertTrue(false);
    }

    public function testAssertNotTrue()
    {
        $this->assertNotTrue(false);
        $this->assertNotTrue(1);
        $this->assertNotTrue('true');

        $this->expectException(AssertionFailedError::class);

        $this->assertNotTrue(true);
    }

    public function testAssertFalse()
    {
        $this->assertFalse(false);

        $this->expectException(AssertionFailedError::class);

        $this->assertFalse(true);
    }

    public function testAssertNotFalse()
    {
        $this->assertNotFalse(true);
        $this->assertNotFalse(0);
        $this->assertNotFalse('');

        $this->expectException(AssertionFailedError::class);

        $this->assertNotFalse(false);
    }

    public function testAssertRegExpThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertRegExp(null, null);
    }

    public function testAssertRegExpThrowsException2()
    {
        $this->expectException(Exception::class);

        $this->assertRegExp('', null);
    }

    public function testAssertNotRegExpThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertNotRegExp(null, null);
    }

    public function testAssertNotRegExpThrowsException2()
    {
        $this->expectException(Exception::class);

        $this->assertNotRegExp('', null);
    }

    public function testAssertRegExp()
    {
        $this->assertRegExp('/foo/', 'foobar');

        $this->expectException(AssertionFailedError::class);

        $this->assertRegExp('/foo/', 'bar');
    }

    public function testAssertNotRegExp()
    {
        $this->assertNotRegExp('/foo/', 'bar');

        $this->expectException(AssertionFailedError::class);

        $this->assertNotRegExp('/foo/', 'foobar');
    }

    public function testAssertSame()
    {
        $o = new \stdClass;

        $this->assertSame($o, $o);

        $this->expectException(AssertionFailedError::class);

        $this->assertSame(new \stdClass, new \stdClass);
    }

    public function testAssertSame2()
    {
        $this->assertSame(true, true);
        $this->assertSame(false, false);

        $this->expectException(AssertionFailedError::class);

        $this->assertSame(true, false);
    }

    public function testAssertNotSame()
    {
        $this->assertNotSame(
            new \stdClass,
            null
        );

        $this->assertNotSame(
            null,
            new \stdClass
        );

        $this->assertNotSame(
            new \stdClass,
            new \stdClass
        );

        $o = new \stdClass;

        $this->expectException(AssertionFailedError::class);

        $this->assertNotSame($o, $o);
    }

    public function testAssertNotSame2()
    {
        $this->assertNotSame(true, false);
        $this->assertNotSame(false, true);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotSame(true, true);
    }

    public function testAssertNotSameFailsNull()
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertNotSame(null, null);
    }

    public function testGreaterThan()
    {
        $this->assertGreaterThan(1, 2);

        $this->expectException(AssertionFailedError::class);

        $this->assertGreaterThan(2, 1);
    }

    public function testAttributeGreaterThan()
    {
        $this->assertAttributeGreaterThan(
            1,
            'bar',
            new \ClassWithNonPublicAttributes
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeGreaterThan(
            1,
            'foo',
            new \ClassWithNonPublicAttributes
        );
    }

    public function testGreaterThanOrEqual()
    {
        $this->assertGreaterThanOrEqual(1, 2);

        $this->expectException(AssertionFailedError::class);

        $this->assertGreaterThanOrEqual(2, 1);
    }

    public function testAttributeGreaterThanOrEqual()
    {
        $this->assertAttributeGreaterThanOrEqual(
            1,
            'bar',
            new \ClassWithNonPublicAttributes
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeGreaterThanOrEqual(
            2,
            'foo',
            new \ClassWithNonPublicAttributes
        );
    }

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

    public function testAttributeLessThan()
    {
        $this->assertAttributeLessThan(
            2,
            'foo',
            new \ClassWithNonPublicAttributes
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeLessThan(
            1,
            'bar',
            new \ClassWithNonPublicAttributes
        );
    }

    public function testLessThanOrEqual()
    {
        $this->assertLessThanOrEqual(2, 1);

        $this->expectException(AssertionFailedError::class);

        $this->assertLessThanOrEqual(1, 2);
    }

    public function testAttributeLessThanOrEqual()
    {
        $this->assertAttributeLessThanOrEqual(
            2,
            'foo',
            new \ClassWithNonPublicAttributes
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeLessThanOrEqual(
            1,
            'bar',
            new \ClassWithNonPublicAttributes
        );
    }

    public function testReadAttribute()
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertEquals('foo', $this->readAttribute($obj, 'publicAttribute'));
        $this->assertEquals('bar', $this->readAttribute($obj, 'protectedAttribute'));
        $this->assertEquals('baz', $this->readAttribute($obj, 'privateAttribute'));
        $this->assertEquals('bar', $this->readAttribute($obj, 'protectedParentAttribute'));
        //$this->assertEquals('bar', $this->readAttribute($obj, 'privateParentAttribute'));
    }

    public function testReadAttribute2()
    {
        $this->assertEquals('foo', $this->readAttribute(\ClassWithNonPublicAttributes::class, 'publicStaticAttribute'));
        $this->assertEquals('bar', $this->readAttribute(\ClassWithNonPublicAttributes::class, 'protectedStaticAttribute'));
        $this->assertEquals('baz', $this->readAttribute(\ClassWithNonPublicAttributes::class, 'privateStaticAttribute'));
        $this->assertEquals('foo', $this->readAttribute(\ClassWithNonPublicAttributes::class, 'protectedStaticParentAttribute'));
        $this->assertEquals('foo', $this->readAttribute(\ClassWithNonPublicAttributes::class, 'privateStaticParentAttribute'));
    }

    public function testReadAttribute3()
    {
        $this->expectException(Exception::class);

        $this->readAttribute('StdClass', null);
    }

    public function testReadAttribute4()
    {
        $this->expectException(Exception::class);

        $this->readAttribute('NotExistingClass', 'foo');
    }

    public function testReadAttribute5()
    {
        $this->expectException(Exception::class);

        $this->readAttribute(null, 'foo');
    }

    public function testReadAttributeIfAttributeNameIsNotValid()
    {
        $this->expectException(Exception::class);

        $this->readAttribute(\stdClass::class, '2');
    }

    public function testGetStaticAttributeRaisesExceptionForInvalidFirstArgument()
    {
        $this->expectException(Exception::class);

        $this->getStaticAttribute(null, 'foo');
    }

    public function testGetStaticAttributeRaisesExceptionForInvalidFirstArgument2()
    {
        $this->expectException(Exception::class);

        $this->getStaticAttribute('NotExistingClass', 'foo');
    }

    public function testGetStaticAttributeRaisesExceptionForInvalidSecondArgument()
    {
        $this->expectException(Exception::class);

        $this->getStaticAttribute(\stdClass::class, null);
    }

    public function testGetStaticAttributeRaisesExceptionForInvalidSecondArgument2()
    {
        $this->expectException(Exception::class);

        $this->getStaticAttribute(\stdClass::class, '0');
    }

    public function testGetStaticAttributeRaisesExceptionForInvalidSecondArgument3()
    {
        $this->expectException(Exception::class);

        $this->getStaticAttribute(\stdClass::class, 'foo');
    }

    public function testGetObjectAttributeRaisesExceptionForInvalidFirstArgument()
    {
        $this->expectException(Exception::class);

        $this->getObjectAttribute(null, 'foo');
    }

    public function testGetObjectAttributeRaisesExceptionForInvalidSecondArgument()
    {
        $this->expectException(Exception::class);

        $this->getObjectAttribute(new \stdClass, null);
    }

    public function testGetObjectAttributeRaisesExceptionForInvalidSecondArgument2()
    {
        $this->expectException(Exception::class);

        $this->getObjectAttribute(new \stdClass, '0');
    }

    public function testGetObjectAttributeRaisesExceptionForInvalidSecondArgument3()
    {
        $this->expectException(Exception::class);

        $this->getObjectAttribute(new \stdClass, 'foo');
    }

    public function testGetObjectAttributeWorksForInheritedAttributes()
    {
        $this->assertEquals(
            'bar',
            $this->getObjectAttribute(new \ClassWithNonPublicAttributes, 'privateParentAttribute')
        );
    }

    public function testAssertPublicAttributeContains()
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeContains('foo', 'publicArray', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeContains('bar', 'publicArray', $obj);
    }

    public function testAssertPublicAttributeContainsOnly()
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeContainsOnly('string', 'publicArray', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeContainsOnly('integer', 'publicArray', $obj);
    }

    public function testAssertPublicAttributeNotContains()
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeNotContains('bar', 'publicArray', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotContains('foo', 'publicArray', $obj);
    }

    public function testAssertPublicAttributeNotContainsOnly()
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeNotContainsOnly('integer', 'publicArray', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotContainsOnly('string', 'publicArray', $obj);
    }

    public function testAssertProtectedAttributeContains()
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeContains('bar', 'protectedArray', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeContains('foo', 'protectedArray', $obj);
    }

    public function testAssertProtectedAttributeNotContains()
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeNotContains('foo', 'protectedArray', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotContains('bar', 'protectedArray', $obj);
    }

    public function testAssertPrivateAttributeContains()
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeContains('baz', 'privateArray', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeContains('foo', 'privateArray', $obj);
    }

    public function testAssertPrivateAttributeNotContains()
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeNotContains('foo', 'privateArray', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotContains('baz', 'privateArray', $obj);
    }

    public function testAssertAttributeContainsNonObject()
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeContains(true, 'privateArray', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeContains(true, 'privateArray', $obj, '', false, true, true);
    }

    public function testAssertAttributeNotContainsNonObject()
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeNotContains(true, 'privateArray', $obj, '', false, true, true);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotContains(true, 'privateArray', $obj);
    }

    public function testAssertPublicAttributeEquals()
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeEquals('foo', 'publicAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeEquals('bar', 'publicAttribute', $obj);
    }

    public function testAssertPublicAttributeNotEquals()
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeNotEquals('bar', 'publicAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotEquals('foo', 'publicAttribute', $obj);
    }

    public function testAssertPublicAttributeSame()
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeSame('foo', 'publicAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeSame('bar', 'publicAttribute', $obj);
    }

    public function testAssertPublicAttributeNotSame()
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeNotSame('bar', 'publicAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotSame('foo', 'publicAttribute', $obj);
    }

    public function testAssertProtectedAttributeEquals()
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeEquals('bar', 'protectedAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeEquals('foo', 'protectedAttribute', $obj);
    }

    public function testAssertProtectedAttributeNotEquals()
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeNotEquals('foo', 'protectedAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotEquals('bar', 'protectedAttribute', $obj);
    }

    public function testAssertPrivateAttributeEquals()
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeEquals('baz', 'privateAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeEquals('foo', 'privateAttribute', $obj);
    }

    public function testAssertPrivateAttributeNotEquals()
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeNotEquals('foo', 'privateAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotEquals('baz', 'privateAttribute', $obj);
    }

    public function testAssertPublicStaticAttributeEquals()
    {
        $this->assertAttributeEquals('foo', 'publicStaticAttribute', \ClassWithNonPublicAttributes::class);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeEquals('bar', 'publicStaticAttribute', \ClassWithNonPublicAttributes::class);
    }

    public function testAssertPublicStaticAttributeNotEquals()
    {
        $this->assertAttributeNotEquals('bar', 'publicStaticAttribute', \ClassWithNonPublicAttributes::class);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotEquals('foo', 'publicStaticAttribute', \ClassWithNonPublicAttributes::class);
    }

    public function testAssertProtectedStaticAttributeEquals()
    {
        $this->assertAttributeEquals('bar', 'protectedStaticAttribute', \ClassWithNonPublicAttributes::class);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeEquals('foo', 'protectedStaticAttribute', \ClassWithNonPublicAttributes::class);
    }

    public function testAssertProtectedStaticAttributeNotEquals()
    {
        $this->assertAttributeNotEquals('foo', 'protectedStaticAttribute', \ClassWithNonPublicAttributes::class);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotEquals('bar', 'protectedStaticAttribute', \ClassWithNonPublicAttributes::class);
    }

    public function testAssertPrivateStaticAttributeEquals()
    {
        $this->assertAttributeEquals('baz', 'privateStaticAttribute', \ClassWithNonPublicAttributes::class);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeEquals('foo', 'privateStaticAttribute', \ClassWithNonPublicAttributes::class);
    }

    public function testAssertPrivateStaticAttributeNotEquals()
    {
        $this->assertAttributeNotEquals('foo', 'privateStaticAttribute', \ClassWithNonPublicAttributes::class);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotEquals('baz', 'privateStaticAttribute', \ClassWithNonPublicAttributes::class);
    }

    public function testAssertClassHasAttributeThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertClassHasAttribute(null, null);
    }

    public function testAssertClassHasAttributeThrowsException2()
    {
        $this->expectException(Exception::class);

        $this->assertClassHasAttribute('foo', null);
    }

    public function testAssertClassHasAttributeThrowsExceptionIfAttributeNameIsNotValid()
    {
        $this->expectException(Exception::class);

        $this->assertClassHasAttribute('1', \ClassWithNonPublicAttributes::class);
    }

    public function testAssertClassNotHasAttributeThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertClassNotHasAttribute(null, null);
    }

    public function testAssertClassNotHasAttributeThrowsException2()
    {
        $this->expectException(Exception::class);

        $this->assertClassNotHasAttribute('foo', null);
    }

    public function testAssertClassNotHasAttributeThrowsExceptionIfAttributeNameIsNotValid()
    {
        $this->expectException(Exception::class);

        $this->assertClassNotHasAttribute('1', \ClassWithNonPublicAttributes::class);
    }

    public function testAssertClassHasStaticAttributeThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertClassHasStaticAttribute(null, null);
    }

    public function testAssertClassHasStaticAttributeThrowsException2()
    {
        $this->expectException(Exception::class);

        $this->assertClassHasStaticAttribute('foo', null);
    }

    public function testAssertClassHasStaticAttributeThrowsExceptionIfAttributeNameIsNotValid()
    {
        $this->expectException(Exception::class);

        $this->assertClassHasStaticAttribute('1', \ClassWithNonPublicAttributes::class);
    }

    public function testAssertClassNotHasStaticAttributeThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertClassNotHasStaticAttribute(null, null);
    }

    public function testAssertClassNotHasStaticAttributeThrowsException2()
    {
        $this->expectException(Exception::class);

        $this->assertClassNotHasStaticAttribute('foo', null);
    }

    public function testAssertClassNotHasStaticAttributeThrowsExceptionIfAttributeNameIsNotValid()
    {
        $this->expectException(Exception::class);

        $this->assertClassNotHasStaticAttribute('1', \ClassWithNonPublicAttributes::class);
    }

    public function testAssertObjectHasAttributeThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertObjectHasAttribute(null, null);
    }

    public function testAssertObjectHasAttributeThrowsException2()
    {
        $this->expectException(Exception::class);

        $this->assertObjectHasAttribute('foo', null);
    }

    public function testAssertObjectHasAttributeThrowsExceptionIfAttributeNameIsNotValid()
    {
        $this->expectException(Exception::class);

        $this->assertObjectHasAttribute('1', \ClassWithNonPublicAttributes::class);
    }

    public function testAssertObjectNotHasAttributeThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertObjectNotHasAttribute(null, null);
    }

    public function testAssertObjectNotHasAttributeThrowsException2()
    {
        $this->expectException(Exception::class);

        $this->assertObjectNotHasAttribute('foo', null);
    }

    public function testAssertObjectNotHasAttributeThrowsExceptionIfAttributeNameIsNotValid()
    {
        $this->expectException(Exception::class);

        $this->assertObjectNotHasAttribute('1', \ClassWithNonPublicAttributes::class);
    }

    public function testClassHasPublicAttribute()
    {
        $this->assertClassHasAttribute('publicAttribute', \ClassWithNonPublicAttributes::class);

        $this->expectException(AssertionFailedError::class);

        $this->assertClassHasAttribute('attribute', \ClassWithNonPublicAttributes::class);
    }

    public function testClassNotHasPublicAttribute()
    {
        $this->assertClassNotHasAttribute('attribute', \ClassWithNonPublicAttributes::class);

        $this->expectException(AssertionFailedError::class);

        $this->assertClassNotHasAttribute('publicAttribute', \ClassWithNonPublicAttributes::class);
    }

    public function testClassHasPublicStaticAttribute()
    {
        $this->assertClassHasStaticAttribute('publicStaticAttribute', \ClassWithNonPublicAttributes::class);

        $this->expectException(AssertionFailedError::class);

        $this->assertClassHasStaticAttribute('attribute', \ClassWithNonPublicAttributes::class);
    }

    public function testClassNotHasPublicStaticAttribute()
    {
        $this->assertClassNotHasStaticAttribute('attribute', \ClassWithNonPublicAttributes::class);

        $this->expectException(AssertionFailedError::class);

        $this->assertClassNotHasStaticAttribute('publicStaticAttribute', \ClassWithNonPublicAttributes::class);
    }

    public function testObjectHasPublicAttribute()
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertObjectHasAttribute('publicAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectHasAttribute('attribute', $obj);
    }

    public function testObjectNotHasPublicAttribute()
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertObjectNotHasAttribute('attribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectNotHasAttribute('publicAttribute', $obj);
    }

    public function testObjectHasOnTheFlyAttribute()
    {
        $obj      = new \stdClass;
        $obj->foo = 'bar';

        $this->assertObjectHasAttribute('foo', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectHasAttribute('bar', $obj);
    }

    public function testObjectNotHasOnTheFlyAttribute()
    {
        $obj      = new \stdClass;
        $obj->foo = 'bar';

        $this->assertObjectNotHasAttribute('bar', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectNotHasAttribute('foo', $obj);
    }

    public function testObjectHasProtectedAttribute()
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertObjectHasAttribute('protectedAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectHasAttribute('attribute', $obj);
    }

    public function testObjectNotHasProtectedAttribute()
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertObjectNotHasAttribute('attribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectNotHasAttribute('protectedAttribute', $obj);
    }

    public function testObjectHasPrivateAttribute()
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertObjectHasAttribute('privateAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectHasAttribute('attribute', $obj);
    }

    public function testObjectNotHasPrivateAttribute()
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertObjectNotHasAttribute('attribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectNotHasAttribute('privateAttribute', $obj);
    }

    public function testAssertThatAttributeEquals()
    {
        $this->assertThat(
            new \ClassWithNonPublicAttributes,
            $this->attribute(
                $this->equalTo('foo'),
                'publicAttribute'
            )
        );
    }

    public function testAssertThatAttributeEquals2()
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertThat(
            new \ClassWithNonPublicAttributes,
            $this->attribute(
                $this->equalTo('bar'),
                'publicAttribute'
            )
        );
    }

    public function testAssertThatAttributeEqualTo()
    {
        $this->assertThat(
            new \ClassWithNonPublicAttributes,
            $this->attributeEqualTo('publicAttribute', 'foo')
        );
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testAssertThatAnything()
    {
        $this->assertThat('anything', $this->anything());
    }

    public function testAssertThatIsTrue()
    {
        $this->assertThat(true, $this->isTrue());
    }

    public function testAssertThatIsFalse()
    {
        $this->assertThat(false, $this->isFalse());
    }

    public function testAssertThatIsJson()
    {
        $this->assertThat('{}', $this->isJson());
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testAssertThatAnythingAndAnything()
    {
        $this->assertThat(
            'anything',
            $this->logicalAnd(
                $this->anything(),
                $this->anything()
            )
        );
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testAssertThatAnythingOrAnything()
    {
        $this->assertThat(
            'anything',
            $this->logicalOr(
                $this->anything(),
                $this->anything()
            )
        );
    }

    /**
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

    public function testAssertThatContains()
    {
        $this->assertThat(['foo'], $this->contains('foo'));
    }

    public function testAssertThatStringContains()
    {
        $this->assertThat('barfoobar', $this->stringContains('foo'));
    }

    public function testAssertThatContainsOnly()
    {
        $this->assertThat(['foo'], $this->containsOnly('string'));
    }

    public function testAssertThatContainsOnlyInstancesOf()
    {
        $this->assertThat([new \Book], $this->containsOnlyInstancesOf(\Book::class));
    }

    public function testAssertThatArrayHasKey()
    {
        $this->assertThat(['foo' => 'bar'], $this->arrayHasKey('foo'));
    }

    public function testAssertThatClassHasAttribute()
    {
        $this->assertThat(
            new \ClassWithNonPublicAttributes,
            $this->classHasAttribute('publicAttribute')
        );
    }

    public function testAssertThatClassHasStaticAttribute()
    {
        $this->assertThat(
            new \ClassWithNonPublicAttributes,
            $this->classHasStaticAttribute('publicStaticAttribute')
        );
    }

    public function testAssertThatObjectHasAttribute()
    {
        $this->assertThat(
            new \ClassWithNonPublicAttributes,
            $this->objectHasAttribute('publicAttribute')
        );
    }

    public function testAssertThatEqualTo()
    {
        $this->assertThat('foo', $this->equalTo('foo'));
    }

    public function testAssertThatIdenticalTo()
    {
        $value      = new \stdClass;
        $constraint = $this->identicalTo($value);

        $this->assertThat($value, $constraint);
    }

    public function testAssertThatIsInstanceOf()
    {
        $this->assertThat(new \stdClass, $this->isInstanceOf('StdClass'));
    }

    public function testAssertThatIsType()
    {
        $this->assertThat('string', $this->isType('string'));
    }

    public function testAssertThatIsEmpty()
    {
        $this->assertThat([], $this->isEmpty());
    }

    public function testAssertThatFileExists()
    {
        $this->assertThat(__FILE__, $this->fileExists());
    }

    public function testAssertThatGreaterThan()
    {
        $this->assertThat(2, $this->greaterThan(1));
    }

    public function testAssertThatGreaterThanOrEqual()
    {
        $this->assertThat(2, $this->greaterThanOrEqual(1));
    }

    public function testAssertThatLessThan()
    {
        $this->assertThat(1, $this->lessThan(2));
    }

    public function testAssertThatLessThanOrEqual()
    {
        $this->assertThat(1, $this->lessThanOrEqual(2));
    }

    public function testAssertThatMatchesRegularExpression()
    {
        $this->assertThat('foobar', $this->matchesRegularExpression('/foo/'));
    }

    public function testAssertThatCallback()
    {
        $this->assertThat(
            null,
            $this->callback(function ($other) {
                return true;
            })
        );
    }

    public function testAssertThatCountOf()
    {
        $this->assertThat([1], $this->countOf(1));
    }

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

    public function testAssertStringEqualsFile()
    {
        $this->assertStringEqualsFile(
            $this->filesDirectory . 'foo.xml',
            \file_get_contents($this->filesDirectory . 'foo.xml')
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertStringEqualsFile(
            $this->filesDirectory . 'foo.xml',
            \file_get_contents($this->filesDirectory . 'bar.xml')
        );
    }

    public function testAssertStringNotEqualsFile()
    {
        $this->assertStringNotEqualsFile(
            $this->filesDirectory . 'foo.xml',
            \file_get_contents($this->filesDirectory . 'bar.xml')
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertStringNotEqualsFile(
            $this->filesDirectory . 'foo.xml',
            \file_get_contents($this->filesDirectory . 'foo.xml')
        );
    }

    public function testAssertStringStartsWithThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertStringStartsWith(null, null);
    }

    public function testAssertStringStartsWithThrowsException2()
    {
        $this->expectException(Exception::class);

        $this->assertStringStartsWith('', null);
    }

    public function testAssertStringStartsNotWithThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertStringStartsNotWith(null, null);
    }

    public function testAssertStringStartsNotWithThrowsException2()
    {
        $this->expectException(Exception::class);

        $this->assertStringStartsNotWith('', null);
    }

    public function testAssertStringEndsWithThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertStringEndsWith(null, null);
    }

    public function testAssertStringEndsWithThrowsException2()
    {
        $this->expectException(Exception::class);

        $this->assertStringEndsWith('', null);
    }

    public function testAssertStringEndsNotWithThrowsException()
    {
        $this->expectException(Exception::class);

        $this->assertStringEndsNotWith(null, null);
    }

    public function testAssertStringEndsNotWithThrowsException2()
    {
        $this->expectException(Exception::class);

        $this->assertStringEndsNotWith('', null);
    }

    public function testAssertStringStartsWith()
    {
        $this->assertStringStartsWith('prefix', 'prefixfoo');

        $this->expectException(AssertionFailedError::class);

        $this->assertStringStartsWith('prefix', 'foo');
    }

    public function testAssertStringStartsNotWith()
    {
        $this->assertStringStartsNotWith('prefix', 'foo');

        $this->expectException(AssertionFailedError::class);

        $this->assertStringStartsNotWith('prefix', 'prefixfoo');
    }

    public function testAssertStringEndsWith()
    {
        $this->assertStringEndsWith('suffix', 'foosuffix');

        $this->expectException(AssertionFailedError::class);

        $this->assertStringEndsWith('suffix', 'foo');
    }

    public function testAssertStringEndsNotWith()
    {
        $this->assertStringEndsNotWith('suffix', 'foo');

        $this->expectException(AssertionFailedError::class);

        $this->assertStringEndsNotWith('suffix', 'foosuffix');
    }

    public function testAssertStringMatchesFormatRaisesExceptionForInvalidFirstArgument()
    {
        $this->expectException(Exception::class);

        $this->assertStringMatchesFormat(null, '');
    }

    public function testAssertStringMatchesFormatRaisesExceptionForInvalidSecondArgument()
    {
        $this->expectException(Exception::class);

        $this->assertStringMatchesFormat('', null);
    }

    public function testAssertStringMatchesFormat()
    {
        $this->assertStringMatchesFormat('*%s*', '***');
    }

    public function testAssertStringMatchesFormatFailure()
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertStringMatchesFormat('*%s*', '**');
    }

    public function testAssertStringNotMatchesFormatRaisesExceptionForInvalidFirstArgument()
    {
        $this->expectException(Exception::class);

        $this->assertStringNotMatchesFormat(null, '');
    }

    public function testAssertStringNotMatchesFormatRaisesExceptionForInvalidSecondArgument()
    {
        $this->expectException(Exception::class);

        $this->assertStringNotMatchesFormat('', null);
    }

    public function testAssertStringNotMatchesFormat()
    {
        $this->assertStringNotMatchesFormat('*%s*', '**');

        $this->expectException(AssertionFailedError::class);

        $this->assertStringMatchesFormat('*%s*', '**');
    }

    public function testAssertEmpty()
    {
        $this->assertEmpty([]);

        $this->expectException(AssertionFailedError::class);

        $this->assertEmpty(['foo']);
    }

    public function testAssertNotEmpty()
    {
        $this->assertNotEmpty(['foo']);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotEmpty([]);
    }

    public function testAssertAttributeEmpty()
    {
        $o    = new \stdClass;
        $o->a = [];

        $this->assertAttributeEmpty('a', $o);

        $o->a = ['b'];

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeEmpty('a', $o);
    }

    public function testAssertAttributeNotEmpty()
    {
        $o    = new \stdClass;
        $o->a = ['b'];

        $this->assertAttributeNotEmpty('a', $o);

        $o->a = [];

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotEmpty('a', $o);
    }

    public function testMarkTestIncomplete()
    {
        try {
            $this->markTestIncomplete('incomplete');
        } catch (IncompleteTestError $e) {
            $this->assertEquals('incomplete', $e->getMessage());

            return;
        }

        $this->fail();
    }

    public function testMarkTestSkipped()
    {
        try {
            $this->markTestSkipped('skipped');
        } catch (SkippedTestError $e) {
            $this->assertEquals('skipped', $e->getMessage());

            return;
        }

        $this->fail();
    }

    public function testAssertCount()
    {
        $this->assertCount(2, [1, 2]);

        $this->expectException(AssertionFailedError::class);

        $this->assertCount(2, [1, 2, 3]);
    }

    public function testAssertCountTraversable()
    {
        $this->assertCount(2, new \ArrayIterator([1, 2]));

        $this->expectException(AssertionFailedError::class);

        $this->assertCount(2, new \ArrayIterator([1, 2, 3]));
    }

    public function testAssertCountThrowsExceptionIfExpectedCountIsNoInteger()
    {
        try {
            $this->assertCount('a', []);
        } catch (Exception $e) {
            $this->assertEquals('Argument #1 (No Value) of PHPUnit\Framework\Assert::assertCount() must be a integer', $e->getMessage());

            return;
        }

        $this->fail();
    }

    public function testAssertCountThrowsExceptionIfElementIsNotCountable()
    {
        try {
            $this->assertCount(2, '');
        } catch (Exception $e) {
            $this->assertEquals('Argument #2 (No Value) of PHPUnit\Framework\Assert::assertCount() must be a countable or traversable', $e->getMessage());

            return;
        }

        $this->fail();
    }

    public function testAssertAttributeCount()
    {
        $o    = new \stdClass;
        $o->a = [];

        $this->assertAttributeCount(0, 'a', $o);
    }

    public function testAssertNotCount()
    {
        $this->assertNotCount(2, [1, 2, 3]);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotCount(2, [1, 2]);
    }

    public function testAssertNotCountThrowsExceptionIfExpectedCountIsNoInteger()
    {
        $this->expectException(Exception::class);

        $this->assertNotCount('a', []);
    }

    public function testAssertNotCountThrowsExceptionIfElementIsNotCountable()
    {
        $this->expectException(Exception::class);

        $this->assertNotCount(2, '');
    }

    public function testAssertAttributeNotCount()
    {
        $o    = new \stdClass;
        $o->a = [];

        $this->assertAttributeNotCount(1, 'a', $o);
    }

    public function testAssertSameSize()
    {
        $this->assertSameSize([1, 2], [3, 4]);

        $this->expectException(AssertionFailedError::class);

        $this->assertSameSize([1, 2], [1, 2, 3]);
    }

    public function testAssertSameSizeThrowsExceptionIfExpectedIsNotCountable()
    {
        try {
            $this->assertSameSize('a', []);
        } catch (Exception $e) {
            $this->assertEquals('Argument #1 (No Value) of PHPUnit\Framework\Assert::assertSameSize() must be a countable or traversable', $e->getMessage());

            return;
        }

        $this->fail();
    }

    public function testAssertSameSizeThrowsExceptionIfActualIsNotCountable()
    {
        try {
            $this->assertSameSize([], '');
        } catch (Exception $e) {
            $this->assertEquals('Argument #2 (No Value) of PHPUnit\Framework\Assert::assertSameSize() must be a countable or traversable', $e->getMessage());

            return;
        }

        $this->fail();
    }

    public function testAssertNotSameSize()
    {
        $this->assertNotSameSize([1, 2], [1, 2, 3]);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotSameSize([1, 2], [3, 4]);
    }

    public function testAssertNotSameSizeThrowsExceptionIfExpectedIsNotCountable()
    {
        $this->expectException(Exception::class);

        $this->assertNotSameSize('a', []);
    }

    public function testAssertNotSameSizeThrowsExceptionIfActualIsNotCountable()
    {
        $this->expectException(Exception::class);

        $this->assertNotSameSize([], '');
    }

    public function testAssertJsonRaisesExceptionForInvalidArgument()
    {
        $this->expectException(Exception::class);

        $this->assertJson(null);
    }

    public function testAssertJson()
    {
        $this->assertJson('{}');
    }

    public function testAssertJsonStringEqualsJsonString()
    {
        $expected = '{"Mascott" : "Tux"}';
        $actual   = '{"Mascott" : "Tux"}';
        $message  = 'Given Json strings do not match';

        $this->assertJsonStringEqualsJsonString($expected, $actual, $message);
    }

    /**
     * @dataProvider validInvalidJsonDataprovider
     */
    public function testAssertJsonStringEqualsJsonStringErrorRaised($expected, $actual)
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function testAssertJsonStringNotEqualsJsonString()
    {
        $expected = '{"Mascott" : "Beastie"}';
        $actual   = '{"Mascott" : "Tux"}';
        $message  = 'Given Json strings do match';

        $this->assertJsonStringNotEqualsJsonString($expected, $actual, $message);
    }

    /**
     * @dataProvider validInvalidJsonDataprovider
     */
    public function testAssertJsonStringNotEqualsJsonStringErrorRaised($expected, $actual)
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertJsonStringNotEqualsJsonString($expected, $actual);
    }

    public function testAssertJsonStringEqualsJsonFile()
    {
        $file    = __DIR__ . '/../_files/JsonData/simpleObject.json';
        $actual  = \json_encode(['Mascott' => 'Tux']);
        $message = '';

        $this->assertJsonStringEqualsJsonFile($file, $actual, $message);
    }

    public function testAssertJsonStringEqualsJsonFileExpectingExpectationFailedException()
    {
        $file    = __DIR__ . '/../_files/JsonData/simpleObject.json';
        $actual  = \json_encode(['Mascott' => 'Beastie']);
        $message = '';

        try {
            $this->assertJsonStringEqualsJsonFile($file, $actual, $message);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                'Failed asserting that \'{"Mascott":"Beastie"}\' matches JSON string "{"Mascott":"Tux"}".',
                $e->getMessage()
            );

            return;
        }

        $this->fail('Expected Exception not thrown.');
    }

    public function testAssertJsonStringEqualsJsonFileExpectingException()
    {
        $file = __DIR__ . '/../_files/JsonData/simpleObject.json';

        try {
            $this->assertJsonStringEqualsJsonFile($file, null);
        } catch (Exception $e) {
            return;
        }

        $this->fail('Expected Exception not thrown.');
    }

    public function testAssertJsonStringNotEqualsJsonFile()
    {
        $file    = __DIR__ . '/../_files/JsonData/simpleObject.json';
        $actual  = \json_encode(['Mascott' => 'Beastie']);
        $message = '';

        $this->assertJsonStringNotEqualsJsonFile($file, $actual, $message);
    }

    public function testAssertJsonStringNotEqualsJsonFileExpectingException()
    {
        $file = __DIR__ . '/../_files/JsonData/simpleObject.json';

        try {
            $this->assertJsonStringNotEqualsJsonFile($file, null);
        } catch (Exception $e) {
            return;
        }

        $this->fail('Expected exception not found.');
    }

    public function testAssertJsonFileNotEqualsJsonFile()
    {
        $fileExpected = __DIR__ . '/../_files/JsonData/simpleObject.json';
        $fileActual   = __DIR__ . '/../_files/JsonData/arrayObject.json';
        $message      = '';

        $this->assertJsonFileNotEqualsJsonFile($fileExpected, $fileActual, $message);
    }

    public function testAssertJsonFileEqualsJsonFile()
    {
        $file    = __DIR__ . '/../_files/JsonData/simpleObject.json';
        $message = '';

        $this->assertJsonFileEqualsJsonFile($file, $file, $message);
    }

    public function testAssertInstanceOf()
    {
        $this->assertInstanceOf(\stdClass::class, new \stdClass);

        $this->expectException(AssertionFailedError::class);

        $this->assertInstanceOf(\Exception::class, new \stdClass);
    }

    public function testAssertInstanceOfThrowsExceptionForInvalidArgument()
    {
        $this->expectException(Exception::class);

        $this->assertInstanceOf(null, new \stdClass);
    }

    public function testAssertAttributeInstanceOf()
    {
        $o    = new \stdClass;
        $o->a = new \stdClass;

        $this->assertAttributeInstanceOf(\stdClass::class, 'a', $o);
    }

    public function testAssertNotInstanceOf()
    {
        $this->assertNotInstanceOf(\Exception::class, new \stdClass);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotInstanceOf(\stdClass::class, new \stdClass);
    }

    public function testAssertNotInstanceOfThrowsExceptionForInvalidArgument()
    {
        $this->expectException(Exception::class);

        $this->assertNotInstanceOf(null, new \stdClass);
    }

    public function testAssertAttributeNotInstanceOf()
    {
        $o    = new \stdClass;
        $o->a = new \stdClass;

        $this->assertAttributeNotInstanceOf(\Exception::class, 'a', $o);
    }

    public function testAssertInternalType()
    {
        $this->assertInternalType('integer', 1);

        $this->expectException(AssertionFailedError::class);

        $this->assertInternalType('string', 1);
    }

    public function testAssertInternalTypeDouble()
    {
        $this->assertInternalType('double', 1.0);

        $this->expectException(AssertionFailedError::class);

        $this->assertInternalType('double', 1);
    }

    public function testAssertInternalTypeThrowsExceptionForInvalidArgument()
    {
        $this->expectException(Exception::class);

        $this->assertInternalType(null, 1);
    }

    public function testAssertAttributeInternalType()
    {
        $o    = new \stdClass;
        $o->a = 1;

        $this->assertAttributeInternalType('integer', 'a', $o);
    }

    public function testAssertNotInternalType()
    {
        $this->assertNotInternalType('string', 1);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotInternalType('integer', 1);
    }

    public function testAssertNotInternalTypeThrowsExceptionForInvalidArgument()
    {
        $this->expectException(Exception::class);

        $this->assertNotInternalType(null, 1);
    }

    public function testAssertAttributeNotInternalType()
    {
        $o    = new \stdClass;
        $o->a = 1;

        $this->assertAttributeNotInternalType('string', 'a', $o);
    }

    public function testAssertStringMatchesFormatFileThrowsExceptionForInvalidArgument()
    {
        $this->expectException(Exception::class);

        $this->assertStringMatchesFormatFile('not_existing_file', '');
    }

    public function testAssertStringMatchesFormatFileThrowsExceptionForInvalidArgument2()
    {
        $this->expectException(Exception::class);

        $this->assertStringMatchesFormatFile($this->filesDirectory . 'expectedFileFormat.txt', null);
    }

    public function testAssertStringMatchesFormatFile()
    {
        $this->assertStringMatchesFormatFile($this->filesDirectory . 'expectedFileFormat.txt', "FOO\n");

        $this->expectException(AssertionFailedError::class);

        $this->assertStringMatchesFormatFile($this->filesDirectory . 'expectedFileFormat.txt', "BAR\n");
    }

    public function testAssertStringNotMatchesFormatFileThrowsExceptionForInvalidArgument()
    {
        $this->expectException(Exception::class);

        $this->assertStringNotMatchesFormatFile('not_existing_file', '');
    }

    public function testAssertStringNotMatchesFormatFileThrowsExceptionForInvalidArgument2()
    {
        $this->expectException(Exception::class);

        $this->assertStringNotMatchesFormatFile($this->filesDirectory . 'expectedFileFormat.txt', null);
    }

    public function testAssertStringNotMatchesFormatFile()
    {
        $this->assertStringNotMatchesFormatFile($this->filesDirectory . 'expectedFileFormat.txt', "BAR\n");

        $this->expectException(AssertionFailedError::class);

        $this->assertStringNotMatchesFormatFile($this->filesDirectory . 'expectedFileFormat.txt', "FOO\n");
    }

    /**
     * @return array<string, string[]>
     */
    public static function validInvalidJsonDataprovider()
    {
        return [
            'error syntax in expected JSON' => ['{"Mascott"::}', '{"Mascott" : "Tux"}'],
            'error UTF-8 in actual JSON'    => ['{"Mascott" : "Tux"}', '{"Mascott" : :}'],
        ];
    }
}
