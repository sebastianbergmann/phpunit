<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestImpactAnalysis;

use function array_key_exists;
use function hash;
use function is_dir;
use function iterator_to_array;
use function ksort;
use PHPUnit\Runner\TestIndex\FileHasher;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Hashes what a test depends on, which is not always a file.
 *
 * A directory has no contents of its own to hash. It is hashed from the names
 * of the files in it, together with the hash of each of them, so that a file
 * that is added to it or removed from it changes the hash of the directory
 * just as a change to one of its files does. Files in subdirectories count:
 * fixtures are nested as often as not.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class PathHasher
{
    private readonly FileHasher $fileHasher;

    /**
     * @var array<non-empty-string, ?non-empty-string>
     */
    private array $hashes = [];

    public function __construct(?FileHasher $fileHasher = null)
    {
        if ($fileHasher === null) {
            $fileHasher = new FileHasher;
        }

        $this->fileHasher = $fileHasher;
    }

    /**
     * Returns null when the path is neither a file nor a directory, or when it
     * cannot be read.
     *
     * @param non-empty-string $path
     *
     * @return ?non-empty-string
     */
    public function hash(string $path): ?string
    {
        if (!is_dir($path)) {
            return $this->fileHasher->hash($path);
        }

        if (array_key_exists($path, $this->hashes)) {
            return $this->hashes[$path];
        }

        $this->hashes[$path] = $this->hashOfDirectory($path);

        return $this->hashes[$path];
    }

    /**
     * @param non-empty-string $directory
     *
     * @return ?non-empty-string
     */
    private function hashOfDirectory(string $directory): ?string
    {
        $files = [];

        foreach ($this->filesIn($directory) as $file) {
            $hash = $this->fileHasher->hash($file);

            if ($hash === null) {
                return null; // @codeCoverageIgnore
            }

            $files[$file] = $hash;
        }

        ksort($files);

        $digest = '';

        foreach ($files as $file => $hash) {
            $digest .= $file . "\0" . $hash . "\0";
        }

        return hash('xxh128', $digest);
    }

    /**
     * @param non-empty-string $directory
     *
     * @return list<non-empty-string>
     */
    private function filesIn(string $directory): array
    {
        $files = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
        );

        foreach (iterator_to_array($iterator) as $file) {
            if (!$file instanceof SplFileInfo || !$file->isFile()) {
                continue; // @codeCoverageIgnore
            }

            $path = $file->getPathname();

            if ($path === '') {
                continue; // @codeCoverageIgnore
            }

            $files[] = $path;
        }

        return $files;
    }
}
