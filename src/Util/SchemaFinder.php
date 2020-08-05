<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use function is_file;
use function sprintf;
use PHPUnit\Runner\Version;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class SchemaFinder
{
    /**
     * @throws Exception
     */
    public function find(string $version): string
    {
        if ($version === Version::series()) {
            if (defined('__PHPUNIT_PHAR_ROOT__')) {
                $filename = __PHPUNIT_PHAR_ROOT__ . '/phpunit.xsd';
            } else {
                $filename = __DIR__ . '/../../phpunit.xsd';
            }
        } elseif (defined('__PHPUNIT_PHAR_ROOT__')) {
            $filename = __PHPUNIT_PHAR_ROOT__ . '/src/TextUI/XmlConfiguration/Migration/schema/phpunit-' . $version . '.xsd';
        } else {
            $filename = __DIR__ . '/../TextUI/XmlConfiguration/Migration/schema/phpunit-' . $version . '.xsd';
        }

        if (!is_file($filename)) {
            throw new Exception(
                sprintf(
                    'Schema for PHPUnit %s is not available',
                    $version
                )
            );
        }

        return $filename;
    }
}
