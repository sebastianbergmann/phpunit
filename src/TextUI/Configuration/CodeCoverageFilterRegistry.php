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

    public static function init(Configuration $configuration): void
    {
        if (!$configuration->hasCoverageReport()) {
            return;
        }

        if (self::$configured) {
            return;
        }

        self::$filter = new Filter;

        if ($configuration->hasNonEmptyListOfFilesToBeIncludedInCodeCoverageReport()) {
            foreach ($configuration->coverageIncludeDirectories() as $directory) {
                self::$filter->includeDirectory(
                    $directory->path(),
                    $directory->suffix(),
                    $directory->prefix()
                );
            }

            foreach ($configuration->coverageIncludeFiles() as $file) {
                self::$filter->includeFile($file->path());
            }

            foreach ($configuration->coverageExcludeDirectories() as $directory) {
                self::$filter->excludeDirectory(
                    $directory->path(),
                    $directory->suffix(),
                    $directory->prefix()
                );
            }

            foreach ($configuration->coverageExcludeFiles() as $file) {
                self::$filter->excludeFile($file->path());
            }

            self::$configured = true;
        }
    }

    public static function configured(): bool
    {
        return self::$configured;
    }
}
