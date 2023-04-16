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

use function count;
use PHPUnit\TextUI\Configuration\FileCollection;
use PHPUnit\TextUI\Configuration\FilterDirectoryCollection;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @psalm-immutable
 */
final class Source
{
    private readonly FilterDirectoryCollection $directories;
    private readonly FileCollection $files;
    private readonly FilterDirectoryCollection $excludeDirectories;
    private readonly FileCollection $excludeFiles;
    private readonly bool $restrictDeprecations;
    private readonly bool $restrictNotices;
    private readonly bool $restrictWarnings;
    private readonly bool $ignoreSuppressionOfDeprecations;
    private readonly bool $ignoreSuppressionOfPhpDeprecations;
    private readonly bool $ignoreSuppressionOfErrors;
    private readonly bool $ignoreSuppressionOfNotices;
    private readonly bool $ignoreSuppressionOfPhpNotices;
    private readonly bool $ignoreSuppressionOfWarnings;
    private readonly bool $ignoreSuppressionOfPhpWarnings;

    public function __construct(FilterDirectoryCollection $directories, FileCollection $files, FilterDirectoryCollection $excludeDirectories, FileCollection $excludeFiles, bool $restrictDeprecations, bool $restrictNotices, bool $restrictWarnings, bool $ignoreSuppressionOfDeprecations, bool $ignoreSuppressionOfPhpDeprecations, bool $ignoreSuppressionOfErrors, bool $ignoreSuppressionOfNotices, bool $ignoreSuppressionOfPhpNotices, bool $ignoreSuppressionOfWarnings, bool $ignoreSuppressionOfPhpWarnings)
    {
        $this->directories                        = $directories;
        $this->files                              = $files;
        $this->excludeDirectories                 = $excludeDirectories;
        $this->excludeFiles                       = $excludeFiles;
        $this->restrictDeprecations               = $restrictDeprecations;
        $this->restrictNotices                    = $restrictNotices;
        $this->restrictWarnings                   = $restrictWarnings;
        $this->ignoreSuppressionOfDeprecations    = $ignoreSuppressionOfDeprecations;
        $this->ignoreSuppressionOfPhpDeprecations = $ignoreSuppressionOfPhpDeprecations;
        $this->ignoreSuppressionOfErrors          = $ignoreSuppressionOfErrors;
        $this->ignoreSuppressionOfNotices         = $ignoreSuppressionOfNotices;
        $this->ignoreSuppressionOfPhpNotices      = $ignoreSuppressionOfPhpNotices;
        $this->ignoreSuppressionOfWarnings        = $ignoreSuppressionOfWarnings;
        $this->ignoreSuppressionOfPhpWarnings     = $ignoreSuppressionOfPhpWarnings;
    }

    public function hasNonEmptyListOfFiles(): bool
    {
        return count($this->directories) > 0 || count($this->files) > 0;
    }

    public function directories(): FilterDirectoryCollection
    {
        return $this->directories;
    }

    public function files(): FileCollection
    {
        return $this->files;
    }

    public function excludeDirectories(): FilterDirectoryCollection
    {
        return $this->excludeDirectories;
    }

    public function excludeFiles(): FileCollection
    {
        return $this->excludeFiles;
    }

    public function restrictDeprecations(): bool
    {
        return $this->restrictDeprecations;
    }

    public function restrictNotices(): bool
    {
        return $this->restrictNotices;
    }

    public function restrictWarnings(): bool
    {
        return $this->restrictWarnings;
    }

    public function ignoreSuppressionOfDeprecations(): bool
    {
        return $this->ignoreSuppressionOfDeprecations;
    }

    public function ignoreSuppressionOfPhpDeprecations(): bool
    {
        return $this->ignoreSuppressionOfPhpDeprecations;
    }

    public function ignoreSuppressionOfErrors(): bool
    {
        return $this->ignoreSuppressionOfErrors;
    }

    public function ignoreSuppressionOfNotices(): bool
    {
        return $this->ignoreSuppressionOfNotices;
    }

    public function ignoreSuppressionOfPhpNotices(): bool
    {
        return $this->ignoreSuppressionOfPhpNotices;
    }

    public function ignoreSuppressionOfWarnings(): bool
    {
        return $this->ignoreSuppressionOfWarnings;
    }

    public function ignoreSuppressionOfPhpWarnings(): bool
    {
        return $this->ignoreSuppressionOfPhpWarnings;
    }
}
