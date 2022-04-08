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
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\FilterMapper;
use PHPUnit\TextUI\XmlConfiguration\Configuration as XmlConfiguration;
use SebastianBergmann\CodeCoverage\Filter;

/**
 * CLI options and XML configuration are static within a single PHPUnit process.
 * It is therefore okay to use a Singleton registry here.
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class CodeCoverageFilterRegistry
{
    private static ?Filter $filter  = null;
    private static bool $configured = false;

    public static function get(): Filter
    {
        assert(self::$filter !== null);

        return self::$filter;
    }

    public static function init(CliConfiguration $cliConfiguration, XmlConfiguration $xmlConfiguration): void
    {
        self::$filter = new Filter;

        if ($cliConfiguration->hasCoverageFilter()) {
            foreach ($cliConfiguration->coverageFilter() as $directory) {
                self::$filter->includeDirectory($directory);
            }

            self::$configured = true;
        }

        if ($xmlConfiguration->codeCoverage()->hasNonEmptyListOfFilesToBeIncludedInCodeCoverageReport()) {
            (new FilterMapper)->map(
                self::$filter,
                $xmlConfiguration->codeCoverage()
            );

            self::$configured = true;
        }
    }

    public static function configured(): bool
    {
        return self::$configured;
    }
}
