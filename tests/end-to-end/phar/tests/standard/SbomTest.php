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

use function array_keys;
use function file_get_contents;
use function json_decode;
use function libxml_clear_errors;
use function libxml_get_errors;
use function libxml_set_external_entity_loader;
use function libxml_use_internal_errors;
use function preg_match;
use function sort;
use function sprintf;
use function strpos;
use function substr_count;
use function trim;
use DOMDocument;
use DOMElement;
use DOMXPath;
use PHPUnit\Framework\TestCase;

final class SbomTest extends TestCase
{
    private const CYCLONEDX_NAMESPACE            = 'http://cyclonedx.org/schema/bom/1.7';
    private const BUNDLED_COMPONENTS             = '/c:bom/c:components/c:component[not(@isExternal="true")]';
    private const EXTERNAL_COMPONENTS            = '/c:bom/c:components/c:component[@isExternal="true"]';
    private const PHPUNIT_AND_BUNDLED_COMPONENTS = '/c:bom/c:metadata/c:component | ' . self::BUNDLED_COMPONENTS;

    public function testIsCycloneDx17Document(): void
    {
        $bom = $this->xpath()->document->documentElement;

        $this->assertSame('bom', $bom->localName);
        $this->assertSame(self::CYCLONEDX_NAMESPACE, $bom->namespaceURI);
        $this->assertSame('1', $bom->getAttribute('version'));
        $this->assertFalse($bom->hasAttribute('serialNumber'));
    }

