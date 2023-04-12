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

    public function __construct(FilterDirectoryCollection $directories, FileCollection $files, FilterDirectoryCollection $excludeDirectories, FileCollection $excludeFiles, bool $restrictDeprecations, bool $restrictNotices, bool $restrictWarnings)
    {
        $this->directories          = $directories;
        $this->files                = $files;
        $this->excludeDirectories   = $excludeDirectories;
        $this->excludeFiles         = $excludeFiles;
        $this->restrictDeprecations = $restrictDeprecations;
        $this->restrictNotices      = $restrictNotices;
        $this->restrictWarnings     = $restrictWarnings;
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
}
