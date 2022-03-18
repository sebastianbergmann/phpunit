<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

class Issue1472Test extends TestCase
{
    public function testAssertEqualXMLStructure(): void
    {
        $doc = new DOMDocument;
        $doc->loadXML('<root><label>text content</label></root>');

        $xpath = new DOMXPath($doc);

        $labelElement = $doc->getElementsByTagName('label')->item(0);

        $this->assertEquals(1, $xpath->evaluate('count(//label[text() = "text content"])'));

        $expectedElmt = $doc->createElement('label', 'text content');
        $this->assertEqualXMLStructure($expectedElmt, $labelElement);

        // the following assertion fails, even though it passed before - which is due to the assertEqualXMLStructure() has modified the $labelElement
        $this->assertEquals(1, $xpath->evaluate('count(//label[text() = "text content"])'));
    }
}
