#!/usr/bin/env php
<?php declare(strict_types=1);
require __DIR__ . '/source-date-epoch.php';

if ($argc !== 5 || !in_array($argv[4], ['release', 'snapshot'], true)) {
    fwrite(
        STDERR,
        sprintf(
            '%s /path/to/composer.lock /path/to/manifest.txt /path/to/sbom.xml release|snapshot' . PHP_EOL,
            $argv[0]
        )
    );

    exit(1);
}

$package      = package();
$version      = version();
$dependencies = dependencies($argv[1]);
$epoch        = sourceDateEpoch($argv[4])['epoch'];

manifest($argv[2], $package, $version, $dependencies);
sbom($argv[3], $package, $version, $dependencies, $epoch);

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

function sbom(string $outputFilename, array $package, string $version, array $dependencies, int $epoch): void
{
    usort(
        $dependencies,
        static function (array $a, array $b): int
        {
            return strcmp($a['name'], $b['name']);
        }
    );

    $ref  = purl($package['group'] . '/' . $package['name'], $version);
    $refs = [];

    foreach ($dependencies as $dependency) {
        $refs[$dependency['name']] = purl(
            $dependency['name'],
            versionWithReference(
                $dependency['version'],
                $dependency['source']['reference']
            )
        );
    }

    $writer = new XMLWriter;

    $writer->openMemory();
    $writer->setIndent(true);
    $writer->startDocument('1.0', 'UTF-8');

    $writer->startElement('bom');
    $writer->writeAttribute('xmlns', 'http://cyclonedx.org/schema/bom/1.7');
    $writer->writeAttribute('version', '1');

    writeMetadata($writer, $package, $version, $ref, $epoch);

    $writer->startElement('components');

    foreach ($dependencies as $dependency) {
        writeComponent($writer, $dependency, $refs[$dependency['name']]);
    }

    $writer->endElement();

    writeDependencies($writer, $package, $ref, $dependencies, $refs);
    writeCompositions($writer, $ref);

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
        'authors' => $data['authors'],
        'homepage' => $data['homepage'],
        'issues' => $data['support']['issues'],
        'security' => $data['support']['security'],
        'require' => $data['require'],
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

function dependencies(string $composerLock): array
{
    return json_decode(
        file_get_contents(
            $composerLock
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

function purl(string $name, string $version): string
{
    return sprintf(
        'pkg:composer/%s@%s',
        $name,
        rawurlencode($version)
    );
}

function writeMetadata(XMLWriter $writer, array $package, string $version, string $ref, int $epoch): void
{
    $writer->startElement('metadata');

    $writer->writeElement('timestamp', gmdate('Y-m-d\TH:i:s\Z', $epoch));

    $writer->startElement('lifecycles');
    $writer->startElement('lifecycle');
    $writer->writeElement('phase', 'build');
    $writer->endElement();
    $writer->endElement();

    $writer->startElement('tools');
    $writer->startElement('components');
    $writer->startElement('component');
    $writer->writeAttribute('type', 'application');
    $writer->writeElement('group', $package['group']);
    $writer->writeElement('name', 'phar-manifest');
    $writer->writeElement('version', $version);
    $writer->endElement();
    $writer->endElement();
    $writer->endElement();

    writeAuthors($writer, $package['authors']);

    $writer->startElement('component');
    $writer->writeAttribute('type', 'framework');
    $writer->writeAttribute('bom-ref', $ref);

    writeAuthors($writer, $package['authors']);

    $writer->writeElement('group', $package['group']);
    $writer->writeElement('name', $package['name']);
    $writer->writeElement('version', $version);
    $writer->writeElement('description', $package['description']);

    writeLicenses($writer, $package['license']);

    $writer->writeElement('purl', $ref);

    writeExternalReferences(
        $writer,
        [
            'website' => $package['homepage'],
            'issue-tracker' => $package['issues'],
            'security-contact' => $package['security'],
        ]
    );

    $writer->endElement();

    $writer->endElement();
}

function writeComponent(XMLWriter $writer, array $dependency, string $ref): void
{
    [$group, $name] = explode('/', $dependency['name']);

    $writer->startElement('component');
    $writer->writeAttribute('type', 'library');
    $writer->writeAttribute('bom-ref', $ref);

    if (isset($dependency['authors']) && $dependency['authors'] !== []) {
        writeAuthors($writer, $dependency['authors']);
    } else {
        writeSupplier($writer, $group, $dependency);
    }

    $writer->writeElement('group', $group);
    $writer->writeElement('name', $name);
    $writer->writeElement(
        'version',
        versionWithReference(
            $dependency['version'],
            $dependency['source']['reference']
        )
    );

    if (isset($dependency['description'])) {
        $writer->writeElement('description', $dependency['description']);
    }

    if (isset($dependency['license'])) {
        writeLicenses($writer, $dependency['license']);
    }

    $writer->writeElement('purl', $ref);

    $references = [];

    if (isset($dependency['source']['url'])) {
        $references['vcs'] = $dependency['source']['url'];
    }

    if (isset($dependency['dist']['url'])) {
        $references['distribution'] = $dependency['dist']['url'];
    }

    writeExternalReferences($writer, $references);

    $properties = [];

    if (isset($dependency['source']['reference'])) {
        $properties['cdx:composer:package:sourceReference'] = $dependency['source']['reference'];
    }

    if (isset($dependency['dist']['reference'])) {
        $properties['cdx:composer:package:distReference'] = $dependency['dist']['reference'];
    }

    writeProperties($writer, $properties);

    $writer->endElement();
}

function writeAuthors(XMLWriter $writer, array $authors): void
{
    $writer->startElement('authors');

    foreach ($authors as $author) {
        $writer->startElement('author');
        $writer->writeElement('name', $author['name']);

        if (isset($author['email'])) {
            $writer->writeElement('email', $author['email']);
        }

        $writer->endElement();
    }

    $writer->endElement();
}

function writeSupplier(XMLWriter $writer, string $group, array $dependency): void
{
    $writer->startElement('supplier');
    $writer->writeElement('name', $group);

    if (isset($dependency['source']['url'])) {
        $writer->writeElement('url', $dependency['source']['url']);
    }

    $writer->endElement();
}

function writeLicenses(XMLWriter $writer, array $licenses): void
{
    if ($licenses === []) {
        return;
    }

    $writer->startElement('licenses');

    if (count($licenses) === 1) {
        $writer->startElement('license');

        if (isSpdxLicenseIdentifier($licenses[0])) {
            $writer->writeElement('id', $licenses[0]);
        } else {
            $writer->writeElement('name', $licenses[0]);
        }

        $writer->endElement();
    } else {
        // Multiple licenses in composer.json are disjunctive: the package can be used under any of them
        $writer->writeElement('expression', '(' . implode(' OR ', $licenses) . ')');
    }

    $writer->endElement();
}

function isSpdxLicenseIdentifier(string $license): bool
{
    if (strtolower($license) === 'proprietary') {
        return false;
    }

    return preg_match('/^[A-Za-z0-9.+-]+$/', $license) === 1;
}

function writeExternalReferences(XMLWriter $writer, array $references): void
{
    if ($references === []) {
        return;
    }

    $writer->startElement('externalReferences');

    foreach ($references as $type => $url) {
        $writer->startElement('reference');
        $writer->writeAttribute('type', $type);
        $writer->writeElement('url', $url);
        $writer->endElement();
    }

    $writer->endElement();
}

function writeProperties(XMLWriter $writer, array $properties): void
{
    if ($properties === []) {
        return;
    }

    $writer->startElement('properties');

    foreach ($properties as $name => $value) {
        $writer->startElement('property');
        $writer->writeAttribute('name', $name);
        $writer->text($value);
        $writer->endElement();
    }

    $writer->endElement();
}

function writeDependencies(XMLWriter $writer, array $package, string $ref, array $dependencies, array $refs): void
{
    $writer->startElement('dependencies');

    writeDependency(
        $writer,
        $ref,
        requirements($package['group'] . '/' . $package['name'], $package['require'], $refs)
    );

    foreach ($dependencies as $dependency) {
        $require = [];

        if (isset($dependency['require'])) {
            $require = $dependency['require'];
        }

        writeDependency(
            $writer,
            $refs[$dependency['name']],
            requirements($dependency['name'], $require, $refs)
        );
    }

    $writer->endElement();
}

function requirements(string $requiredBy, array $require, array $refs): array
{
    $requirements = [];

    foreach (array_keys($require) as $name) {
        if (isPlatformPackage($name)) {
            continue;
        }

        if (!isset($refs[$name])) {
            fwrite(
                STDERR,
                sprintf(
                    'Cannot create SBOM: %s requires %s, which is neither a platform package nor a package in composer.lock' . PHP_EOL,
                    $requiredBy,
                    $name
                )
            );

            exit(1);
        }

        $requirements[] = $refs[$name];
    }

    return $requirements;
}

/**
 * @see https://github.com/composer/composer/blob/main/src/Composer/Repository/PlatformRepository.php
 */
function isPlatformPackage(string $name): bool
{
    return preg_match('{^(?:php(?:-64bit|-ipv6|-zts|-debug)?|hhvm|(?:ext|lib)-[a-z0-9](?:[_.-]?[a-z0-9]+)*|composer(?:-(?:plugin|runtime)-api)?)$}iD', $name) === 1;
}

function writeDependency(XMLWriter $writer, string $ref, array $dependsOn): void
{
    sort($dependsOn, SORT_STRING);

    $writer->startElement('dependency');
    $writer->writeAttribute('ref', $ref);

    foreach ($dependsOn as $dependency) {
        $writer->startElement('dependency');
        $writer->writeAttribute('ref', $dependency);
        $writer->endElement();
    }

    $writer->endElement();
}

function writeCompositions(XMLWriter $writer, string $ref): void
{
    $writer->startElement('compositions');
    $writer->startElement('composition');
    $writer->writeElement('aggregate', 'complete');
    $writer->startElement('assemblies');
    $writer->startElement('assembly');
    $writer->writeAttribute('ref', $ref);
    $writer->endElement();
    $writer->endElement();
    $writer->endElement();
    $writer->endElement();
}
