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

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 * @psalm-immutable
 */
final class Filter
{
    /**
     * @var FilterDirectoryCollection
     */
    private $directories;

    /**
     * @var FilterFileCollection
     */
    private $files;

    /**
     * @var FilterDirectoryCollection
     */
    private $excludeDirectories;

    /**
     * @var FilterFileCollection
     */
    private $excludeFiles;

    /**
     * @var bool
     */
    private $addUncoveredFilesFromWhitelist;

    /**
     * @var bool
     */
    private $processUncoveredFilesFromWhitelist;

    public function __construct(FilterDirectoryCollection $directories, FilterFileCollection $files, FilterDirectoryCollection $excludeDirectories, FilterFileCollection $excludeFiles, bool $addUncoveredFilesFromWhitelist, bool $processUncoveredFilesFromWhitelist)
    {
        $this->directories                        = $directories;
        $this->files                              = $files;
        $this->excludeDirectories                 = $excludeDirectories;
        $this->excludeFiles                       = $excludeFiles;
        $this->addUncoveredFilesFromWhitelist     = $addUncoveredFilesFromWhitelist;
        $this->processUncoveredFilesFromWhitelist = $processUncoveredFilesFromWhitelist;
    }

    public function hasNonEmptyWhitelist(): bool
    {
        return \count($this->directories) > 0 || \count($this->files) > 0;
    }

    public function directories(): FilterDirectoryCollection
    {
        return $this->directories;
    }

    public function files(): FilterFileCollection
    {
        return $this->files;
    }

    public function excludeDirectories(): FilterDirectoryCollection
    {
        return $this->excludeDirectories;
    }

    public function excludeFiles(): FilterFileCollection
    {
        return $this->excludeFiles;
    }

    public function addUncoveredFilesFromWhitelist(): bool
    {
        return $this->addUncoveredFilesFromWhitelist;
    }

    public function processUncoveredFilesFromWhitelist(): bool
    {
        return $this->processUncoveredFilesFromWhitelist;
    }
}
