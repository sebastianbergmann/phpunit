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

use const PHP_EOL;
use PHPUnit\Util\Xml;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Migrator
{
    public function migrate(string $filename): string
    {
        $oldXsdFilename = __DIR__ . '/phpunit-9.2.xsd';

        if (defined('__PHPUNIT_PHAR_ROOT__')) {
            $oldXsdFilename = __PHPUNIT_PHAR_ROOT__ . '/src/TextUI/XmlConfiguration/Migration/phpunit-9.2.xsd';
        }

        $configurationDocument = Xml::loadFile(
            $filename,
            false,
            true,
            true
        );

        $validationErrors = Xml::validate($configurationDocument, $oldXsdFilename);

        if (!empty($validationErrors)) {
            $message = \sprintf(
                '"%s" is not a valid PHPUnit 9.2 XML configuration file:',
                $filename
            );

            foreach ($validationErrors as $line => $validationErrorsOnLine) {
                $message .= \sprintf(PHP_EOL . '  Line %d:' . PHP_EOL, $line);

                foreach ($validationErrorsOnLine as $validationError) {
                    $message .= sprintf('  - %s' . PHP_EOL, $validationError);
                }
            }

            throw new Exception($message);
        }

        foreach ((new MigrationBuilder)->build('9.2') as $migration) {
            $migration->migrate($configurationDocument);
        }

        $configurationDocument->formatOutput       = true;
        $configurationDocument->preserveWhiteSpace = false;

        return $configurationDocument->saveXML();
    }
}
