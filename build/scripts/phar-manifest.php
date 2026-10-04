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

    $platformPackages = platformPackages($package, $dependencies);
    $platformVersion  = minimumPhpVersion($package['require']);

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

    foreach ($platformPackages as $platformPackage) {
        writePlatformComponent($writer, $platformPackage, $platformVersion);
    }

    $writer->endElement();

    writeDependencies($writer, $package, $ref, $dependencies, $refs);
    writeCompositions($writer, array_merge([$ref], array_values($refs)), $platformPackages);

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

function platformPackages(array $package, array $dependencies): array
{
    $requirements = [$package['group'] . '/' . $package['name'] => $package['require']];

    foreach ($dependencies as $dependency) {
        if (isset($dependency['require'])) {
            $requirements[$dependency['name']] = $dependency['require'];
        }
    }

    $platformPackages = [];

    foreach ($requirements as $requiredBy => $require) {
        foreach (array_keys($require) as $name) {
            if (!isPlatformPackage($name)) {
                continue;
            }

            if ($name !== 'php' && strpos($name, 'ext-') !== 0) {
                abort(
                    sprintf(
                        '%s requires %s, which is a platform package that is neither php nor a PHP extension',
                        $requiredBy,
                        $name
                    )
                );
            }

            $platformPackages[$name] = true;
        }
    }

    $platformPackages = array_keys($platformPackages);

    sort($platformPackages, SORT_STRING);

    return $platformPackages;
}

/**
 * The PHP runtime and its extensions are not bundled, so the minimum version of PHP
 * that is required by composer.json is used for php and for every ext-* package
 */
function minimumPhpVersion(array $require): string
{
    if (!isset($require['php'])) {
        abort('composer.json does not require php');
    }

    if (preg_match('/^>=\s*(\d+)\.(\d+)(?:\.(\d+))?$/', $require['php'], $matches) !== 1) {
        abort(
            sprintf(
                'composer.json requires php %s, but only >=X.Y and >=X.Y.Z are supported',
                $require['php']
            )
        );
    }

    $patch = '0';

    if (isset($matches[3])) {
        $patch = $matches[3];
    }

    return $matches[1] . '.' . $matches[2] . '.' . $patch;
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

    $name    = $package['group'] . '/' . $package['name'];
    $license = license($name, $package['license']);

    $writer->startElement('component');
    $writer->writeAttribute('type', 'framework');
    $writer->writeAttribute('bom-ref', $ref);

    writeManufacturer($writer, $name, $package['authors'], $package['homepage']);
    writeAuthors($writer, $package['authors']);

    $writer->writeElement('group', $package['group']);
    $writer->writeElement('name', $package['name']);
    $writer->writeElement('version', $version);
    $writer->writeElement('description', $package['description']);

    writeLicenses($writer, $license);

    $writer->writeElement('purl', $ref);

    writeExternalReferences(
        $writer,
        [
            'website' => $package['homepage'],
            'issue-tracker' => $package['issues'],
            'security-contact' => $package['security'],
            'rfc-9116' => 'https://phpunit.de/.well-known/security.txt',
        ]
    );

    writeProperties($writer, ['bsi:component:effectiveLicence' => $license]);

    $writer->endElement();

    writeManufacturer($writer, $name, $package['authors'], $package['homepage']);

    $writer->endElement();
}

