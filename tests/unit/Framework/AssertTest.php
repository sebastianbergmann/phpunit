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

use PHPUnit\Util\Xml;

/**
 * @small
 */
final class AssertTest extends TestCase
{
    public static function validInvalidJsonDataprovider(): array
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

    public function testAssertContainsOnlyInstancesOf(): void
    {
        $test = [new \Book, new \Book];

        $this->assertContainsOnlyInstancesOf(\Book::class, $test);
        $this->assertContainsOnlyInstancesOf(\stdClass::class, [new \stdClass]);

        $test2 = [new \Author('Test')];

        $this->expectException(AssertionFailedError::class);

        $this->assertContainsOnlyInstancesOf(\Book::class, $test2);
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

    public function equalProvider(): array
    {
        // same |= equal
        return \array_merge($this->equalValues(), $this->sameValues());
    }

    public function notEqualProvider()
    {
        return $this->notEqualValues();
    }

    public function sameProvider(): array
    {
        return $this->sameValues();
    }

    public function notSameProvider(): array
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
    public function testAssertEqualsSucceeds($a, $b): void
    {
        $this->assertEquals($a, $b);
    }

    /**
     * @dataProvider notEqualProvider
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testAssertEqualsFails($a, $b): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertEquals($a, $b);
    }

    /**
     * @dataProvider notEqualProvider
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testAssertNotEqualsSucceeds($a, $b): void
    {
        $this->assertNotEquals($a, $b);
    }

    /**
     * @testdox assertNotEquals($a, $b) with delta $delta, canoicalize $canonicalize, ignoreCase $ignoreCase
     * @dataProvider equalProvider
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testAssertNotEqualsFails($a, $b): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertNotEquals($a, $b);
    }

    /**
     * @testdox assertNotSame($a, $b) fails
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
     * @testdox assertNotSame($a, $b)
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
     * @testdox assertSame($a, $b) fails
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
     * @testdox assertSame($a, $b)
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
        $this->assertNotIsReadable(__DIR__ . \DIRECTORY_SEPARATOR . 'NotExisting');

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
        $this->assertNotIsWritable(__DIR__ . \DIRECTORY_SEPARATOR . 'NotExisting');

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

    public function testAssertObjectHasAttributeNumericAttribute(): void
    {
        $object           = new \stdClass;
        $object->{'2020'} = 'Tokyo';

        $this->assertObjectHasAttribute('2020', $object);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectHasAttribute('2018', $object);
    }

    public function testAssertObjectHasAttributeMultiByteAttribute(): void
    {
        $object         = new \stdClass;
        $object->{'東京'} = 2020;

        $this->assertObjectHasAttribute('東京', $object);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectHasAttribute('長野', $object);
    }

    public function testAssertObjectNotHasAttribute(): void
    {
        $o = new \Author('Terry Pratchett');

        $this->assertObjectNotHasAttribute('foo', $o);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectNotHasAttribute('name', $o);
    }

    public function testAssertObjectNotHasAttributeNumericAttribute(): void
    {
        $object           = new \stdClass;
        $object->{'2020'} = 'Tokyo';

        $this->assertObjectNotHasAttribute('2018', $object);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectNotHasAttribute('2020', $object);
    }

    public function testAssertObjectNotHasAttributeMultiByteAttribute(): void
    {
        $object         = new \stdClass;
        $object->{'東京'} = 2020;

        $this->assertObjectNotHasAttribute('長野', $object);

        $this->expectException(AssertionFailedError::class);

        $this->assertObjectNotHasAttribute('東京', $object);
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
        } catch (AssertionFailedError $e) {
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

    public function testAssertClassHasAttributeThrowsExceptionIfAttributeNameIsNotValid(): void
    {
        $this->expectException(Exception::class);

        $this->assertClassHasAttribute('1', \ClassWithNonPublicAttributes::class);
    }

    public function testAssertClassHasAttributeThrowsExceptionIfClassDoesNotExist(): void
    {
        $this->expectException(Exception::class);

        $this->assertClassHasAttribute('attribute', 'ClassThatDoesNotExist');
    }

    public function testAssertClassNotHasAttributeThrowsExceptionIfAttributeNameIsNotValid(): void
    {
        $this->expectException(Exception::class);

        $this->assertClassNotHasAttribute('1', \ClassWithNonPublicAttributes::class);
    }

    public function testAssertClassNotHasAttributeThrowsExceptionIfClassDoesNotExist(): void
    {
        $this->expectException(Exception::class);

        $this->assertClassNotHasAttribute('attribute', 'ClassThatDoesNotExist');
    }

    public function testAssertClassHasStaticAttributeThrowsExceptionIfAttributeNameIsNotValid(): void
    {
        $this->expectException(Exception::class);

        $this->assertClassHasStaticAttribute('1', \ClassWithNonPublicAttributes::class);
    }

    public function testAssertClassHasStaticAttributeThrowsExceptionIfClassDoesNotExist(): void
    {
        $this->expectException(Exception::class);

        $this->assertClassHasStaticAttribute('attribute', 'ClassThatDoesNotExist');
    }

    public function testAssertClassNotHasStaticAttributeThrowsExceptionIfAttributeNameIsNotValid(): void
    {
        $this->expectException(Exception::class);

        $this->assertClassNotHasStaticAttribute('1', \ClassWithNonPublicAttributes::class);
    }

    public function testAssertClassNotHasStaticAttributeThrowsExceptionIfClassDoesNotExist(): void
    {
        $this->expectException(Exception::class);

        $this->assertClassNotHasStaticAttribute('attribute', 'ClassThatDoesNotExist');
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
            $this->assertEquals('Argument #2 of PHPUnit\Framework\Assert::assertCount() must be a countable or iterable', $e->getMessage());

            return;
        }

        $this->fail();
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
            $this->assertEquals('Argument #1 of PHPUnit\Framework\Assert::assertSameSize() must be a countable or iterable', $e->getMessage());

            return;
        }

        $this->fail();
    }

    public function testAssertSameSizeThrowsExceptionIfActualIsNotCountable(): void
    {
        try {
            $this->assertSameSize([], '');
        } catch (Exception $e) {
            $this->assertEquals('Argument #2 of PHPUnit\Framework\Assert::assertSameSize() must be a countable or iterable', $e->getMessage());

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

    /**
     * @testdox Assert JSON
     */
    public function testAssertJson(): void
    {
        $this->assertJson('{}');
    }

    /**
     * @testdox Assert JSON string equals JSON string
     */
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
     * @testdox Assert JSON string equals equals JSON string raised $_dataName
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

    public function testAssertInstanceOfThrowsExceptionIfTypeDoesNotExist(): void
    {
        $this->expectException(Exception::class);

        $this->assertInstanceOf('ClassThatDoesNotExist', new \stdClass);
    }

    public function testAssertInstanceOf(): void
    {
        $this->assertInstanceOf(\stdClass::class, new \stdClass);

        $this->expectException(AssertionFailedError::class);

        $this->assertInstanceOf(\Exception::class, new \stdClass);
    }

    public function testAssertNotInstanceOfThrowsExceptionIfTypeDoesNotExist(): void
    {
        $this->expectException(Exception::class);

        $this->assertNotInstanceOf('ClassThatDoesNotExist', new \stdClass);
    }

    public function testAssertNotInstanceOf(): void
    {
        $this->assertNotInstanceOf(\Exception::class, new \stdClass);

        $this->expectException(AssertionFailedError::class);

        $this->assertNotInstanceOf(\stdClass::class, new \stdClass);
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
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testBoolTypeCanBeAsserted(): void
    {
        $this->assertIsBool(true);

        try {
            $this->assertIsBool(null);
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testFloatTypeCanBeAsserted(): void
    {
        $this->assertIsFloat(0.0);

        try {
            $this->assertIsFloat(null);
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testIntTypeCanBeAsserted(): void
    {
        $this->assertIsInt(1);

        try {
            $this->assertIsInt(null);
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testNumericTypeCanBeAsserted(): void
    {
        $this->assertIsNumeric('1.0');

        try {
            $this->assertIsNumeric('abc');
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testObjectTypeCanBeAsserted(): void
    {
        $this->assertIsObject(new \stdClass);

        try {
            $this->assertIsObject(null);
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testResourceTypeCanBeAsserted(): void
    {
        $this->assertIsResource(\fopen(__FILE__, 'r'));

        try {
            $this->assertIsResource(null);
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testStringTypeCanBeAsserted(): void
    {
        $this->assertIsString('');

        try {
            $this->assertIsString(null);
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testScalarTypeCanBeAsserted(): void
    {
        $this->assertIsScalar(true);

        try {
            $this->assertIsScalar(new \stdClass);
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testCallableTypeCanBeAsserted(): void
    {
        $this->assertIsCallable(function (): void {
        });

        try {
            $this->assertIsCallable(null);
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testIterableTypeCanBeAsserted(): void
    {
        $this->assertIsIterable([]);

        try {
            $this->assertIsIterable(null);
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testNotArrayTypeCanBeAsserted(): void
    {
        $this->assertIsNotArray(null);

        try {
            $this->assertIsNotArray([]);
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testNotBoolTypeCanBeAsserted(): void
    {
        $this->assertIsNotBool(null);

        try {
            $this->assertIsNotBool(true);
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testNotFloatTypeCanBeAsserted(): void
    {
        $this->assertIsNotFloat(null);

        try {
            $this->assertIsNotFloat(0.0);
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testNotIntTypeCanBeAsserted(): void
    {
        $this->assertIsNotInt(null);

        try {
            $this->assertIsNotInt(1);
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testNotNumericTypeCanBeAsserted(): void
    {
        $this->assertIsNotNumeric('abc');

        try {
            $this->assertIsNotNumeric('1.0');
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testNotObjectTypeCanBeAsserted(): void
    {
        $this->assertIsNotObject(null);

        try {
            $this->assertIsNotObject(new \stdClass);
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testNotResourceTypeCanBeAsserted(): void
    {
        $this->assertIsNotResource(null);

        try {
            $this->assertIsNotResource(\fopen(__FILE__, 'r'));
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testNotScalarTypeCanBeAsserted(): void
    {
        $this->assertIsNotScalar(new \stdClass);

        try {
            $this->assertIsNotScalar(true);
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testNotStringTypeCanBeAsserted(): void
    {
        $this->assertIsNotString(null);

        try {
            $this->assertIsNotString('');
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testNotCallableTypeCanBeAsserted(): void
    {
        $this->assertIsNotCallable(null);

        try {
            $this->assertIsNotCallable(function (): void {
            });
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testNotIterableTypeCanBeAsserted(): void
    {
        $this->assertIsNotIterable(null);

        try {
            $this->assertIsNotIterable([]);
        } catch (AssertionFailedError $e) {
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
                $this->isTrue()
            )
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertThat(
            true,
            $this->logicalAnd(
                $this->isTrue(),
                $this->isFalse()
            )
        );
    }

    public function testLogicalOr(): void
    {
        $this->assertThat(
            true,
            $this->logicalOr(
                $this->isTrue(),
                $this->isFalse()
            )
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertThat(
            true,
            $this->logicalOr(
                $this->isFalse(),
                $this->isFalse()
            )
        );
    }

    public function testLogicalXor(): void
    {
        $this->assertThat(
            true,
            $this->logicalXor(
                $this->isTrue(),
                $this->isFalse()
            )
        );

        $this->expectException(AssertionFailedError::class);

        $this->assertThat(
            true,
            $this->logicalXor(
                $this->isTrue(),
                $this->isTrue()
            )
        );
    }

    public function testStringContainsStringCanBeAsserted(): void
    {
        $this->assertStringContainsString('bar', 'foobarbaz');

        try {
            $this->assertStringContainsString('barbara', 'foobarbaz');
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testStringNotContainsStringCanBeAsserted(): void
    {
        $this->assertStringNotContainsString('barbara', 'foobarbaz');

        try {
            $this->assertStringNotContainsString('bar', 'foobarbaz');
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testStringContainsStringCanBeAssertedIgnoringCase(): void
    {
        $this->assertStringContainsStringIgnoringCase('BAR', 'foobarbaz');

        try {
            $this->assertStringContainsStringIgnoringCase('BARBARA', 'foobarbaz');
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testStringNotContainsStringCanBeAssertedIgnoringCase(): void
    {
        $this->assertStringNotContainsStringIgnoringCase('BARBARA', 'foobarbaz');

        try {
            $this->assertStringNotContainsStringIgnoringCase('BAR', 'foobarbaz');
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testIterableContainsSameObjectCanBeAsserted(): void
    {
        $object   = new \stdClass;
        $iterable = [$object];

        $this->assertContains($object, $iterable);

        try {
            $this->assertContains(new \stdClass, $iterable);
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testIterableNotContainsSameObjectCanBeAsserted(): void
    {
        $object   = new \stdClass;
        $iterable = [$object];

        $this->assertNotContains(new \stdClass, $iterable);

        try {
            $this->assertNotContains($object, $iterable);
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testIterableContainsEqualObjectCanBeAsserted(): void
    {
        $a      = new \stdClass;
        $a->foo = 'bar';

        $b      = new \stdClass;
        $b->foo = 'baz';

        $this->assertContainsEquals($a, [$a]);

        try {
            $this->assertContainsEquals($b, [$a]);
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    public function testIterableNotContainsEqualObjectCanBeAsserted(): void
    {
        $a      = new \stdClass;
        $a->foo = 'bar';

        $b      = new \stdClass;
        $b->foo = 'baz';

        $this->assertNotContainsEquals($b, [$a]);

        try {
            $this->assertNotContainsEquals($a, [$a]);
        } catch (AssertionFailedError $e) {
            return;
        }

        $this->fail();
    }

    protected function sameValues(): array
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

    protected function notEqualValues(): array
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
                3500,
            ],
            [
                new \DateTime('2013-03-29 04:13:35', new \DateTimeZone('America/New_York')),
                new \DateTime('2013-03-29 05:13:35', new \DateTimeZone('America/New_York')),
                3500,
            ],
            [
                new \DateTime('2013-03-29', new \DateTimeZone('America/New_York')),
                new \DateTime('2013-03-30', new \DateTimeZone('America/New_York')),
            ],
            [
                new \DateTime('2013-03-29', new \DateTimeZone('America/New_York')),
                new \DateTime('2013-03-30', new \DateTimeZone('America/New_York')),
                43200,
            ],
            [
                new \DateTime('2013-03-29 04:13:35', new \DateTimeZone('America/New_York')),
                new \DateTime('2013-03-29 04:13:35', new \DateTimeZone('America/Chicago')),
            ],
            [
                new \DateTime('2013-03-29 04:13:35', new \DateTimeZone('America/New_York')),
                new \DateTime('2013-03-29 04:13:35', new \DateTimeZone('America/Chicago')),
                3500,
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
            [\acos(8), 3],
        ];
    }

    protected function equalValues(): array
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
                new \DateTime('2013-03-29', new \DateTimeZone('America/New_York')),
                new \DateTime('2013-03-29', new \DateTimeZone('America/New_York')),
            ],
            [
                new \DateTime('2013-03-29 04:13:35', new \DateTimeZone('America/New_York')),
                new \DateTime('2013-03-29 03:13:35', new \DateTimeZone('America/Chicago')),
            ],
            [
                new \DateTime('2013-03-30', new \DateTimeZone('America/New_York')),
                new \DateTime('2013-03-29 23:00:00', new \DateTimeZone('America/Chicago')),
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
