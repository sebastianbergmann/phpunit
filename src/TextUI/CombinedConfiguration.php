<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI;

use function assert;
use PHPUnit\Event\Facade;
use PHPUnit\TextUI\CliArguments\Configuration as CliConfiguration;
use PHPUnit\TextUI\XmlConfiguration\Configuration as XmlConfiguration;

/**
 * CLI options and XML configuration are static within a single PHPUnit process.
 * It is therefore okay to use a Singleton registry here.
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class CombinedConfiguration implements Configuration
{
    private static ?self $instance = null;

    public static function get(): self
    {
        assert(self::$instance instanceof self);

        return self::$instance;
    }

    public static function combine(XmlConfiguration $xmlConfiguration, CliConfiguration $cliConfiguration): void
    {
        self::$instance = new self;

        Facade::emitter()->testRunnerConfigured(self::$instance);
    }

    private function __construct()
    {
    }
}
