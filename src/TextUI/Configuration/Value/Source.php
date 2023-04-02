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

use function basename;
use function str_ends_with;
use function str_starts_with;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @psalm-immutable
 */
final class Source
{
    private readonly FilterDirectoryCollection $includeDirectories;
    private readonly FileCollection $includeFiles;
    private readonly FilterDirectoryCollection $excludeDirectories;
    private readonly FileCollection $excludeFiles;

    public function __construct(FilterDirectoryCollection $includeDirectories, FileCollection $includeFiles, FilterDirectoryCollection $excludeDirectories, FileCollection $excludeFiles)
    {
        $this->includeDirectories = $includeDirectories;
        $this->includeFiles       = $includeFiles;
        $this->excludeDirectories = $excludeDirectories;
        $this->excludeFiles       = $excludeFiles;
    }

    public function includes(string $path): bool
    {
        if ($this->fileCollectionHas($this->excludeFiles, $path)) {
            return false;
        }

        if ($this->fileCollectionHas($this->includeFiles, $path)) {
            return true;
        }

        return $this->directoryMatches($this->includeDirectories, $path) &&
               !$this->directoryMatches($this->excludeDirectories, $path);
    }

    public function includeDirectories(): FilterDirectoryCollection
    {
        return $this->includeDirectories;
    }

    public function includeFiles(): FileCollection
    {
        return $this->includeFiles;
    }

    public function excludeDirectories(): FilterDirectoryCollection
    {
        return $this->excludeDirectories;
    }

    public function excludeFiles(): FileCollection
    {
        return $this->excludeFiles;
    }

    public function notEmpty(): bool
    {
        return $this->includeDirectories->notEmpty() || $this->includeFiles->notEmpty();
    }

    private function directoryMatches(FilterDirectoryCollection $directories, string $path): bool
    {
        $filename = basename($path);

        foreach ($directories as $directory) {
            if (!str_starts_with($path, $directory->path())) {
                continue;
            }

            if (!empty($directory->prefix()) && !str_starts_with($filename, $directory->prefix())) {
                continue;
            }

            if (!empty($directory->suffix()) && !str_ends_with($filename, $directory->suffix())) {
                continue;
            }

            return true;
        }

        return false;
    }

    private function fileCollectionHas(FileCollection $files, string $path): bool
    {
        foreach ($files as $file) {
            if ($file->path() === $path) {
                return true;
            }
        }

        return false;
    }
}
