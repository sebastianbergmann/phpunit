<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration\CodeCoverage;

use PHPUnit\TextUI\Configuration\CodeCoverage\Filter\DirectoryCollection;
use PHPUnit\TextUI\Configuration\FileCollection;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 * @psalm-immutable
 */
final class CodeCoverage
{
    /**
     * @var DirectoryCollection
     */
    private $directories;

    /**
     * @var FileCollection
     */
    private $files;

    /**
     * @var DirectoryCollection
     */
    private $excludeDirectories;

    /**
     * @var FileCollection
     */
    private $excludeFiles;

    /**
     * @var bool
     */
    private $includeUncoveredFilesInCodeCoverageReport;

    /**
     * @var bool
     */
    private $processUncoveredFilesForCodeCoverageReport;

    public function __construct(DirectoryCollection $directories, FileCollection $files, DirectoryCollection $excludeDirectories, FileCollection $excludeFiles, bool $includeUncoveredFilesInCodeCoverageReport, bool $processUncoveredFilesForCodeCoverageReport)
    {
        $this->directories                                = $directories;
        $this->files                                      = $files;
        $this->excludeDirectories                         = $excludeDirectories;
        $this->excludeFiles                               = $excludeFiles;
        $this->includeUncoveredFilesInCodeCoverageReport  = $includeUncoveredFilesInCodeCoverageReport;
        $this->processUncoveredFilesForCodeCoverageReport = $processUncoveredFilesForCodeCoverageReport;
    }

    public function hasNonEmptyListOfFilesToBeIncludedInCodeCoverageReport(): bool
    {
        return \count($this->directories) > 0 || \count($this->files) > 0;
    }

    public function directories(): DirectoryCollection
    {
        return $this->directories;
    }

    public function files(): FileCollection
    {
        return $this->files;
    }

    public function excludeDirectories(): DirectoryCollection
    {
        return $this->excludeDirectories;
    }

    public function excludeFiles(): FileCollection
    {
        return $this->excludeFiles;
    }

    public function includeUncoveredFilesInCodeCoverageReport(): bool
    {
        return $this->includeUncoveredFilesInCodeCoverageReport;
    }

    public function processUncoveredFilesForCodeCoverageReport(): bool
    {
        return $this->processUncoveredFilesForCodeCoverageReport;
    }
}
