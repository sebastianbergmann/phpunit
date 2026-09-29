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

use const SORT_STRING;
use function array_key_exists;
use function hash;
use function in_array;
use function is_dir;
use function ksort;
use function realpath;
use FilesystemIterator;
use PHPUnit\Runner\TestIndex\FileHasher;
use SplFileInfo;
use UnexpectedValueException;

/**
 * Hashes what a test depends on, which is not always a file.
 *
 * A directory has no contents of its own to hash. It is hashed from the names
 * of the files in it, together with the hash of each of them, so that a file
 * that is added to it or removed from it changes the hash of the directory
 * just as a change to one of its files does. Files in subdirectories count:
 * fixtures are nested as often as not.
 *
 * A file is named relative to the directory, and not by its absolute path: the
 * same directory in another checkout of the project has the same hash.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class PathHasher
{
    private readonly FileHasher $fileHasher;

    /**
     * Asking whether a path is a directory is a question about the file
     * system, and it is asked once per path rather than once per test that
     * refers to it.
     *
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
        if (array_key_exists($path, $this->hashes)) {
            return $this->hashes[$path];
        }

        if (is_dir($path)) {
            $hash = $this->hashOfDirectory($path);
        } else {
            $hash = $this->fileHasher->hash($path);
        }

        $this->hashes[$path] = $hash;

        return $hash;
    }

    /**
     * @param non-empty-string $directory
     *
     * @return ?non-empty-string
     */
    private function hashOfDirectory(string $directory): ?string
    {
        $filesInDirectory = $this->filesIn($directory);

        if ($filesInDirectory === null) {
            return null;
        }

        $files = [];

        foreach ($filesInDirectory as $name => $file) {
            $hash = $this->fileHasher->hash($file);

            if ($hash === null) {
                return null; // @codeCoverageIgnore
            }

            $files[$name] = $hash;
        }

        /*
         * A file whose name is a number is a key that is an integer, and the
         * names are compared as strings so that the order does not depend on
         * which of them are.
         */
        ksort($files, SORT_STRING);

        $digest = '';

        foreach ($files as $name => $hash) {
            $digest .= $name . "\0" . $hash . "\0";
        }

        return hash('xxh128', $digest);
    }

    /**
     * Returns null when the directory, or one of the directories beneath it,
     * cannot be read: what is in a directory that cannot be opened is not
     * known, and a hash of the rest of the directory would say that it did not
     * change when it may well have.
     *
     * Symbolic links are followed, the way the filter that decides which files
     * are first-party code follows them: a directory that is linked to is part
     * of the fixture, and a change to what is in it is a change to the fixture.
     *
     * A link that leads back to a directory the walk came through is not
     * followed. What it leads to has been seen already, and following it does
     * not end: the file system stops a path with more than a few dozen links
     * in it, which is not a limit that is reached before the number of paths
     * that lead to the same file has become unreasonable.
     *
     * Each file is keyed by its name relative to the directory the walk
     * started in, with '/' as the directory separator on every platform.
     *
     * @param non-empty-string       $directory
     * @param list<non-empty-string> $directoriesTheWalkCameThrough
     *
     * @return ?array<non-empty-string, non-empty-string>
     */
    private function filesIn(string $directory, string $prefix = '', array $directoriesTheWalkCameThrough = []): ?array
    {
        $resolved = realpath($directory);

        if ($resolved === false) {
            return null; // @codeCoverageIgnore
        }

        if (in_array($resolved, $directoriesTheWalkCameThrough, true)) {
            return [];
        }

        $directoriesTheWalkCameThrough[] = $resolved;

        try {
            $iterator = new FilesystemIterator($directory, FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS);
            // @codeCoverageIgnoreStart
        } catch (UnexpectedValueException) {
            return null;
        }
        // @codeCoverageIgnoreEnd

        $files = [];

        foreach ($iterator as $entry) {
            if (!$entry instanceof SplFileInfo) {
                continue; // @codeCoverageIgnore
            }

            $path     = $entry->getPathname();
            $filename = $entry->getFilename();

            if ($path === '' || $filename === '') {
                continue; // @codeCoverageIgnore
            }

            $name = $prefix . $filename;

            if ($entry->isDir()) {
                $filesInDirectory = $this->filesIn($path, $name . '/', $directoriesTheWalkCameThrough);

                if ($filesInDirectory === null) {
                    return null;
                }

                foreach ($filesInDirectory as $nameInDirectory => $file) {
                    $files[$nameInDirectory] = $file;
                }

                continue;
            }

            if (!$entry->isFile()) {
                continue; // @codeCoverageIgnore
            }

            $files[$name] = $path;
        }

        return $files;
    }
}
