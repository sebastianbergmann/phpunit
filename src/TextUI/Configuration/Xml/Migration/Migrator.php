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

use function sprintf;
use PHPUnit\Util\Xml\Exception as XmlException;
use PHPUnit\Util\Xml\Loader as XmlLoader;
use PHPUnit\Util\Xml\SchemaDetector;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Migrator
{
    /**
     * @throws Exception
     * @throws MigrationBuilderException
     * @throws MigrationException
     * @throws XmlException
     */
    public function migrate(string $filename): string
    {
        $origin = (new SchemaDetector)->detect($filename);

        if (!$origin->detected()) {
            throw new Exception(
                sprintf(
                    '"%s" is not a valid PHPUnit XML configuration file that can be migrated',
                    $filename,
                )
            );
        }

        $configurationDocument = (new XmlLoader)->loadFile(
            $filename,
            false,
            true,
            true
        );

        foreach ((new MigrationBuilder)->build($origin->version()) as $migration) {
            $migration->migrate($configurationDocument);
        }

        $configurationDocument->formatOutput       = true;
        $configurationDocument->preserveWhiteSpace = false;

        return $configurationDocument->saveXML();
    }
}
