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
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @psalm-immutable
 */
final class SourceFilter
{
    public function includes(Source $source, string $path): bool
    {
        if ($this->fileCollectionHas($source->excludeFiles(), $path)) {
            return false;
        }

        if ($this->fileCollectionHas($source->includeFiles(), $path)) {
            return true;
        }

        return $this->directoryMatches($source->includeDirectories(), $path) &&
               !$this->directoryMatches($source->excludeDirectories(), $path);
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
