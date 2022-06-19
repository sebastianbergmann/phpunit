#!/usr/bin/env php
<?php declare(strict_types=1);
if ($argc !== 3) {
    fwrite(
        STDERR,
        sprintf(
            '%s /path/to/manifest.txt /path/to/sbom.xml' . PHP_EOL,
            $argv[0]
        )
    );

    exit(1);
}

$dependencies = dependencies();
$version      = version();

manifest($argv[1], $version, $dependencies);
sbom($argv[2], $version, $dependencies);

function manifest(string $outputFilename, string $version, array $dependencies): void
{
    $buffer = 'phpunit/phpunit: ' . $version . "\n";

    foreach ($dependencies as $dependency) {
        $buffer .= $dependency['name'] . ': ' . $dependency['version'];

        if (!preg_match('/^[v= ]*(([0-9]+)(\\.([0-9]+)(\\.([0-9]+)(-([0-9]+))?(-?([a-zA-Z-+][a-zA-Z0-9.\\-:]*)?)?)?)?)$/', $dependency['version'])) {
            $buffer .=  '@' . $dependency['source']['reference'];
        }

        $buffer .=  "\n";
    }

    file_put_contents($outputFilename, $buffer);
}

function sbom(string $outputFilename, string $version, array $dependencies): void
{
    $writer = new XMLWriter;

    $writer->openMemory();
    $writer->setIndent(true);
    $writer->startDocument();

    $writer->startElement('bom');
    $writer->writeAttribute('xmlns', 'https://cyclonedx.org/schema/bom/1.4');

    $writer->startElement('components');

    writeComponent(
        $writer,
        'phpunit',
        'phpunit',
        $version,
        'The PHP Unit Testing framework',
        ['BSD-3-Clause']
    );

    foreach ($dependencies as $dependency) {
        [$group, $name]    = explode('/', $dependency['name']);
        $dependencyVersion = $dependency['version'];

        if (!preg_match('/^[v= ]*(([0-9]+)(\\.([0-9]+)(\\.([0-9]+)(-([0-9]+))?(-?([a-zA-Z-+][a-zA-Z0-9.\\-:]*)?)?)?)?)$/', $dependencyVersion)) {
            $dependencyVersion .=  '@' . $dependency['source']['reference'];
        }

        writeComponent(
            $writer,
            $group,
            $name,
            $dependencyVersion,
            $dependency['description'],
            $dependency['license']
        );
    }

    $writer->endElement();
    $writer->endElement();
    $writer->endDocument();

    file_put_contents($outputFilename, $writer->outputMemory());
}

function dependencies(): array
{
    return json_decode(
        file_get_contents(
            __DIR__ . '/../../composer.lock'
        ),
        true
    )['packages'];
}

function version(): string
{
    $tag = @exec('git describe --tags 2>&1');

    if (strpos($tag, '-') === false && strpos($tag, 'No names found') === false) {
        return $tag;
    }

    $branch = @exec('git rev-parse --abbrev-ref HEAD');
    $hash   = @exec('git log -1 --format="%H"');

    return $branch . '@' . $hash;
}

function writeComponent(XMLWriter $writer, string $group, string $name, string $version, string $description, array $licenses): void
{
    $writer->startElement('component');
    $writer->writeAttribute('type', 'library');

    $writer->writeElement('group', $group);
    $writer->writeElement('name', $name);
    $writer->writeElement('version', $version);
    $writer->writeElement('description', $description);

    $writer->startElement('licenses');

    foreach ($licenses as $license) {
        $writer->startElement('license');
        $writer->writeElement('id', $license);
        $writer->endElement();
    }

    $writer->endElement();

    $writer->writeElement(
        'purl',
        sprintf(
            'pkg:composer/%s/%s@%s',
            $group,
            $name,
            $version
        )
    );

    $writer->endElement();
}
