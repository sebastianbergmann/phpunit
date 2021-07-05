<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

use function assert;
use PHPUnit\TextUI\CliArguments\Configuration as CliConfiguration;
use PHPUnit\TextUI\XmlConfiguration\Configuration as XmlConfiguration;

/**
 * CLI options and XML configuration are static within a single PHPUnit process.
 * It is therefore okay to use a Singleton registry here.
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Registry
{
    private static ?Configuration $instance = null;

    public static function get(): Configuration
    {
        assert(self::$instance instanceof Configuration);

        return self::$instance;
    }

    public static function init(CliConfiguration $cliConfiguration, XmlConfiguration $xmlConfiguration): Configuration
    {
        self::$instance = (new Merger)->merge($cliConfiguration, $xmlConfiguration);

        return self::$instance;
    }
}
