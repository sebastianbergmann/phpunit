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

use const DIRECTORY_SEPARATOR;
use function array_values;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function is_string;
use function preg_match;
use function realpath;
use function serialize;
use function str_replace;
use function unserialize;
use SebastianBergmann\FileIterator\Facade as FileIteratorFacade;
use SplObjectStorage;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class SourceMapper
{
    /**
     * @var ?SplObjectStorage<Source, array<non-empty-string, true>>
     */
    private static ?SplObjectStorage $files = null;

    public static function saveTo(string $path, Source $source): bool
    {
        $map = (new self)->map($source);

        return file_put_contents($path, serialize($map)) !== false;
    }

    /**
     * @codeCoverageIgnore
     */
    public static function loadFrom(string $path, Source $source): void
    {
        $content = file_get_contents($path);

        if ($content === false) {
            return;
        }

        $map = unserialize($content, ['allowed_classes' => false]);

        if (!is_array($map)) {
            return;
        }

        $validated = [];

        foreach ($map as $file => $included) {
            if (!is_string($file) || $file === '' || $included !== true) {
                return;
            }

            $validated[$file] = true;
        }

        if (self::$files === null) {
            self::$files = new SplObjectStorage;
        }

        self::$files[$source] = $validated;
    }

    /**
     * @return array<non-empty-string, true>
     */
    public function map(Source $source): array
    {
        if (self::$files === null) {
            self::$files = new SplObjectStorage;
        }

        if (isset(self::$files[$source])) {
            return self::$files[$source];
        }

        $files = [];

        $directories = $this->aggregateDirectories($source->includeDirectories());

        foreach ($directories as ['path' => $path, 'prefixes' => $prefixes, 'suffixes' => $suffixes]) {
            $basePath = realpath($path);

            foreach ((new FileIteratorFacade)->getFilesAsArray($path, $suffixes, $prefixes) as $file) {
                $file = realpath($file);

                // @codeCoverageIgnoreStart
                if ($file === false) {
                    continue;
                }
                // @codeCoverageIgnoreEnd

                if ($this->isInHiddenDirectory($file, $basePath)) {
                    continue;
                }

                $files[$file] = true;
            }
        }

        foreach ($source->includeFiles() as $file) {
            $file = realpath($file->path());

            if ($file === false) {
                continue;
            }

            $files[$file] = true;
        }

        $directories = $this->aggregateDirectories($source->excludeDirectories());

        foreach ($directories as ['path' => $path, 'prefixes' => $prefixes, 'suffixes' => $suffixes]) {
            foreach ((new FileIteratorFacade)->getFilesAsArray($path, $suffixes, $prefixes) as $file) {
                $file = realpath($file);

                // @codeCoverageIgnoreStart
                if ($file === false) {
                    continue;
                }
                // @codeCoverageIgnoreEnd

                if (!isset($files[$file])) {
                    continue;
                }

                unset($files[$file]);
            }
        }

        foreach ($source->excludeFiles() as $file) {
            $file = realpath($file->path());

            if ($file === false) {
                continue;
            }

            if (!isset($files[$file])) {
                continue;
            }

            unset($files[$file]);
        }

        self::$files[$source] = $files;

        return $files;
    }

    /**
     * @return array<non-empty-string, true>
     */
    public function mapForCodeCoverage(Source $source): array
    {
        $files = $this->map($source);

        foreach ($source->includeDirectories() as $directory) {
            if ($directory->includeInCodeCoverage()) {
                continue;
            }

            foreach ((new FileIteratorFacade)->getFilesAsArray($directory->path(), $directory->suffix(), $directory->prefix()) as $file) {
                $file = realpath($file);

                // @codeCoverageIgnoreStart
                if ($file === false) {
                    continue;
                }
                // @codeCoverageIgnoreEnd

                unset($files[$file]);
            }
        }

        foreach ($source->includeFiles() as $file) {
            if ($file->includeInCodeCoverage()) {
                continue;
            }

            $path = realpath($file->path());

            if ($path === false) {
                continue;
            }

            unset($files[$path]);
        }

        return $files;
    }

    private function isInHiddenDirectory(string $path, false|string $basePath): bool
    {
        $relativePath = str_replace((string) $basePath, '', $path);

        $separator = DIRECTORY_SEPARATOR === '\\' ? '\\\\' : '/';

        return preg_match('=' . $separator . '\.[^' . $separator . ']*' . $separator . '=', $relativePath) === 1;
    }

    /**
     * @return list<array{path: non-empty-string, prefixes: list<non-empty-string>, suffixes: list<non-empty-string>}>
     */
    private function aggregateDirectories(FilterDirectoryCollection $directories): array
    {
        $aggregated = [];

        foreach ($directories as $directory) {
            $path = $directory->path();

            if (!isset($aggregated[$path])) {
                $aggregated[$path] = [
                    'path'     => $path,
                    'prefixes' => [],
                    'suffixes' => [],
                ];
            }

            $prefix = $directory->prefix();

            if ($prefix !== '') {
                $aggregated[$path]['prefixes'][] = $prefix;
            }

            $suffix = $directory->suffix();

            if ($suffix !== '') {
                $aggregated[$path]['suffixes'][] = $suffix;
            }
        }

        return array_values($aggregated);
    }
}
