<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Cache;

use Symfony\Component\Filesystem\Exception\IOException;

/**
 * @author Andreas Möller <am@localheinz.com>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class FileHandler implements FileHandlerInterface
{
    private \SplFileInfo $fileInfo;

    private int $fileMTime = 0;

    public function __construct(string $file)
    {
        $this->fileInfo = new \SplFileInfo($file);
    }

    public function getFile(): string
    {
        return $this->fileInfo->getPathname();
    }

    public function read(): ?CacheInterface
    {
        // Refuse to follow a symlinked cache path: a checkout-supplied symlink at the predictable
        // default cache location could otherwise be read/written through, reaching a file outside
        // the intended cache location.
        if ($this->fileInfo->isLink() || !$this->fileInfo->isFile() || !$this->fileInfo->isReadable()) {
            return null;
        }

        $fileObject = $this->fileInfo->openFile('r');

        $cache = $this->readFromHandle($fileObject);
        $this->fileMTime = $this->getFileCurrentMTime();

        unset($fileObject); // explicitly close file handler

        return $cache;
    }

    public function write(CacheInterface $cache): void
    {
        $this->ensureFileIsWriteable();

        $fileObject = $this->fileInfo->openFile('r+');

        if (method_exists($cache, 'backfillHashes') && $this->fileMTime < $this->getFileCurrentMTime()) {
            $resultOfFlock = $fileObject->flock(\LOCK_EX);
            if (false === $resultOfFlock) {
                // Lock failed, OK - we continue without the lock.
                // noop
            }

            $oldCache = $this->readFromHandle($fileObject);

            $fileObject->rewind();

            if (null !== $oldCache) {
                $cache->backfillHashes($oldCache);
            }
        }

        $resultOfTruncate = $fileObject->ftruncate(0);
        if (false === $resultOfTruncate) {
            // Truncate failed. OK - we do not save the cache.
            return;
        }

        $resultOfWrite = $fileObject->fwrite($cache->toJson());
        if (false === $resultOfWrite) {
            // Write failed. OK - we did not save the cache.
            return;
        }

        $resultOfFlush = $fileObject->fflush();
        if (false === $resultOfFlush) {
            // Flush failed. OK - part of cache can be missing, in case this was last chunk in this pid.
            // noop
        }

        $this->fileMTime = time(); // we could take the fresh `mtime` of file that we just modified with `$this->getFileCurrentMTime()`, but `time()` should be good enough here and reduce IO operation
    }

    private function getFileCurrentMTime(): int
    {
        clearstatcache(true, $this->fileInfo->getPathname());

        $mtime = $this->fileInfo->getMTime();

        if (false === $mtime) {
            // cannot check mtime? OK - let's pretend file is old.
            $mtime = 0;
        }

        return $mtime;
    }

    private function readFromHandle(\SplFileObject $fileObject): ?CacheInterface
    {
        try {
            $size = $fileObject->getSize();
            if (false === $size || 0 === $size) {
                return null;
            }

            $content = $fileObject->fread($size);

            if (false === $content) {
                return null;
            }

            return Cache::fromJson($content);
        } catch (\InvalidArgumentException $exception) {
            return null;
        }
    }

    private function ensureFileIsWriteable(): void
    {
        if ($this->fileInfo->isLink()) {
            // Do not write through a symbolic link: a checkout-supplied symlink at the predictable
            // default cache path would otherwise be followed and its target truncated/overwritten
            // (this also fires for `check`/`--dry-run`, which still writes the cache).
            throw new IOException(
                \sprintf('Cannot write cache file "%s" as it is a symbolic link.', $this->fileInfo->getPathname()),
                0,
                null,
                $this->fileInfo->getPathname(),
            );
        }

        if ($this->fileInfo->isFile() && $this->fileInfo->isWritable()) {
            // all good
            return;
        }

        if ($this->fileInfo->isDir()) {
            throw new IOException(
                \sprintf('Cannot write cache file "%s" as the location exists as directory.', $this->fileInfo->getRealPath()),
                0,
                null,
                $this->fileInfo->getPathname(),
            );
        }

        if ($this->fileInfo->isFile() && !$this->fileInfo->isWritable()) {
            throw new IOException(
                \sprintf('Cannot write to file "%s" as it is not writable.', $this->fileInfo->getRealPath()),
                0,
                null,
                $this->fileInfo->getPathname(),
            );
        }

        $this->createFile($this->fileInfo->getPathname());
    }

    private function createFile(string $file): void
    {
        $dir = \dirname($file);

        // Ensure path is created, but ignore if already exists. FYI: ignore EA suggestion in IDE,
        // `mkdir()` returns `false` for existing paths, so we can't mix it with `is_dir()` in one condition.
        if (!@is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        if (!@is_dir($dir)) {
            throw new IOException(
                \sprintf('Directory of cache file "%s" does not exists and couldn\'t be created.', $file),
                0,
                null,
                $file,
            );
        }

        @touch($file); // with default 0644 file mode
    }
}
