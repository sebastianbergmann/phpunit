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
use PHPUnit\Util\Xml\SchemaFinder;
use PHPUnit\Util\Xml\Validator;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Migrator
{
    /**
     * @throws MigrationBuilderException
     * @throws MigrationException
     * @throws Exception
     * @throws XmlException
     */
    public function migrate(string $filename): string
    {
        $oldXsdFilename = (new SchemaFinder)->find('9.2');

        $configurationDocument = (new XmlLoader)->loadFile(
            $filename,
            false,
            true,
            true
        );

        $validationResult = (new Validator)->validate($configurationDocument, $oldXsdFilename);

        if ($validationResult->hasValidationErrors()) {
            throw new Exception(
                sprintf(
                    '"%s" is not a valid PHPUnit 9.2 XML configuration file:%s',
                    $filename,
                    $validationResult->asString()
                )
            );
        }

        foreach ((new MigrationBuilder)->build('9.2') as $migration) {
            $migration->migrate($configurationDocument);
        }

        $configurationDocument->formatOutput       = true;
        $configurationDocument->preserveWhiteSpace = false;

        return $configurationDocument->saveXML();
    }
}