    /**
     * The XML Schema files in _files are bom-1.7.xsd and spdx.xsd from
     * https://github.com/CycloneDX/specification/tree/1.7.2/schema.
     */
    public function testIsValidAccordingToCycloneDx17XmlSchema(): void
    {
        $document = $this->xpath()->document;

        // bom-1.7.xsd imports the SPDX license schema from cyclonedx.org, use the local copy instead
        libxml_set_external_entity_loader(
            static function (?string $public, ?string $system, array $context): ?string
            {
                if ($system === 'http://cyclonedx.org/schema/spdx') {
                    return __DIR__ . '/_files/spdx.xsd';
                }

                return $system;
            }
        );

        $useInternalErrors = libxml_use_internal_errors(true);

        libxml_clear_errors();

        try {
            $valid  = $document->schemaValidate(__DIR__ . '/_files/bom-1.7.xsd');
            $errors = [];

            foreach (libxml_get_errors() as $error) {
                $errors[] = sprintf('Line %d: %s', $error->line, trim($error->message));
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($useInternalErrors);
            libxml_set_external_entity_loader(null);
        }

        $this->assertSame([], $errors);
        $this->assertTrue($valid);
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

    public function testMetadataHasManufacturerWithContact(): void
    {
        $this->assertGreaterThan(
            0,
            $this->xpath()->query('/c:bom/c:metadata/c:manufacturer/c:contact[c:email]')->length
        );
    }

    public function testMetadataComponentReferencesSecurityTxt(): void
    {
        $this->assertSame(
            'https://phpunit.de/.well-known/security.txt',
            $this->xpath()->evaluate('string(/c:bom/c:metadata/c:component/c:externalReferences/c:reference[@type="rfc-9116"]/c:url)')
        );
    }

    public function testComponentsAreThePackagesFromTheBundledComposerLock(): void
    {
        $xpath      = $this->xpath();
        $components = [];

        foreach ($xpath->query(self::BUNDLED_COMPONENTS) as $component) {
            $components[] = $xpath->evaluate('string(c:group)', $component) . '/' . $xpath->evaluate('string(c:name)', $component);
        }

        $packages = [];

        foreach ($this->packages() as $package) {
            $packages[] = $package['name'];
        }

        sort($packages);

        $this->assertSame($packages, $components);
        $this->assertNotContains('phpunit/phpunit', $components);
    }

    public function testPhpunitAndEveryBundledComponentHaveBomRefNameVersionPurlAndLicenses(): void
    {
        $xpath = $this->xpath();

        foreach ($xpath->query(self::PHPUNIT_AND_BUNDLED_COMPONENTS) as $component) {
            $this->assertInstanceOf(DOMElement::class, $component);

            $purl = $xpath->evaluate('string(c:purl)', $component);

            $this->assertSame($purl, $component->getAttribute('bom-ref'));
            $this->assertNotSame('', $xpath->evaluate('string(c:name)', $component));
            $this->assertNotSame('', $xpath->evaluate('string(c:version)', $component));
            $this->assertSame(1, $xpath->query('c:licenses', $component)->length);
            $this->assertSame(2, $xpath->query('c:licenses/*', $component)->length);

            $declared  = $xpath->query('c:licenses/c:expression[@acknowledgement="declared"]', $component);
            $concluded = $xpath->query('c:licenses/c:expression[@acknowledgement="concluded"]', $component);

            $this->assertSame(1, $declared->length);
            $this->assertSame(1, $concluded->length);
            $this->assertNotSame('', $declared->item(0)->textContent);
            $this->assertSame($declared->item(0)->textContent, $concluded->item(0)->textContent);
        }
    }

    public function testPhpunitAndEveryBundledComponentHaveManufacturerWithContactsOrUrl(): void
    {
        $xpath = $this->xpath();

        foreach ($xpath->query(self::PHPUNIT_AND_BUNDLED_COMPONENTS) as $component) {
            $this->assertSame(1, $xpath->query('c:manufacturer', $component)->length);

            $contacts = $xpath->query('c:manufacturer/c:contact', $component)->length;
            $urls     = $xpath->query('c:manufacturer/c:url', $component)->length;

            if ($contacts > 0) {
                $this->assertSame($contacts, $xpath->query('c:manufacturer/c:contact[c:email]', $component)->length);
                $this->assertSame(0, $urls);
            } else {
                $this->assertSame(1, $urls);
            }
        }
    }

    public function testPhpunitAndEveryBundledComponentHaveEffectiveLicence(): void
    {
        $xpath = $this->xpath();

        foreach ($xpath->query(self::PHPUNIT_AND_BUNDLED_COMPONENTS) as $component) {
            $effectiveLicence = $xpath->query('c:properties/c:property[@name="bsi:component:effectiveLicence"]', $component);

            $this->assertSame(1, $effectiveLicence->length);
            $this->assertNotSame('', $effectiveLicence->item(0)->textContent);
        }
    }

    public function testPlatformPackagesAreExternalComponents(): void
    {
        $xpath      = $this->xpath();
        $components = $xpath->query(self::EXTERNAL_COMPONENTS);

        $this->assertGreaterThan(0, $components->length);

        foreach ($components as $component) {
            $this->assertInstanceOf(DOMElement::class, $component);

            $this->assertNotSame('', $xpath->evaluate('string(c:name)', $component));
            $this->assertNotSame('', $xpath->evaluate('string(c:version)', $component));
            $this->assertNotSame('', $xpath->evaluate('string(c:manufacturer/c:url)', $component));
            $this->assertSame(0, $xpath->query('c:purl', $component)->length);
            $this->assertSame(0, $xpath->query('c:licenses', $component)->length);
            $this->assertSame(0, $xpath->query(sprintf('/c:bom/c:dependencies/c:dependency[@ref="%s"]', $component->getAttribute('bom-ref')))->length);
        }

        $php = $xpath->query(self::EXTERNAL_COMPONENTS . '[@bom-ref="php"]');

        $this->assertSame(1, $php->length);
        $this->assertSame(1, $xpath->query('c:cpe', $php->item(0))->length);
    }

    public function testEveryBundledComponentDependsOnThePlatformPackagesItRequires(): void
    {
        $xpath   = $this->xpath();
        $bomRefs = [];

        foreach ($xpath->query(self::BUNDLED_COMPONENTS) as $component) {
            $this->assertInstanceOf(DOMElement::class, $component);

            $bomRefs[$xpath->evaluate('string(c:group)', $component) . '/' . $xpath->evaluate('string(c:name)', $component)] = $component->getAttribute('bom-ref');
        }

        $expected = [];

        foreach ($this->packages() as $package) {
            $require = [];

            if (isset($package['require'])) {
                $require = $package['require'];
            }

            foreach (array_keys($require) as $name) {
                if ($name === 'php' || strpos($name, 'ext-') === 0) {
                    $expected[] = $bomRefs[$package['name']] . ' -> ' . $name;
                }
            }
        }

        $actual = [];

        foreach ($xpath->query('/c:bom/c:dependencies/c:dependency[position() > 1]/c:dependency[not(starts-with(@ref, "pkg:"))]') as $dependency) {
            $actual[] = $dependency->parentNode->getAttribute('ref') . ' -> ' . $dependency->getAttribute('ref');
        }

        sort($expected);
        sort($actual);

        $this->assertNotSame([], $expected);
        $this->assertSame($expected, $actual);
    }

    public function testEveryPurlIsValid(): void
    {
        foreach ($this->xpath()->query('//c:purl') as $purl) {
            $this->assertSame(0, strpos($purl->textContent, 'pkg:composer/'));
            $this->assertSame(1, substr_count($purl->textContent, '@'));
        }
    }

    public function testThereIsOneDependencyEntryForPhpunitAndForEveryBundledComponent(): void
    {
        $xpath = $this->xpath();

        $this->assertSame(
            $xpath->query(self::BUNDLED_COMPONENTS)->length,
            $xpath->query('/c:bom/c:dependencies/c:dependency')->length - 1
        );

        $this->assertSame(
            $xpath->evaluate('string(/c:bom/c:metadata/c:component/@bom-ref)'),
            $xpath->evaluate('string(/c:bom/c:dependencies/c:dependency[1]/@ref)')
        );
    }

    public function testDependenciesOfPhpunitAndBundledComponentsAreCompleteAndThoseOfExternalComponentsAreUnknown(): void
    {
        $xpath = $this->xpath();

        $this->assertSame(2, $xpath->query('/c:bom/c:compositions/c:composition')->length);

        $this->assertSame(
            $this->attributeValues($xpath, self::PHPUNIT_AND_BUNDLED_COMPONENTS, 'bom-ref'),
            $this->attributeValues($xpath, '/c:bom/c:compositions/c:composition[c:aggregate="complete"]/c:dependencies/c:dependency', 'ref')
        );

        $this->assertSame(
            $this->attributeValues($xpath, self::EXTERNAL_COMPONENTS, 'bom-ref'),
            $this->attributeValues($xpath, '/c:bom/c:compositions/c:composition[c:aggregate="unknown"]/c:dependencies/c:dependency', 'ref')
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

    private function packages(): array
    {
        return json_decode(file_get_contents(__PHPUNIT_PHAR_ROOT__ . '/composer.lock'), true)['packages'];
    }

    private function attributeValues(DOMXPath $xpath, string $expression, string $attribute): array
    {
        $values = [];

        foreach ($xpath->query($expression) as $element) {
            $this->assertInstanceOf(DOMElement::class, $element);

            $values[] = $element->getAttribute($attribute);
        }

        sort($values);

        return $values;
    }
}