function writeComponent(XMLWriter $writer, array $dependency, string $ref): void
{
    [$group, $name] = explode('/', $dependency['name']);

    $authors = [];

    if (isset($dependency['authors'])) {
        $authors = $dependency['authors'];
    }

    $licenses = [];

    if (isset($dependency['license'])) {
        $licenses = $dependency['license'];
    }

    $license = license($dependency['name'], $licenses);

    $writer->startElement('component');
    $writer->writeAttribute('type', 'library');
    $writer->writeAttribute('bom-ref', $ref);

    if ($authors === []) {
        writeSupplier($writer, $group, $dependency);
    }

    writeManufacturer($writer, $dependency['name'], $authors, website($dependency));

    if ($authors !== []) {
        writeAuthors($writer, $authors);
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

    writeLicenses($writer, $license);

    $writer->writeElement('purl', $ref);

    $references = [];

    if (isset($dependency['source']['url'])) {
        $references['vcs'] = $dependency['source']['url'];
    }

    if (isset($dependency['support']['source'])) {
        $references['source-distribution'] = $dependency['support']['source'];
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

    $properties['bsi:component:effectiveLicence'] = $license;

    writeProperties($writer, $properties);

    $writer->endElement();
}

function writePlatformComponent(XMLWriter $writer, string $platformPackage, string $version): void
{
    if ($platformPackage === 'php') {
        $type = 'platform';
        $name = 'php';
    } else {
        $type = 'library';
        $name = substr($platformPackage, strlen('ext-'));
    }

    $writer->startElement('component');
    $writer->writeAttribute('type', $type);
    $writer->writeAttribute('bom-ref', $platformPackage);
    $writer->writeAttribute('isExternal', 'true');

    $writer->startElement('manufacturer');
    $writer->writeElement('url', 'https://www.php.net/');
    $writer->endElement();

    $writer->writeElement('name', $name);
    $writer->writeElement('version', $version);

    if ($platformPackage === 'php') {
        $writer->writeElement('cpe', sprintf('cpe:2.3:a:php:php:%s:*:*:*:*:*:*:*', $version));
    }

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

function writeManufacturer(XMLWriter $writer, string $name, array $authors, ?string $website): void
{
    $contacts = [];

    foreach ($authors as $author) {
        if (isset($author['email'])) {
            $contacts[] = $author;
        }
    }

    if ($contacts === [] && $website === null) {
        abort(
            sprintf(
                '%s has neither an author with an email address nor a homepage or source URL',
                $name
            )
        );
    }

    $writer->startElement('manufacturer');

    if ($contacts === []) {
        $writer->writeElement('url', $website);
    }

    foreach ($contacts as $contact) {
        $writer->startElement('contact');
        $writer->writeElement('name', $contact['name']);
        $writer->writeElement('email', $contact['email']);
        $writer->endElement();
    }

    $writer->endElement();
}

function website(array $dependency): ?string
{
    if (isset($dependency['homepage'])) {
        return $dependency['homepage'];
    }

    if (isset($dependency['source']['url'])) {
        return preg_replace('/\.git$/', '', $dependency['source']['url']);
    }

    return null;
}

function writeLicenses(XMLWriter $writer, string $license): void
{
    $writer->startElement('licenses');

    foreach (['declared', 'concluded'] as $acknowledgement) {
        $writer->startElement('expression');
        $writer->writeAttribute('acknowledgement', $acknowledgement);
        $writer->text($license);
        $writer->endElement();
    }

    $writer->endElement();
}

function license(string $name, array $licenses): string
{
    if ($licenses === []) {
        abort(
            sprintf(
                '%s does not declare a license',
                $name
            )
        );
    }

    // Multiple licenses in composer.json are disjunctive: the package can be used under any of them,
    // but the effective license under which PHPUnit uses the package cannot be determined automatically
    if (count($licenses) > 1) {
        abort(
            sprintf(
                '%s declares more than one license (%s), the effective license cannot be determined',
                $name,
                implode(', ', $licenses)
            )
        );
    }

    if (!isSpdxLicenseIdentifier($licenses[0])) {
        abort(
            sprintf(
                '%s declares the license %s, which is not a single SPDX license identifier',
                $name,
                $licenses[0]
            )
        );
    }

    return $licenses[0];
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
    $requiredByDependency = [];

    foreach ($dependencies as $dependency) {
        if (!isset($dependency['require'])) {
            continue;
        }

        foreach (array_keys($dependency['require']) as $name) {
            $requiredByDependency[$name] = true;
        }
    }

    $direct = requirements($package['group'] . '/' . $package['name'], $package['require'], $refs);

    // Packages that are added to composer.lock only for the PHAR build, without
    // being required in composer.json, are direct dependencies of PHPUnit, too
    foreach ($dependencies as $dependency) {
        if (!isset($requiredByDependency[$dependency['name']]) && !in_array($refs[$dependency['name']], $direct, true)) {
            $direct[] = $refs[$dependency['name']];
        }
    }

    $writer->startElement('dependencies');

    writeDependency($writer, $ref, $direct);

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
        // The bom-ref of a platform component is its Composer name, see writePlatformComponent()
        if (isPlatformPackage($name)) {
            $requirements[] = $name;

            continue;
        }

        if (!isset($refs[$name])) {
            abort(
                sprintf(
                    '%s requires %s, which is neither a platform package nor a package in composer.lock',
                    $requiredBy,
                    $name
                )
            );
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

function writeCompositions(XMLWriter $writer, array $bundled, array $external): void
{
    $writer->startElement('compositions');

    // The dependencies of PHPUnit and of the bundled packages are known completely,
    // the dependencies of the PHP runtime and its extensions are not resolved
    writeComposition($writer, 'complete', $bundled);
    writeComposition($writer, 'unknown', $external);

    $writer->endElement();
}

function writeComposition(XMLWriter $writer, string $aggregate, array $refs): void
{
    sort($refs, SORT_STRING);

    $writer->startElement('composition');
    $writer->writeElement('aggregate', $aggregate);
    $writer->startElement('dependencies');

    foreach ($refs as $ref) {
        $writer->startElement('dependency');
        $writer->writeAttribute('ref', $ref);
        $writer->endElement();
    }

    $writer->endElement();
    $writer->endElement();
}

function abort(string $message): void
{
    fwrite(STDERR, 'Cannot create SBOM: ' . $message . PHP_EOL);

    exit(1);
}
