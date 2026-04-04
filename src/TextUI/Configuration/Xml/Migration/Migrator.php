<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration;

use function assert;
use function str_contains;
use DOMDocument;
use DOMElement;
use PHPUnit\Runner\Version;
use PHPUnit\Util\Xml\Loader as XmlLoader;
use PHPUnit\Util\Xml\XmlException;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Migrator
{
    /**
     * @throws Exception
     * @throws MigrationException
     * @throws XmlException
     */
    public function migrate(string $filename): string
    {
        $origin = (new SchemaDetector)->detect($filename);

        if (!$origin->detected()) {
            throw new Exception('The file does not validate against any known schema');
        }

        $configurationDocument = (new XmlLoader)->loadFile($filename);

        if ($origin->version() === Version::series()) {
            if (!$this->schemaLocationNeedsUpdate($configurationDocument)) {
                throw new Exception('The file does not need to be migrated');
            }

            (new UpdateSchemaLocation)->migrate($configurationDocument);
        } else {
            foreach ((new MigrationBuilder)->build($origin->version()) as $migration) {
                $migration->migrate($configurationDocument);
            }
        }

        $configurationDocument->formatOutput       = true;
        $configurationDocument->preserveWhiteSpace = false;

        $xml = $configurationDocument->saveXML();

        assert($xml !== false);

        return $xml;
    }

    private function schemaLocationNeedsUpdate(DOMDocument $document): bool
    {
        $root = $document->documentElement;

        assert($root instanceof DOMElement);

        $schemaLocation = $root->getAttributeNS(
            'http://www.w3.org/2001/XMLSchema-instance',
            'noNamespaceSchemaLocation',
        );

        if (!str_contains($schemaLocation, '://')) {
            return false;
        }

        return $schemaLocation !== 'https://schema.phpunit.de/' . Version::series() . '/phpunit.xsd';
    }
}
