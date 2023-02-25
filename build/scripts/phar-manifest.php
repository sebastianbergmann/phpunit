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

$package      = package();
$version      = version();
$dependencies = dependencies();

manifest($argv[1], $package, $version, $dependencies);
sbom($argv[2], $package, $version, $dependencies);

function manifest(string $outputFilename, array $package, string $version, array $dependencies): void
{
    $buffer = sprintf(
        '%s/%s: %s' . "\n",
        $package['group'],
        $package['name'],
        $version
    );

    foreach ($dependencies as $dependency) {
        $buffer .= sprintf(
            '%s: %s' . "\n",
            $dependency['name'],
            versionWithReference(
                $dependency['version'],
                $dependency['source']['reference']
            )
        );
    }

    file_put_contents($outputFilename, $buffer);
}

function sbom(string $outputFilename, array $package, string $version, array $dependencies): void
{
    $writer = new XMLWriter;

    $writer->openMemory();
    $writer->setIndent(true);
    $writer->startDocument();

    $writer->startElement('bom');
    $writer->writeAttribute('xmlns', 'http://cyclonedx.org/schema/bom/1.4');

    $writer->startElement('components');

    writeComponent(
        $writer,
        $package['group'],
        $package['name'],
        $version,
        $package['description'],
        $package['license']
    );

    foreach ($dependencies as $dependency) {
        [$group, $name] = explode('/', $dependency['name']);

        writeComponent(
            $writer,
            $group,
            $name,
            versionWithReference(
                $dependency['version'],
                $dependency['source']['reference']
            ),
            $dependency['description'],
            $dependency['license']
        );
    }

    $writer->endElement();
    $writer->endElement();
    $writer->endDocument();

    file_put_contents($outputFilename, $writer->outputMemory());
}

function package(): array
{
    $data = json_decode(
        file_get_contents(
            __DIR__ . '/../../composer.json'
        ),
        true
    );

    [$group, $name] = explode('/', $data['name']);

    return [
        'group' => $group,
        'name' => $name,
        'description' => $data['description'],
        'license' => [$data['license']],
    ];
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

function dependencies(): array
{
    return json_decode(
        file_get_contents(
            __DIR__ . '/../../composer.lock'
        ),
        true
    )['packages'];
}

function versionWithReference(string $version, string $reference): string
{
    if (!preg_match('/^[v= ]*(([0-9]+)(\\.([0-9]+)(\\.([0-9]+)(-([0-9]+))?(-?([a-zA-Z-+][a-zA-Z0-9.\\-:]*)?)?)?)?)$/', $version)) {
        $version .=  '@' . $reference;
    }

    return $version;
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
