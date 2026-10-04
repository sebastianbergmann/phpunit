<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Phar;

use function file_get_contents;
use function json_decode;
use function preg_match;
use function sort;
use function strpos;
use function substr_count;
use DOMDocument;
use DOMElement;
use DOMXPath;
use PHPUnit\Framework\TestCase;

final class SbomTest extends TestCase
{
    private const CYCLONEDX_NAMESPACE = 'http://cyclonedx.org/schema/bom/1.7';

    public function testIsCycloneDx17Document(): void
    {
        $bom = $this->xpath()->document->documentElement;

        $this->assertSame('bom', $bom->localName);
        $this->assertSame(self::CYCLONEDX_NAMESPACE, $bom->namespaceURI);
        $this->assertSame('1', $bom->getAttribute('version'));
        $this->assertFalse($bom->hasAttribute('serialNumber'));
    }

    public function testMetadataHasTimestampAuthorToolAndComponent(): void
    {
        $xpath = $this->xpath();

        $timestamp = $xpath->query('/c:bom/c:metadata/c:timestamp');

        $this->assertSame(1, $timestamp->length);
        $this->assertSame(1, preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $timestamp->item(0)->textContent));

        $this->assertGreaterThan(0, $xpath->query('/c:bom/c:metadata/c:authors/c:author/c:name')->length);
        $this->assertSame(1, $xpath->query('/c:bom/c:metadata/c:tools/c:components/c:component')->length);

        $component = $xpath->query('/c:bom/c:metadata/c:component');

        $this->assertSame(1, $component->length);
        $this->assertSame('framework', $component->item(0)->getAttribute('type'));
        $this->assertSame('phpunit', $xpath->evaluate('string(c:group)', $component->item(0)));
        $this->assertSame('phpunit', $xpath->evaluate('string(c:name)', $component->item(0)));
    }

    public function testComponentsAreThePackagesFromTheBundledComposerLock(): void
    {
        $xpath      = $this->xpath();
        $components = [];

        foreach ($xpath->query('/c:bom/c:components/c:component') as $component) {
            $components[] = $xpath->evaluate('string(c:group)', $component) . '/' . $xpath->evaluate('string(c:name)', $component);
        }

        $packages = [];

        foreach (json_decode(file_get_contents(__PHPUNIT_PHAR_ROOT__ . '/composer.lock'), true)['packages'] as $package) {
            $packages[] = $package['name'];
        }

        sort($packages);

        $this->assertSame($packages, $components);
        $this->assertNotContains('phpunit/phpunit', $components);
    }

    public function testEveryComponentHasBomRefNameVersionPurlAndLicenses(): void
    {
        $xpath = $this->xpath();

        foreach ($xpath->query('/c:bom/c:metadata/c:component | /c:bom/c:components/c:component') as $component) {
            $this->assertInstanceOf(DOMElement::class, $component);

            $purl = $xpath->evaluate('string(c:purl)', $component);

            $this->assertSame($purl, $component->getAttribute('bom-ref'));
            $this->assertNotSame('', $xpath->evaluate('string(c:name)', $component));
            $this->assertNotSame('', $xpath->evaluate('string(c:version)', $component));
            $this->assertSame(1, $xpath->query('c:licenses', $component)->length);
        }
    }

    public function testEveryPurlIsValid(): void
    {
        foreach ($this->xpath()->query('//c:purl') as $purl) {
            $this->assertSame(0, strpos($purl->textContent, 'pkg:composer/'));
            $this->assertSame(1, substr_count($purl->textContent, '@'));
        }
    }

    public function testThereIsOneDependencyEntryForPhpunitAndForEveryComponent(): void
    {
        $xpath = $this->xpath();

        $this->assertSame(
            $xpath->query('/c:bom/c:components/c:component')->length,
            $xpath->query('/c:bom/c:dependencies/c:dependency')->length - 1
        );

        $this->assertSame(
            $xpath->evaluate('string(/c:bom/c:metadata/c:component/@bom-ref)'),
            $xpath->evaluate('string(/c:bom/c:dependencies/c:dependency[1]/@ref)')
        );
    }

    public function testEveryReferenceRefersToComponent(): void
    {
        $xpath   = $this->xpath();
        $bomRefs = [];

        foreach ($xpath->query('//@bom-ref') as $bomRef) {
            $bomRefs[] = $bomRef->value;
        }

        $refs = $xpath->query('/c:bom/c:dependencies//@ref | /c:bom/c:compositions//@ref');

        $this->assertGreaterThan(0, $refs->length);

        foreach ($refs as $ref) {
            $this->assertContains($ref->value, $bomRefs);
        }
    }

    private function xpath(): DOMXPath
    {
        $document = new DOMDocument;
        $document->load(__PHPUNIT_PHAR_ROOT__ . '/sbom.xml');

        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('c', self::CYCLONEDX_NAMESPACE);

        return $xpath;
    }
}
