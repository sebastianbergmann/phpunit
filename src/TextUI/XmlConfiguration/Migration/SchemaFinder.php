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

use function is_file;
use function sprintf;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class SchemaFinder
{
    /**
     * @throws MigrationException
     */
    public function find(string $version): string
    {
        if (defined('__PHPUNIT_PHAR_ROOT__')) {
            $filename = __PHPUNIT_PHAR_ROOT__ . '/src/TextUI/XmlConfiguration/Migration/schema/phpunit-' . $version . '.xsd';
        } else {
            $filename = __DIR__ . '/schema/phpunit-' . $version . '.xsd';
        }

        if (!is_file($filename)) {
            throw new MigrationException(
                sprintf(
                    'Schema for PHPUnit %s is not available',
                    $version
                )
            );
        }

        return $filename;
    }
}
