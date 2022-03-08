<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use function chr;
use function ord;
use function sprintf;
use DOMDocument;
use PHPUnit\Framework\TestCase;

/**
 * @small
 *
 * @covers \PHPUnit\Util\Xml
 * @covers \PHPUnit\Util\Xml\ValidationResult
 */
final class XmlTest extends TestCase
{
    /**
     * @dataProvider charProvider
     */
    public function testPrepareString(string $char): void
    {
        $e = null;

        $escapedString = Xml::prepareString($char);
        $xml           = "<?xml version='1.0' encoding='UTF-8' ?><tag>{$escapedString}</tag>";
        $dom           = new DOMDocument('1.0', 'UTF-8');

        try {
            $dom->loadXML($xml);
        } catch (Exception $e) {
        }

        $this->assertNull(
            $e,
            sprintf(
                '%s::prepareString("\x%02x") should not crash %s',
                Xml::class,
                ord($char),
                DOMDocument::class
            )
        );
    }

    public function charProvider(): array
    {
        $data = [];

        for ($i = 0; $i < 256; $i++) {
            $data[] = [chr($i)];
        }

        return $data;
    }

    /**
     * @testdox Nested xmlToVariable()
     */
    public function testNestedXmlToVariable(): void
    {
        $xml = '<array><element key="a"><array><element key="b"><string>foo</string></element></array></element><element key="c"><string>bar</string></element></array>';
        $dom = new DOMDocument;
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

    /**
     * @testdox xmlToVariable() can handle multiple of the same argument type
     */
    public function testXmlToVariableCanHandleMultipleOfTheSameArgumentType(): void
    {
        $xml = '<object class="PHPUnit\TestFixture\SampleClass"><arguments><string>a</string><string>b</string><string>c</string></arguments></object>';
        $dom = new DOMDocument;
        $dom->loadXML($xml);

        $expected = ['a' => 'a', 'b' => 'b', 'c' => 'c'];

        $actual = Xml::xmlToVariable($dom->documentElement);

        $this->assertSame($expected, (array) $actual);
    }

    /**
     * @testdox xmlToVariable() can construct objects with constructor arguments recursively
     */
    public function testXmlToVariableCanConstructObjectsWithConstructorArgumentsRecursively(): void
    {
        $xml = '<object class="Exception"><arguments><string>one</string><integer>0</integer><object class="Exception"><arguments><string>two</string></arguments></object></arguments></object>';
        $dom = new DOMDocument;
        $dom->loadXML($xml);

        $actual = Xml::xmlToVariable($dom->documentElement);

        $this->assertEquals('one', $actual->getMessage());
        $this->assertEquals('two', $actual->getPrevious()->getMessage());
    }
}
