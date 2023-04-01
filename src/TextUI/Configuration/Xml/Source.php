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

    public function __construct(FilterDirectoryCollection $directories, FileCollection $files, FilterDirectoryCollection $excludeDirectories, FileCollection $excludeFiles)
    {
        $this->directories        = $directories;
        $this->files              = $files;
        $this->excludeDirectories = $excludeDirectories;
        $this->excludeFiles       = $excludeFiles;
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
}
