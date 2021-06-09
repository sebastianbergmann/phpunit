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
use PHPUnit\TextUI\CliArguments\Configuration as CliConfiguration;
use PHPUnit\TextUI\XmlConfiguration\Configuration as XmlConfiguration;

/**
 * CLI options and XML configuration are static within a single PHPUnit process.
 * It is therefore okay to use a Singleton registry here.
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Configuration
{
    private static ?self $instance = null;

    /**
     * @psalm-var list<string>
     */
    private array $testSuffixes;

    public static function get(): self
    {
        assert(self::$instance instanceof self);

        return self::$instance;
    }

    public static function initFromCli(CliConfiguration $cliConfiguration): void
    {
        $testSuffixes = ['Test.php', '.phpt'];

        if ($cliConfiguration->hasTestSuffixes()) {
            $testSuffixes = $cliConfiguration->testSuffixes();
        }

        self::$instance = new self($testSuffixes);
    }

    public static function initFromCliAndXml(CliConfiguration $cliConfiguration, XmlConfiguration $xmlConfiguration): void
    {
        $testSuffixes = ['Test.php', '.phpt'];

        if ($cliConfiguration->hasTestSuffixes()) {
            $testSuffixes = $cliConfiguration->testSuffixes();
        }

        self::$instance = new self($testSuffixes);
    }

    /**
     * @psalm-param list<string> $testSuffixes
     */
    private function __construct(array $testSuffixes)
    {
        $this->testSuffixes = $testSuffixes;
    }

    /**
     * @psalm-return list<string>
     */
    public function testSuffixes(): array
    {
        return $this->testSuffixes;
    }
}
