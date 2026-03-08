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

use function realpath;
use SebastianBergmann\FileFilter\Builder as FilterBuilder;
use SebastianBergmann\FileFilter\Filter as FileFilter;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class FileFilterMapper
{
    public function map(Source $source): FileFilter
    {
        return (new FilterBuilder)->build(
            $this->directories($source->includeDirectories()),
            $this->files($source->includeFiles()),
            $this->directories($source->excludeDirectories()),
            $this->files($source->excludeFiles()),
        );
    }

    /**
     * @return list<array{path: non-empty-string, prefix: string, suffix: string}>
     */
    private function directories(FilterDirectoryCollection $directories): array
    {
        $result = [];

        foreach ($directories as $directory) {
            $path = realpath($directory->path());

            $result[] = [
                'path'   => $path !== false ? $path : $directory->path(),
                'prefix' => $directory->prefix(),
                'suffix' => $directory->suffix(),
            ];
        }

        return $result;
    }

    /**
     * @return list<non-empty-string>
     */
    private function files(FilterFileCollection $files): array
    {
        $result = [];

        foreach ($files as $file) {
            $path = realpath($file->path());

            $result[] = $path !== false ? $path : $file->path();
        }

        return $result;
    }
}
