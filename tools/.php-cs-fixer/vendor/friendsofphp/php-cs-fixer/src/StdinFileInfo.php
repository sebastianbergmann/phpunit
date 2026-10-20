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

namespace PhpCsFixer;

/**
 * @author Davi Koscianski Vidal <davividal@gmail.com>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class StdinFileInfo extends \SplFileInfo
{
    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function __toString(): string
    {
        return $this->getRealPath();
    }

    public function getRealPath(): string
    {
        // So file_get_contents & friends will work.
        // Warning - this stream is not seekable, so `file_get_contents` will work only once! Consider using `FileReader`.
        return 'php://stdin';
    }

    public function getATime(): int
    {
        return 0;
    }

    public function getBasename($suffix = null): string
    {
        return $this->getFilename();
    }

    public function getCTime(): int
    {
        return 0;
    }

    public function getExtension(): string
    {
        return '.php';
    }

    /**
     * @param null|class-string<\SplFileInfo> $class
     */
    public function getFileInfo($class = null): \SplFileInfo
    {
        throw new \BadMethodCallException(\sprintf('Method "%s" is not implemented.', __METHOD__));
    }

    public function getFilename(): string
    {
        /*
         * Useful so fixers depending on PHP-only files still work.
         *
         * The idea to use STDIN is to parse PHP-only files, so we can
         * assume that there will be always a PHP file out there.
         */

        return 'stdin.php';
    }

    public function getGroup(): int
    {
        return 0;
    }

    public function getInode(): int
    {
        return 0;
    }

    public function getLinkTarget(): string
    {
        return '';
    }

    public function getMTime(): int
    {
        return 0;
    }

    public function getOwner(): int
    {
        return 0;
    }

    public function getPath(): string
    {
        return '';
    }

    /**
     * @param null|class-string<\SplFileInfo> $class
     */
    public function getPathInfo($class = null): \SplFileInfo
    {
        throw new \BadMethodCallException(\sprintf('Method "%s" is not implemented.', __METHOD__));
    }

    public function getPathname(): string
    {
        return $this->getFilename();
    }

    public function getPerms(): int
    {
        return 0;
    }

    public function getSize(): int
    {
        return 0;
    }

    public function getType(): string
    {
        return 'file';
    }

    public function isDir(): bool
    {
        return false;
    }

    public function isExecutable(): bool
    {
        return false;
    }

    public function isFile(): bool
    {
        return true;
    }

    public function isLink(): bool
    {
        return false;
    }

    public function isReadable(): bool
    {
        return true;
    }

    public function isWritable(): bool
    {
        return false;
    }

    public function openFile($openMode = 'r', $useIncludePath = false, $context = null): \SplFileObject
    {
        throw new \BadMethodCallException(\sprintf('Method "%s" is not implemented.', __METHOD__));
    }
}
