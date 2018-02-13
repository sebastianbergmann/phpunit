<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Util;

use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;

class XmlTest extends TestCase
{
    /**
     * @dataProvider charProvider
     *
     * @param mixed $char
     *
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testPrepareString($char): void
    {
        $e = null;

        $escapedString = Xml::prepareString($char);
        $xml           = "<?xml version='1.0' encoding='UTF-8' ?><tag>$escapedString</tag>";
        $dom           = new \DOMDocument('1.0', 'UTF-8');

        try {
            $dom->loadXML($xml);
        } catch (Exception $e) {
        }

        $this->assertNull($e, \sprintf(
            'PHPUnit_Util_XML::prepareString("\x%02x") should not crash DomDocument',
            \ord($char)
        ));
    }

    public function charProvider()
    {
        $data = [];

        for ($i = 0; $i < 256; $i++) {
            $data[] = [\chr($i)];
        }

        return $data;
    }

    public function testLoadEmptyString(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Could not load XML from empty string');

        Xml::load('');
    }

    public function testLoadArray(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Could not load XML from array');

        Xml::load([1, 2, 3]);
    }

    public function testLoadBoolean(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Could not load XML from boolean');

        Xml::load(false);
    }

    public function testNestedXmlToVariable(): void
    {
        $xml = '<array><element key="a"><array><element key="b"><string>foo</string></element></array></element><element key="c"><string>bar</string></element></array>';
        $dom = new \DOMDocument;
        $dom->loadXML($xml);

        $expected = [
            'a' => [
                'b' => 'foo',
            ],
            'c' => 'bar',
        ];

        $actual = Xml::xmlToVariable($dom->documentElement);

        $this->assertSame($expected, $actual);
    }

    public function testXmlToVariableCanHandleMultipleOfTheSameArgumentType(): void
    {
        $xml = '<object class="SampleClass"><arguments><string>a</string><string>b</string><string>c</string></arguments></object>';
        $dom = new \DOMDocument();
        $dom->loadXML($xml);

        $expected = ['a' => 'a', 'b' => 'b', 'c' => 'c'];

        $actual = Xml::xmlToVariable($dom->documentElement);

        $this->assertSame($expected, (array) $actual);
    }

    public function testXmlToVariableCanConstructObjectsWithConstructorArgumentsRecursively(): void
    {
        $xml = '<object class="Exception"><arguments><string>one</string><integer>0</integer><object class="Exception"><arguments><string>two</string></arguments></object></arguments></object>';
        $dom = new \DOMDocument();
        $dom->loadXML($xml);

        $actual = Xml::xmlToVariable($dom->documentElement);

        $this->assertEquals('one', $actual->getMessage());
        $this->assertEquals('two', $actual->getPrevious()->getMessage());
    }
}
