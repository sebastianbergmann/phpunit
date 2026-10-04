#!/usr/bin/env php
<?php declare(strict_types=1);
const CYCLONEDX_NAMESPACE = 'http://cyclonedx.org/schema/bom/1.7';
const DOWNLOAD_URL        = 'https://phar.phpunit.de/';
const UUID_NAMESPACE_URL  = '6ba7b811-9dad-11d1-80b4-00c04fd430c8';

if ($argc !== 3) {
    fwrite(
        STDERR,
        sprintf(
            '%s /path/to/phpunit.phar /path/to/phpunit.phar.cdx.xml' . PHP_EOL,
            $argv[0]
        )
    );

    exit(1);
}

sbom($argv[1], $argv[2]);

/**
 * Writes the SBOM for a PHAR file: the SBOM that is embedded in the PHAR,
 * amended with the filename, the SHA-512 hash, and the executable, archive
 * and structured properties of the PHAR file as required by BSI TR-03183-2
 */
function sbom(string $pharFilename, string $outputFilename): void
{
    $embedded = @file_get_contents('phar://' . $pharFilename . '/sbom.xml');

    if ($embedded === false) {
        fwrite(
            STDERR,
            sprintf(
                'Cannot create SBOM: %s does not contain sbom.xml' . PHP_EOL,
                $pharFilename
            )
        );

        exit(1);
    }

    $filename = basename($pharFilename);
    $hash     = hash_file('sha512', $pharFilename);

    $document                     = new DOMDocument;
    $document->preserveWhiteSpace = false;
    $document->formatOutput       = true;

    $document->loadXML($embedded);

    $xpath = new DOMXPath($document);
    $xpath->registerNamespace('c', CYCLONEDX_NAMESPACE);

    $document->documentElement->setAttribute(
        'serialNumber',
        'urn:uuid:' . uuid5(UUID_NAMESPACE_URL, DOWNLOAD_URL . basename($outputFilename))
    );

    $component = $xpath->query('/c:bom/c:metadata/c:component')->item(0);
    $version   = $xpath->evaluate('string(c:version)', $component);

    $tool = element($document, 'component', ['type' => 'application']);
    $tool->appendChild(element($document, 'group', [], 'phpunit'));
    $tool->appendChild(element($document, 'name', [], 'phar-sbom'));
    $tool->appendChild(element($document, 'version', [], $version));

    $xpath->query('/c:bom/c:metadata/c:tools/c:components')->item(0)->appendChild($tool);

    $component->insertBefore(
        hashes($document, $hash),
        $xpath->query('c:licenses', $component)->item(0)
    );

    $reference = element($document, 'reference', ['type' => 'distribution']);
    $reference->appendChild(element($document, 'url', [], DOWNLOAD_URL . $filename));
    $reference->appendChild(hashes($document, $hash));

    $xpath->query('c:externalReferences', $component)->item(0)->appendChild($reference);

    // A PHAR is a structured archive of PHP code, comparable to a self-extracting archive (BSI TR-03183-2, table 7)
    $properties = [
        'bsi:component:filename' => $filename,
        'bsi:component:executable' => 'executable',
        'bsi:component:archive' => 'archive',
        'bsi:component:structured' => 'structured',
    ];

    foreach ($properties as $name => $value) {
        $xpath->query('c:properties', $component)->item(0)->appendChild(
            element($document, 'property', ['name' => $name], $value)
        );
    }

    file_put_contents($outputFilename, $document->saveXML());
}

function hashes(DOMDocument $document, string $hash): DOMElement
{
    $hashes = element($document, 'hashes');
    $hashes->appendChild(element($document, 'hash', ['alg' => 'SHA-512'], $hash));

    return $hashes;
}

function element(DOMDocument $document, string $name, array $attributes = [], ?string $text = null): DOMElement
{
    $element = $document->createElementNS(CYCLONEDX_NAMESPACE, $name);

    foreach ($attributes as $attribute => $value) {
        $element->setAttribute($attribute, $value);
    }

    if ($text !== null) {
        $element->appendChild($document->createTextNode($text));
    }

    return $element;
}

/**
 * @see https://www.rfc-editor.org/rfc/rfc9562#name-uuid-version-5
 */
function uuid5(string $namespace, string $name): string
{
    $hash = sha1(hex2bin(str_replace('-', '', $namespace)) . $name);

    return sprintf(
        '%s-%s-%04x-%04x-%s',
        substr($hash, 0, 8),
        substr($hash, 8, 4),
        (hexdec(substr($hash, 12, 4)) & 0x0FFF) | 0x5000,
        (hexdec(substr($hash, 16, 4)) & 0x3FFF) | 0x8000,
        substr($hash, 20, 12)
    );
}
