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
     * @return array<string, string[]>
     */
    public static function validInvalidJsonDataprovider()
    {
        return [
            'error syntax in expected JSON' => ['{"Mascott"::}', '{"Mascott" : "Tux"}'],
            'error UTF-8 in actual JSON'    => ['{"Mascott" : "Tux"}', '{"Mascott" : :}'],
        ];
    }

    public function testFail(): void
    {
        try {
            $this->fail();
        } catch (AssertionFailedError $e) {
            return;
        }

        throw new AssertionFailedError('Fail did not throw fail exception');
    }

    public function testAssertSplObjectStorageContainsObject(): void
    {
        $a = new \stdClass;
        $b = new \stdClass;
        $c = new \SplObjectStorage;
        $c->attach($a);

        $this->assertContains($a, $c);

        $this->expectException(AssertionFailedError::class);

        $this->assertContains($b, $c);
    }

    public function testAssertArrayContainsObject(): void
    {
        $a = new \stdClass;
        $b = new \stdClass;

        $this->assertContains($a, [$a]);

        $this->expectException(AssertionFailedError::class);

        $this->assertContains($a, [$b]);
    }

    public function testAssertArrayContainsString(): void
    {
        $this->assertContains('foo', ['foo']);

        $this->expectException(AssertionFailedError::class);

        $this->assertContains('foo', ['bar']);
    }

    public function testAssertArrayContainsNonObject(): void
    {
        $this->assertContains('foo', [true]);

        $this->expectException(AssertionFailedError::class);

        $this->assertContains('foo', [true], '', false, true, true);
    }

    public function testAssertContainsOnlyInstancesOf(): void
    {
        $test = [new \Book, new \Book];

        $this->assertContainsOnlyInstancesOf(\Book::class, $test);
        $this->assertContainsOnlyInstancesOf(\stdClass::class, [new \stdClass()]);

        $test2 = [new \Author('Test')];

        $this->expectException(AssertionFailedError::class);

        $this->assertContainsOnlyInstancesOf(\Book::class, $test2);
    }

    public function testAssertContainsPartialStringInString(): void
    {
        $this->assertContains('bar', 'foo bar');

        $this->expectException(AssertionFailedError::class);

        $this->assertContains('cake', 'foo bar');
    }

    public function testAssertContainsNonCaseSensitiveStringInString(): void
    {
        $this->assertContains('Foo', 'foo', '', true);

        $this->expectException(AssertionFailedError::class);

        $this->assertContains('Foo', 'foo', '', false);
    }

    public function testAssertContainsEmptyStringInString(): void
    {
        $this->assertContains('', 'test');
    }

    public function testAssertArrayHasKeyThrowsExceptionForInvalidFirstArgument(): void
    {
        $this->expectException(Exception::class);

        $this->assertArrayHasKey(null, []);
    }

    public function testAssertArrayHasKeyThrowsExceptionForInvalidSecondArgument(): void
    {
        $this->expectException(Exception::class);

        $this->assertArrayHasKey(0, null);
    }

    public function testAssertArrayHasIntegerKey(): void
    {
        $this->assertArrayHasKey(0, ['foo']);

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayHasKey(1, ['foo']);
    }

    public function testAssertArraySubset(): void
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

    public function testAssertArraySubsetWithDeepNestedArrays(): void
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

    public function testAssertArraySubsetWithNoStrictCheckAndObjects(): void
    {
        $obj       = new \stdClass;
        $reference = &$obj;
        $array     = ['a' => $obj];

        $this->assertArraySubset(['a' => $reference], $array);
        $this->assertArraySubset(['a' => new \stdClass], $array);
    }

    public function testAssertArraySubsetWithStrictCheckAndObjects(): void
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
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testAssertArraySubsetRaisesExceptionForInvalidArguments($partial, $subject): void
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

    public function testAssertArrayNotHasKeyThrowsExceptionForInvalidFirstArgument(): void
    {
        $this->expectException(Exception::class);

        $this->assertArrayNotHasKey(null, []);
    }

    public function testAssertArrayNotHasKeyThrowsExceptionForInvalidSecondArgument(): void
    {
        $this->expectException(Exception::class);

        $this->assertArrayNotHasKey(0, null);
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
        $array        = new \ArrayObject;
        $array['foo'] = 'bar';

        $this->assertArrayHasKey('foo', $array);
    }

    public function testAssertArrayHasKeyProperlyFailsWithArrayObjectValue(): void
    {
        $array        = new \ArrayObject;
        $array['bar'] = 'bar';

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayHasKey('foo', $array);
    }

    public function testAssertArrayHasKeyAcceptsArrayAccessValue(): void
    {
        $array        = new \SampleArrayAccess;
        $array['foo'] = 'bar';

        $this->assertArrayHasKey('foo', $array);
    }

    public function testAssertArrayHasKeyProperlyFailsWithArrayAccessValue(): void
    {
        $array        = new \SampleArrayAccess;
        $array['bar'] = 'bar';

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayHasKey('foo', $array);
    }

    public function testAssertArrayNotHasKeyAcceptsArrayAccessValue(): void
    {
        $array        = new \ArrayObject;
        $array['foo'] = 'bar';

        $this->assertArrayNotHasKey('bar', $array);
    }

    public function testAssertArrayNotHasKeyPropertlyFailsWithArrayAccessValue(): void
    {
        $array        = new \ArrayObject;
        $array['bar'] = 'bar';

        $this->expectException(AssertionFailedError::class);

        $this->assertArrayNotHasKey('bar', $array);
    }

    public function testAssertContainsThrowsException(): void
    {
        $this->expectException(Exception::class);

        $this->assertContains(null, null);
    }

    public function testAssertIteratorContainsObject(): void
    {
        $foo = new \stdClass;

        $this->assertContains($foo, new \TestIterator([$foo]));

        $this->expectException(AssertionFailedError::class);

        $this->assertContains($foo, new \TestIterator([new \stdClass]));
    }

    public function testAssertIteratorContainsString(): void
    {
        $this->assertContains('foo', new \TestIterator(['foo']));

        $this->expectException(AssertionFailedError::class);

        $this->assertContains('foo', new \TestIterator(['bar']));
    }

    public function testAssertStringContainsString(): void
    {
        $this->assertContains('foo', 'foobar');

        $this->expectException(AssertionFailedError::class);

        $this->assertContains('foo', 'bar');
    }

    public function testAssertStringContainsStringForUtf8(): void
    {
        $this->assertContains('oryginał', 'oryginał');

        $this->expectException(AssertionFailedError::class);

        $this->assertContains('ORYGINAŁ', 'oryginał');
    }

    public function testAssertStringContainsStringForUtf8WhenIgnoreCase(): void
    {
        $this->assertContains('oryginał', 'oryginał', '', true);
        $this->assertContains('ORYGINAŁ', 'oryginał', '', true);

        $this->expectException(AssertionFailedError::class);

        $this->assertContains('foo', 'oryginał', '', true);
    }

    public function testAssertNotContainsThrowsException(): void
    {
        $this->expectException(Exception::class);

        $this->assertNotContains(null, null);
    }

    public function testAssertSplObjectStorageNotContainsObject(): void
    {
        $a = new \stdClass;
        $b = new \stdClass;
        $c = new \SplObjectStorage;
        $c->attach($a);

        $this->assertNotContains($b, $c);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotContains($a, $c);
    }

    public function testAssertArrayNotContainsObject(): void
    {
        $a = new \stdClass;
        $b = new \stdClass;

        $this->assertNotContains($a, [$b]);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotContains($a, [$a]);
    }

    public function testAssertArrayNotContainsString(): void
    {
        $this->assertNotContains('foo', ['bar']);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotContains('foo', ['foo']);
    }

    public function testAssertArrayNotContainsNonObject(): void
    {
        $this->assertNotContains('foo', [true], '', false, true, true);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotContains('foo', [true]);
    }

    public function testAssertStringNotContainsString(): void
    {
        $this->assertNotContains('foo', 'bar');

        $this->expectException(AssertionFailedError::class);

        $this->assertNotContains('foo', 'foo');
    }

    public function testAssertStringNotContainsStringForUtf8(): void
    {
        $this->assertNotContains('ORYGINAŁ', 'oryginał');

        $this->expectException(AssertionFailedError::class);

        $this->assertNotContains('oryginał', 'oryginał');
    }

    public function testAssertStringNotContainsStringForUtf8WhenIgnoreCase(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertNotContains('ORYGINAŁ', 'oryginał', '', true);
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
        $this->assertContainsOnly('StdClass', [new \stdClass]);

        $this->expectException(AssertionFailedError::class);

        $this->assertContainsOnly('StdClass', ['StdClass']);
    }

    public function testAssertArrayNotContainsOnlyStdClass(): void
    {
        $this->assertNotContainsOnly('StdClass', ['StdClass']);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotContainsOnly('StdClass', [new \stdClass]);
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
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testAssertEqualsSucceeds($a, $b, $delta = 0.0, $canonicalize = false, $ignoreCase = false): void
    {
        $this->assertEquals($a, $b, '', $delta, 10, $canonicalize, $ignoreCase);
    }

    /**
     * @dataProvider notEqualProvider
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testAssertEqualsFails($a, $b, $delta = 0.0, $canonicalize = false, $ignoreCase = false): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertEquals($a, $b, '', $delta, 10, $canonicalize, $ignoreCase);
    }

    /**
     * @dataProvider notEqualProvider
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testAssertNotEqualsSucceeds($a, $b, $delta = 0.0, $canonicalize = false, $ignoreCase = false): void
    {
        $this->assertNotEquals($a, $b, '', $delta, 10, $canonicalize, $ignoreCase);
    }

    /**
     * @dataProvider equalProvider
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testAssertNotEqualsFails($a, $b, $delta = 0.0, $canonicalize = false, $ignoreCase = false): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertNotEquals($a, $b, '', $delta, 10, $canonicalize, $ignoreCase);
    }

    /**
     * @dataProvider sameProvider
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testAssertSameSucceeds($a, $b): void
    {
        $this->assertSame($a, $b);
    }

    /**
     * @dataProvider notSameProvider
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testAssertSameFails($a, $b): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertSame($a, $b);
    }

    /**
     * @dataProvider notSameProvider
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testAssertNotSameSucceeds($a, $b): void
    {
        $this->assertNotSame($a, $b);
    }

    /**
     * @dataProvider sameProvider
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testAssertNotSameFails($a, $b): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertNotSame($a, $b);
    }

    public function testAssertXmlFileEqualsXmlFile(): void
    {
        $this->assertXmlFileEqualsXmlFile(
            TEST_FILES_PATH . 'foo.xml',
            TEST_FILES_PATH . 'foo.xml'
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertXmlFileEqualsXmlFile(
            TEST_FILES_PATH . 'foo.xml',
            TEST_FILES_PATH . 'bar.xml'
        );
    }

    public function testAssertXmlFileNotEqualsXmlFile(): void
    {
        $this->assertXmlFileNotEqualsXmlFile(
            TEST_FILES_PATH . 'foo.xml',
            TEST_FILES_PATH . 'bar.xml'
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertXmlFileNotEqualsXmlFile(
            TEST_FILES_PATH . 'foo.xml',
            TEST_FILES_PATH . 'foo.xml'
        );
    }

    public function testAssertXmlStringEqualsXmlFile(): void
    {
        $this->assertXmlStringEqualsXmlFile(
            TEST_FILES_PATH . 'foo.xml',
            \file_get_contents(TEST_FILES_PATH . 'foo.xml')
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertXmlStringEqualsXmlFile(
            TEST_FILES_PATH . 'foo.xml',
            \file_get_contents(TEST_FILES_PATH . 'bar.xml')
        );
    }

    public function testXmlStringNotEqualsXmlFile(): void
    {
        $this->assertXmlStringNotEqualsXmlFile(
            TEST_FILES_PATH . 'foo.xml',
            \file_get_contents(TEST_FILES_PATH . 'bar.xml')
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertXmlStringNotEqualsXmlFile(
            TEST_FILES_PATH . 'foo.xml',
            \file_get_contents(TEST_FILES_PATH . 'foo.xml')
        );
    }

    public function testAssertXmlStringEqualsXmlString(): void
    {
        $this->assertXmlStringEqualsXmlString('<root/>', '<root/>');

        $this->expectException(AssertionFailedError::class);

        $this->assertXmlStringEqualsXmlString('<foo/>', '<bar/>');
    }

    /**
     * @ticket 1860
     */
    public function testAssertXmlStringEqualsXmlString2(): void
    {
        $this->expectException(Exception::class);

        $this->assertXmlStringEqualsXmlString('<a></b>', '<c></d>');
    }

    /**
     * @ticket 1860
     */
    public function testAssertXmlStringEqualsXmlString3(): void
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

    public function testAssertXmlStringNotEqualsXmlString(): void
    {
        $this->assertXmlStringNotEqualsXmlString('<foo/>', '<bar/>');

        $this->expectException(AssertionFailedError::class);

        $this->assertXmlStringNotEqualsXmlString('<root/>', '<root/>');
    }

    public function testXMLStructureIsSame(): void
    {
        $expected = new \DOMDocument;
        $expected->load(TEST_FILES_PATH . 'structureExpected.xml');

        $actual = new \DOMDocument;
        $actual->load(TEST_FILES_PATH . 'structureExpected.xml');

        $this->assertEqualXMLStructure(
            $expected->firstChild,
            $actual->firstChild,
            true
        );
    }

    public function testXMLStructureWrongNumberOfAttributes(): void
    {
        $expected = new \DOMDocument;
        $expected->load(TEST_FILES_PATH . 'structureExpected.xml');

        $actual = new \DOMDocument;
        $actual->load(TEST_FILES_PATH . 'structureWrongNumberOfAttributes.xml');

        $this->expectException(ExpectationFailedException::class);

        $this->assertEqualXMLStructure(
            $expected->firstChild,
            $actual->firstChild,
            true
        );
    }

    public function testXMLStructureWrongNumberOfNodes(): void
    {
        $expected = new \DOMDocument;
        $expected->load(TEST_FILES_PATH . 'structureExpected.xml');

        $actual = new \DOMDocument;
        $actual->load(TEST_FILES_PATH . 'structureWrongNumberOfNodes.xml');

        $this->expectException(ExpectationFailedException::class);

        $this->assertEqualXMLStructure(
            $expected->firstChild,
            $actual->firstChild,
            true
        );
    }

    public function testXMLStructureIsSameButDataIsNot(): void
    {
        $expected = new \DOMDocument;
        $expected->load(TEST_FILES_PATH . 'structureExpected.xml');

        $actual = new \DOMDocument;
        $actual->load(TEST_FILES_PATH . 'structureIsSameButDataIsNot.xml');

        $this->assertEqualXMLStructure(
            $expected->firstChild,
            $actual->firstChild,
            true
        );
    }

    public function testXMLStructureAttributesAreSameButValuesAreNot(): void
    {
        $expected = new \DOMDocument;
        $expected->load(TEST_FILES_PATH . 'structureExpected.xml');

        $actual = new \DOMDocument;
        $actual->load(TEST_FILES_PATH . 'structureAttributesAreSameButValuesAreNot.xml');

        $this->assertEqualXMLStructure(
            $expected->firstChild,
            $actual->firstChild,
            true
        );
    }

    public function testXMLStructureIgnoreTextNodes(): void
    {
        $expected = new \DOMDocument;
        $expected->load(TEST_FILES_PATH . 'structureExpected.xml');

        $actual = new \DOMDocument;
        $actual->load(TEST_FILES_PATH . 'structureIgnoreTextNodes.xml');

        $this->assertEqualXMLStructure(
            $expected->firstChild,
            $actual->firstChild,
            true
        );
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

        $this->assertIsReadable(__DIR__ . \DIRECTORY_SEPARATOR . 'NotExisting');
    }

    public function testAssertNotIsReadable(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertNotIsReadable(__FILE__);
    }

    public function testAssertIsWritable(): void
    {
        $this->assertIsWritable(__FILE__);

        $this->expectException(AssertionFailedError::class);

        $this->assertIsWritable(__DIR__ . \DIRECTORY_SEPARATOR . 'NotExisting');
    }

    public function testAssertNotIsWritable(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertNotIsWritable(__FILE__);
    }

    public function testAssertDirectoryExists(): void
    {
        $this->assertDirectoryExists(__DIR__);

        $this->expectException(AssertionFailedError::class);

        $this->assertDirectoryExists(__DIR__ . \DIRECTORY_SEPARATOR . 'NotExisting');
    }

    public function testAssertDirectoryNotExists(): void
    {
        $this->assertDirectoryNotExists(__DIR__ . \DIRECTORY_SEPARATOR . 'NotExisting');

        $this->expectException(AssertionFailedError::class);

        $this->assertDirectoryNotExists(__DIR__);
    }

    public function testAssertDirectoryIsReadable(): void
    {
        $this->assertDirectoryIsReadable(__DIR__);

        $this->expectException(AssertionFailedError::class);

        $this->assertDirectoryIsReadable(__DIR__ . \DIRECTORY_SEPARATOR . 'NotExisting');
    }

    public function testAssertDirectoryIsWritable(): void
    {
        $this->assertDirectoryIsWritable(__DIR__);

        $this->expectException(AssertionFailedError::class);

        $this->assertDirectoryIsWritable(__DIR__ . \DIRECTORY_SEPARATOR . 'NotExisting');
    }

    public function testAssertFileExists(): void
    {
        $this->assertFileExists(__FILE__);

        $this->expectException(AssertionFailedError::class);

        $this->assertFileExists(__DIR__ . \DIRECTORY_SEPARATOR . 'NotExisting');
    }

    public function testAssertFileNotExists(): void
    {
        $this->assertFileNotExists(__DIR__ . \DIRECTORY_SEPARATOR . 'NotExisting');

        $this->expectException(AssertionFailedError::class);

        $this->assertFileNotExists(__FILE__);
    }

    public function testAssertFileIsReadable(): void
    {
        $this->assertFileIsReadable(__FILE__);

        $this->expectException(AssertionFailedError::class);

        $this->assertFileIsReadable(__DIR__ . \DIRECTORY_SEPARATOR . 'NotExisting');
    }

    public function testAssertFileIsWritable(): void
    {
        $this->assertFileIsWritable(__FILE__);

        $this->expectException(AssertionFailedError::class);

        $this->assertFileIsWritable(__DIR__ . \DIRECTORY_SEPARATOR . 'NotExisting');
    }

    public function testAssertObjectHasAttribute(): void
    {
        $o = new \Author('Terry Pratchett');

        $this->assertObjectHasAttribute('name', $o);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectHasAttribute('foo', $o);
    }

    public function testAssertObjectNotHasAttribute(): void
    {
        $o = new \Author('Terry Pratchett');

        $this->assertObjectNotHasAttribute('foo', $o);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectNotHasAttribute('name', $o);
    }

    public function testAssertFinite(): void
    {
        $this->assertFinite(1);

        $this->expectException(AssertionFailedError::class);

        $this->assertFinite(\INF);
    }

    public function testAssertInfinite(): void
    {
        $this->assertInfinite(\INF);

        $this->expectException(AssertionFailedError::class);

        $this->assertInfinite(1);
    }

    public function testAssertNan(): void
    {
        $this->assertNan(\NAN);

        $this->expectException(AssertionFailedError::class);

        $this->assertNan(1);
    }

    public function testAssertNull(): void
    {
        $this->assertNull(null);

        $this->expectException(AssertionFailedError::class);

        $this->assertNull(new \stdClass);
    }

    public function testAssertNotNull(): void
    {
        $this->assertNotNull(new \stdClass);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotNull(null);
    }

    public function testAssertTrue(): void
    {
        $this->assertTrue(true);

        $this->expectException(AssertionFailedError::class);

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

    public function testAssertRegExp(): void
    {
        $this->assertRegExp('/foo/', 'foobar');

        $this->expectException(AssertionFailedError::class);

        $this->assertRegExp('/foo/', 'bar');
    }

    public function testAssertNotRegExp(): void
    {
        $this->assertNotRegExp('/foo/', 'bar');

        $this->expectException(AssertionFailedError::class);

        $this->assertNotRegExp('/foo/', 'foobar');
    }

    public function testAssertSame(): void
    {
        $o = new \stdClass;

        $this->assertSame($o, $o);

        $this->expectException(AssertionFailedError::class);

        $this->assertSame(new \stdClass, new \stdClass);
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

    public function testAttributeGreaterThan(): void
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

    public function testGreaterThanOrEqual(): void
    {
        $this->assertGreaterThanOrEqual(1, 2);

        $this->expectException(AssertionFailedError::class);

        $this->assertGreaterThanOrEqual(2, 1);
    }

    public function testAttributeGreaterThanOrEqual(): void
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

    public function testLessThan(): void
    {
        $this->assertLessThan(2, 1);

        try {
            $this->assertLessThan(1, 2);
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testAttributeLessThan(): void
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

    public function testLessThanOrEqual(): void
    {
        $this->assertLessThanOrEqual(2, 1);

        $this->expectException(AssertionFailedError::class);

        $this->assertLessThanOrEqual(1, 2);
    }

    public function testAttributeLessThanOrEqual(): void
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

    public function testReadAttribute(): void
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertEquals('foo', $this->readAttribute($obj, 'publicAttribute'));
        $this->assertEquals('bar', $this->readAttribute($obj, 'protectedAttribute'));
        $this->assertEquals('baz', $this->readAttribute($obj, 'privateAttribute'));
        $this->assertEquals('bar', $this->readAttribute($obj, 'protectedParentAttribute'));
        //$this->assertEquals('bar', $this->readAttribute($obj, 'privateParentAttribute'));
    }

    public function testReadAttribute2(): void
    {
        $this->assertEquals('foo', $this->readAttribute(\ClassWithNonPublicAttributes::class, 'publicStaticAttribute'));
        $this->assertEquals('bar', $this->readAttribute(\ClassWithNonPublicAttributes::class, 'protectedStaticAttribute'));
        $this->assertEquals('baz', $this->readAttribute(\ClassWithNonPublicAttributes::class, 'privateStaticAttribute'));
        $this->assertEquals('foo', $this->readAttribute(\ClassWithNonPublicAttributes::class, 'protectedStaticParentAttribute'));
        $this->assertEquals('foo', $this->readAttribute(\ClassWithNonPublicAttributes::class, 'privateStaticParentAttribute'));
    }

    public function testReadAttribute4(): void
    {
        $this->expectException(Exception::class);

        $this->readAttribute('NotExistingClass', 'foo');
    }

    public function testReadAttribute5(): void
    {
        $this->expectException(Exception::class);

        $this->readAttribute(null, 'foo');
    }

    public function testReadAttributeIfAttributeNameIsNotValid(): void
    {
        $this->expectException(Exception::class);

        $this->readAttribute(\stdClass::class, '2');
    }

    public function testGetStaticAttributeRaisesExceptionForInvalidFirstArgument2(): void
    {
        $this->expectException(Exception::class);

        $this->getStaticAttribute('NotExistingClass', 'foo');
    }

    public function testGetStaticAttributeRaisesExceptionForInvalidSecondArgument2(): void
    {
        $this->expectException(Exception::class);

        $this->getStaticAttribute(\stdClass::class, '0');
    }

    public function testGetStaticAttributeRaisesExceptionForInvalidSecondArgument3(): void
    {
        $this->expectException(Exception::class);

        $this->getStaticAttribute(\stdClass::class, 'foo');
    }

    public function testGetObjectAttributeRaisesExceptionForInvalidFirstArgument(): void
    {
        $this->expectException(Exception::class);

        $this->getObjectAttribute(null, 'foo');
    }

    public function testGetObjectAttributeRaisesExceptionForInvalidSecondArgument2(): void
    {
        $this->expectException(Exception::class);

        $this->getObjectAttribute(new \stdClass, '0');
    }

    public function testGetObjectAttributeRaisesExceptionForInvalidSecondArgument3(): void
    {
        $this->expectException(Exception::class);

        $this->getObjectAttribute(new \stdClass, 'foo');
    }

    public function testGetObjectAttributeWorksForInheritedAttributes(): void
    {
        $this->assertEquals(
            'bar',
            $this->getObjectAttribute(new \ClassWithNonPublicAttributes, 'privateParentAttribute')
        );
    }

    public function testAssertPublicAttributeContains(): void
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeContains('foo', 'publicArray', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeContains('bar', 'publicArray', $obj);
    }

    public function testAssertPublicAttributeContainsOnly(): void
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeContainsOnly('string', 'publicArray', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeContainsOnly('integer', 'publicArray', $obj);
    }

    public function testAssertPublicAttributeNotContains(): void
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeNotContains('bar', 'publicArray', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotContains('foo', 'publicArray', $obj);
    }

    public function testAssertPublicAttributeNotContainsOnly(): void
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeNotContainsOnly('integer', 'publicArray', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotContainsOnly('string', 'publicArray', $obj);
    }

    public function testAssertProtectedAttributeContains(): void
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeContains('bar', 'protectedArray', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeContains('foo', 'protectedArray', $obj);
    }

    public function testAssertProtectedAttributeNotContains(): void
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeNotContains('foo', 'protectedArray', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotContains('bar', 'protectedArray', $obj);
    }

    public function testAssertPrivateAttributeContains(): void
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeContains('baz', 'privateArray', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeContains('foo', 'privateArray', $obj);
    }

    public function testAssertPrivateAttributeNotContains(): void
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeNotContains('foo', 'privateArray', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotContains('baz', 'privateArray', $obj);
    }

    public function testAssertAttributeContainsNonObject(): void
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeContains(true, 'privateArray', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeContains(true, 'privateArray', $obj, '', false, true, true);
    }

    public function testAssertAttributeNotContainsNonObject(): void
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeNotContains(true, 'privateArray', $obj, '', false, true, true);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotContains(true, 'privateArray', $obj);
    }

    public function testAssertPublicAttributeEquals(): void
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeEquals('foo', 'publicAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeEquals('bar', 'publicAttribute', $obj);
    }

    public function testAssertPublicAttributeNotEquals(): void
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeNotEquals('bar', 'publicAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotEquals('foo', 'publicAttribute', $obj);
    }

    public function testAssertPublicAttributeSame(): void
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeSame('foo', 'publicAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeSame('bar', 'publicAttribute', $obj);
    }

    public function testAssertPublicAttributeNotSame(): void
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeNotSame('bar', 'publicAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotSame('foo', 'publicAttribute', $obj);
    }

    public function testAssertProtectedAttributeEquals(): void
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeEquals('bar', 'protectedAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeEquals('foo', 'protectedAttribute', $obj);
    }

    public function testAssertProtectedAttributeNotEquals(): void
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeNotEquals('foo', 'protectedAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotEquals('bar', 'protectedAttribute', $obj);
    }

    public function testAssertPrivateAttributeEquals(): void
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeEquals('baz', 'privateAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeEquals('foo', 'privateAttribute', $obj);
    }

    public function testAssertPrivateAttributeNotEquals(): void
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertAttributeNotEquals('foo', 'privateAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotEquals('baz', 'privateAttribute', $obj);
    }

    public function testAssertPublicStaticAttributeEquals(): void
    {
        $this->assertAttributeEquals('foo', 'publicStaticAttribute', \ClassWithNonPublicAttributes::class);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeEquals('bar', 'publicStaticAttribute', \ClassWithNonPublicAttributes::class);
    }

    public function testAssertPublicStaticAttributeNotEquals(): void
    {
        $this->assertAttributeNotEquals('bar', 'publicStaticAttribute', \ClassWithNonPublicAttributes::class);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotEquals('foo', 'publicStaticAttribute', \ClassWithNonPublicAttributes::class);
    }

    public function testAssertProtectedStaticAttributeEquals(): void
    {
        $this->assertAttributeEquals('bar', 'protectedStaticAttribute', \ClassWithNonPublicAttributes::class);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeEquals('foo', 'protectedStaticAttribute', \ClassWithNonPublicAttributes::class);
    }

    public function testAssertProtectedStaticAttributeNotEquals(): void
    {
        $this->assertAttributeNotEquals('foo', 'protectedStaticAttribute', \ClassWithNonPublicAttributes::class);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotEquals('bar', 'protectedStaticAttribute', \ClassWithNonPublicAttributes::class);
    }

    public function testAssertPrivateStaticAttributeEquals(): void
    {
        $this->assertAttributeEquals('baz', 'privateStaticAttribute', \ClassWithNonPublicAttributes::class);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeEquals('foo', 'privateStaticAttribute', \ClassWithNonPublicAttributes::class);
    }

    public function testAssertPrivateStaticAttributeNotEquals(): void
    {
        $this->assertAttributeNotEquals('foo', 'privateStaticAttribute', \ClassWithNonPublicAttributes::class);

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotEquals('baz', 'privateStaticAttribute', \ClassWithNonPublicAttributes::class);
    }

    public function testAssertClassHasAttributeThrowsExceptionIfAttributeNameIsNotValid(): void
    {
        $this->expectException(Exception::class);

        $this->assertClassHasAttribute('1', \ClassWithNonPublicAttributes::class);
    }

    public function testAssertClassNotHasAttributeThrowsExceptionIfAttributeNameIsNotValid(): void
    {
        $this->expectException(Exception::class);

        $this->assertClassNotHasAttribute('1', \ClassWithNonPublicAttributes::class);
    }

    public function testAssertClassHasStaticAttributeThrowsExceptionIfAttributeNameIsNotValid(): void
    {
        $this->expectException(Exception::class);

        $this->assertClassHasStaticAttribute('1', \ClassWithNonPublicAttributes::class);
    }

    public function testAssertClassNotHasStaticAttributeThrowsExceptionIfAttributeNameIsNotValid(): void
    {
        $this->expectException(Exception::class);

        $this->assertClassNotHasStaticAttribute('1', \ClassWithNonPublicAttributes::class);
    }

    public function testAssertObjectHasAttributeThrowsException2(): void
    {
        $this->expectException(Exception::class);

        $this->assertObjectHasAttribute('foo', null);
    }

    public function testAssertObjectHasAttributeThrowsExceptionIfAttributeNameIsNotValid(): void
    {
        $this->expectException(Exception::class);

        $this->assertObjectHasAttribute('1', \ClassWithNonPublicAttributes::class);
    }

    public function testAssertObjectNotHasAttributeThrowsException2(): void
    {
        $this->expectException(Exception::class);

        $this->assertObjectNotHasAttribute('foo', null);
    }

    public function testAssertObjectNotHasAttributeThrowsExceptionIfAttributeNameIsNotValid(): void
    {
        $this->expectException(Exception::class);

        $this->assertObjectNotHasAttribute('1', \ClassWithNonPublicAttributes::class);
    }

    public function testClassHasPublicAttribute(): void
    {
        $this->assertClassHasAttribute('publicAttribute', \ClassWithNonPublicAttributes::class);

        $this->expectException(AssertionFailedError::class);

        $this->assertClassHasAttribute('attribute', \ClassWithNonPublicAttributes::class);
    }

    public function testClassNotHasPublicAttribute(): void
    {
        $this->assertClassNotHasAttribute('attribute', \ClassWithNonPublicAttributes::class);

        $this->expectException(AssertionFailedError::class);

        $this->assertClassNotHasAttribute('publicAttribute', \ClassWithNonPublicAttributes::class);
    }

    public function testClassHasPublicStaticAttribute(): void
    {
        $this->assertClassHasStaticAttribute('publicStaticAttribute', \ClassWithNonPublicAttributes::class);

        $this->expectException(AssertionFailedError::class);

        $this->assertClassHasStaticAttribute('attribute', \ClassWithNonPublicAttributes::class);
    }

    public function testClassNotHasPublicStaticAttribute(): void
    {
        $this->assertClassNotHasStaticAttribute('attribute', \ClassWithNonPublicAttributes::class);

        $this->expectException(AssertionFailedError::class);

        $this->assertClassNotHasStaticAttribute('publicStaticAttribute', \ClassWithNonPublicAttributes::class);
    }

    public function testObjectHasPublicAttribute(): void
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertObjectHasAttribute('publicAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectHasAttribute('attribute', $obj);
    }

    public function testObjectNotHasPublicAttribute(): void
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertObjectNotHasAttribute('attribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectNotHasAttribute('publicAttribute', $obj);
    }

    public function testObjectHasOnTheFlyAttribute(): void
    {
        $obj      = new \stdClass;
        $obj->foo = 'bar';

        $this->assertObjectHasAttribute('foo', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectHasAttribute('bar', $obj);
    }

    public function testObjectNotHasOnTheFlyAttribute(): void
    {
        $obj      = new \stdClass;
        $obj->foo = 'bar';

        $this->assertObjectNotHasAttribute('bar', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectNotHasAttribute('foo', $obj);
    }

    public function testObjectHasProtectedAttribute(): void
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertObjectHasAttribute('protectedAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectHasAttribute('attribute', $obj);
    }

    public function testObjectNotHasProtectedAttribute(): void
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertObjectNotHasAttribute('attribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectNotHasAttribute('protectedAttribute', $obj);
    }

    public function testObjectHasPrivateAttribute(): void
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertObjectHasAttribute('privateAttribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectHasAttribute('attribute', $obj);
    }

    public function testObjectNotHasPrivateAttribute(): void
    {
        $obj = new \ClassWithNonPublicAttributes;

        $this->assertObjectNotHasAttribute('attribute', $obj);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectNotHasAttribute('privateAttribute', $obj);
    }

    public function testAssertThatAttributeEquals(): void
    {
        $this->assertThat(
            new \ClassWithNonPublicAttributes,
            $this->attribute(
                $this->equalTo('foo'),
                'publicAttribute'
            )
        );
    }

    public function testAssertThatAttributeEquals2(): void
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

    public function testAssertThatAttributeEqualTo(): void
    {
        $this->assertThat(
            new \ClassWithNonPublicAttributes,
            $this->attributeEqualTo('publicAttribute', 'foo')
        );
    }

    /**
     * @doesNotPerformAssertions
     */
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

    /**
     * @doesNotPerformAssertions
     */
    public function testAssertThatAnythingAndAnything(): void
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
    public function testAssertThatAnythingOrAnything(): void
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
    public function testAssertThatAnythingXorNotAnything(): void
    {
        $this->assertThat(
            'anything',
            $this->logicalXor(
                $this->anything(),
                $this->logicalNot($this->anything())
            )
        );
    }

    public function testAssertThatContains(): void
    {
        $this->assertThat(['foo'], $this->contains('foo'));
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
        $this->assertThat([new \Book], $this->containsOnlyInstancesOf(\Book::class));
    }

    public function testAssertThatArrayHasKey(): void
    {
        $this->assertThat(['foo' => 'bar'], $this->arrayHasKey('foo'));
    }

    public function testAssertThatClassHasAttribute(): void
    {
        $this->assertThat(
            new \ClassWithNonPublicAttributes,
            $this->classHasAttribute('publicAttribute')
        );
    }

    public function testAssertThatClassHasStaticAttribute(): void
    {
        $this->assertThat(
            new \ClassWithNonPublicAttributes,
            $this->classHasStaticAttribute('publicStaticAttribute')
        );
    }

    public function testAssertThatObjectHasAttribute(): void
    {
        $this->assertThat(
            new \ClassWithNonPublicAttributes,
            $this->objectHasAttribute('publicAttribute')
        );
    }

    public function testAssertThatEqualTo(): void
    {
        $this->assertThat('foo', $this->equalTo('foo'));
    }

    public function testAssertThatIdenticalTo(): void
    {
        $value      = new \stdClass;
        $constraint = $this->identicalTo($value);

        $this->assertThat($value, $constraint);
    }

    public function testAssertThatIsInstanceOf(): void
    {
        $this->assertThat(new \stdClass, $this->isInstanceOf('StdClass'));
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
            $this->callback(function ($other) {
                return true;
            })
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
            TEST_FILES_PATH . 'foo.xml'
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertFileEquals(
            TEST_FILES_PATH . 'foo.xml',
            TEST_FILES_PATH . 'bar.xml'
        );
    }

    public function testAssertFileNotEquals(): void
    {
        $this->assertFileNotEquals(
            TEST_FILES_PATH . 'foo.xml',
            TEST_FILES_PATH . 'bar.xml'
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertFileNotEquals(
            TEST_FILES_PATH . 'foo.xml',
            TEST_FILES_PATH . 'foo.xml'
        );
    }

    public function testAssertStringEqualsFile(): void
    {
        $this->assertStringEqualsFile(
            TEST_FILES_PATH . 'foo.xml',
            \file_get_contents(TEST_FILES_PATH . 'foo.xml')
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertStringEqualsFile(
            TEST_FILES_PATH . 'foo.xml',
            \file_get_contents(TEST_FILES_PATH . 'bar.xml')
        );
    }

    public function testAssertStringNotEqualsFile(): void
    {
        $this->assertStringNotEqualsFile(
            TEST_FILES_PATH . 'foo.xml',
            \file_get_contents(TEST_FILES_PATH . 'bar.xml')
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertStringNotEqualsFile(
            TEST_FILES_PATH . 'foo.xml',
            \file_get_contents(TEST_FILES_PATH . 'foo.xml')
        );
    }

    public function testAssertStringStartsNotWithThrowsException2(): void
    {
        $this->expectException(Exception::class);

        $this->assertStringStartsNotWith('', null);
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

    public function testAssertStringMatchesFormat(): void
    {
        $this->assertStringMatchesFormat('*%s*', '***');
    }

    public function testAssertStringMatchesFormatFailure(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertStringMatchesFormat('*%s*', '**');
    }

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

    public function testAssertNotEmpty(): void
    {
        $this->assertNotEmpty(['foo']);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotEmpty([]);
    }

    public function testAssertAttributeEmpty(): void
    {
        $o    = new \stdClass;
        $o->a = [];

        $this->assertAttributeEmpty('a', $o);

        $o->a = ['b'];

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeEmpty('a', $o);
    }

    public function testAssertAttributeNotEmpty(): void
    {
        $o    = new \stdClass;
        $o->a = ['b'];

        $this->assertAttributeNotEmpty('a', $o);

        $o->a = [];

        $this->expectException(AssertionFailedError::class);

        $this->assertAttributeNotEmpty('a', $o);
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
        } catch (SkippedTestError $e) {
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

    public function testAssertCountTraversable(): void
    {
        $this->assertCount(2, new \ArrayIterator([1, 2]));

        $this->expectException(AssertionFailedError::class);

        $this->assertCount(2, new \ArrayIterator([1, 2, 3]));
    }

    public function testAssertCountThrowsExceptionIfElementIsNotCountable(): void
    {
        try {
            $this->assertCount(2, '');
        } catch (Exception $e) {
            $this->assertEquals('Argument #2 (No Value) of PHPUnit\Framework\Assert::assertCount() must be a countable or iterable', $e->getMessage());

            return;
        }

        $this->fail();
    }

    public function testAssertAttributeCount(): void
    {
        $o    = new \stdClass;
        $o->a = [];

        $this->assertAttributeCount(0, 'a', $o);
    }

    public function testAssertNotCount(): void
    {
        $this->assertNotCount(2, [1, 2, 3]);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotCount(2, [1, 2]);
    }

    public function testAssertNotCountThrowsExceptionIfElementIsNotCountable(): void
    {
        $this->expectException(Exception::class);

        $this->assertNotCount(2, '');
    }

    public function testAssertAttributeNotCount(): void
    {
        $o    = new \stdClass;
        $o->a = [];

        $this->assertAttributeNotCount(1, 'a', $o);
    }

    public function testAssertSameSize(): void
    {
        $this->assertSameSize([1, 2], [3, 4]);

        $this->expectException(AssertionFailedError::class);

        $this->assertSameSize([1, 2], [1, 2, 3]);
    }

    public function testAssertSameSizeThrowsExceptionIfExpectedIsNotCountable(): void
    {
        try {
            $this->assertSameSize('a', []);
        } catch (Exception $e) {
            $this->assertEquals('Argument #1 (No Value) of PHPUnit\Framework\Assert::assertSameSize() must be a countable or iterable', $e->getMessage());

            return;
        }

        $this->fail();
    }

    public function testAssertSameSizeThrowsExceptionIfActualIsNotCountable(): void
    {
        try {
            $this->assertSameSize([], '');
        } catch (Exception $e) {
            $this->assertEquals('Argument #2 (No Value) of PHPUnit\Framework\Assert::assertSameSize() must be a countable or iterable', $e->getMessage());

            return;
        }

        $this->fail();
    }

    public function testAssertNotSameSize(): void
    {
        $this->assertNotSameSize([1, 2], [1, 2, 3]);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotSameSize([1, 2], [3, 4]);
    }

    public function testAssertNotSameSizeThrowsExceptionIfExpectedIsNotCountable(): void
    {
        $this->expectException(Exception::class);

        $this->assertNotSameSize('a', []);
    }

    public function testAssertNotSameSizeThrowsExceptionIfActualIsNotCountable(): void
    {
        $this->expectException(Exception::class);

        $this->assertNotSameSize([], '');
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

    /**
     * @dataProvider validInvalidJsonDataprovider
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testAssertJsonStringEqualsJsonStringErrorRaised($expected, $actual): void
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

    /**
     * @dataProvider validInvalidJsonDataprovider
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testAssertJsonStringNotEqualsJsonStringErrorRaised($expected, $actual): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertJsonStringNotEqualsJsonString($expected, $actual);
    }

    public function testAssertJsonStringEqualsJsonFile(): void
    {
        $file    = TEST_FILES_PATH . 'JsonData/simpleObject.json';
        $actual  = \json_encode(['Mascott' => 'Tux']);
        $message = '';

        $this->assertJsonStringEqualsJsonFile($file, $actual, $message);
    }

    public function testAssertJsonStringEqualsJsonFileExpectingExpectationFailedException(): void
    {
        $file    = TEST_FILES_PATH . 'JsonData/simpleObject.json';
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

    public function testAssertJsonStringNotEqualsJsonFile(): void
    {
        $file    = TEST_FILES_PATH . 'JsonData/simpleObject.json';
        $actual  = \json_encode(['Mascott' => 'Beastie']);
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

    public function testAssertInstanceOf(): void
    {
        $this->assertInstanceOf(\stdClass::class, new \stdClass);

        $this->expectException(AssertionFailedError::class);

        $this->assertInstanceOf(\Exception::class, new \stdClass);
    }

    public function testAssertAttributeInstanceOf(): void
    {
        $o    = new \stdClass;
        $o->a = new \stdClass;

        $this->assertAttributeInstanceOf(\stdClass::class, 'a', $o);
    }

    public function testAssertNotInstanceOf(): void
    {
        $this->assertNotInstanceOf(\Exception::class, new \stdClass);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotInstanceOf(\stdClass::class, new \stdClass);
    }

    public function testAssertAttributeNotInstanceOf(): void
    {
        $o    = new \stdClass;
        $o->a = new \stdClass;

        $this->assertAttributeNotInstanceOf(\Exception::class, 'a', $o);
    }

    public function testAssertInternalType(): void
    {
        $this->assertInternalType('integer', 1);

        $this->expectException(AssertionFailedError::class);

        $this->assertInternalType('string', 1);
    }

    public function testAssertInternalTypeDouble(): void
    {
        $this->assertInternalType('double', 1.0);

        $this->expectException(AssertionFailedError::class);

        $this->assertInternalType('double', 1);
    }

    public function testAssertAttributeInternalType(): void
    {
        $o    = new \stdClass;
        $o->a = 1;

        $this->assertAttributeInternalType('integer', 'a', $o);
    }

    public function testAssertNotInternalType(): void
    {
        $this->assertNotInternalType('string', 1);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotInternalType('integer', 1);
    }

    public function testAssertAttributeNotInternalType(): void
    {
        $o    = new \stdClass;
        $o->a = 1;

        $this->assertAttributeNotInternalType('string', 'a', $o);
    }

    public function testAssertStringMatchesFormatFileThrowsExceptionForInvalidArgument(): void
    {
        $this->expectException(Exception::class);

        $this->assertStringMatchesFormatFile('not_existing_file', '');
    }

    public function testAssertStringMatchesFormatFile(): void
    {
        $this->assertStringMatchesFormatFile(TEST_FILES_PATH . 'expectedFileFormat.txt', "FOO\n");

        $this->expectException(AssertionFailedError::class);

        $this->assertStringMatchesFormatFile(TEST_FILES_PATH . 'expectedFileFormat.txt', "BAR\n");
    }

    public function testAssertStringNotMatchesFormatFileThrowsExceptionForInvalidArgument(): void
    {
        $this->expectException(Exception::class);

        $this->assertStringNotMatchesFormatFile('not_existing_file', '');
    }

    public function testAssertStringNotMatchesFormatFile(): void
    {
        $this->assertStringNotMatchesFormatFile(TEST_FILES_PATH . 'expectedFileFormat.txt', "BAR\n");

        $this->expectException(AssertionFailedError::class);

        $this->assertStringNotMatchesFormatFile(TEST_FILES_PATH . 'expectedFileFormat.txt', "FOO\n");
    }

    protected function sameValues()
    {
        $object   = new \SampleClass(4, 8, 15);
        $file     = TEST_FILES_PATH . 'foo.xml';
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
            [new \Struct(2.3), new \Struct(4.2), 0.5],
            [[new \Struct(2.3)], [new \Struct(4.2)], 0.5],
            // NAN
            [\NAN, \NAN],
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
}
