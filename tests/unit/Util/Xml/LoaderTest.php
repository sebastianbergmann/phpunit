<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\Xml;

use DOMDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Loader::class)]
#[Small]
final class LoaderTest extends TestCase
{
    public function testCanParseFileWithValidXml(): void
    {
        $document = (new Loader)->loadFile(__DIR__ . '/../../../_files/configuration.xml');

        $this->assertInstanceOf(DOMDocument::class, $document);
    }

    public function testCannotParseFileThatDoesNotExist(): void
    {
        $this->expectException(XmlException::class);
        $this->expectExceptionMessage('Could not read XML from file "/does/not/exist.xml"');

        (new Loader)->loadFile('/does/not/exist.xml');
    }

    public function testCannotParseEmptyFile(): void
    {
        $this->expectException(XmlException::class);

        (new Loader)->loadFile(__DIR__ . '/../../../_files/empty.xml');
    }

    public function testCannotParseFileWithInvalidXml(): void
    {
        $this->expectException(XmlException::class);
        $this->expectExceptionMessageMatches("#Premature end of data in tag test line 1|EndTag: '</' not found#");

        (new Loader)->loadFile(__DIR__ . '/../../../_files/invalid.xml');
    }

    public function testCanParseStringWithValidXml(): void
    {
        $document = (new Loader)->load('<test/>');

        $this->assertInstanceOf(DOMDocument::class, $document);
    }

    public function testCannotParseEmptyString(): void
    {
        $this->expectException(XmlException::class);
        $this->expectExceptionMessage('Could not parse XML from empty string');

        (new Loader)->load('');
    }

    public function testCannotParseStringWithInvalidXml(): void
    {
        $this->expectException(XmlException::class);
        $this->expectExceptionMessageMatches("#Premature end of data in tag test line 1|EndTag: '</' not found#");

        (new Loader)->load('<test>');
    }
}
