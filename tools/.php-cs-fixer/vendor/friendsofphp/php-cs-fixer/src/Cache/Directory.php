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

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class Directory implements DirectoryInterface
{
    private string $directoryName;

    public function __construct(string $directoryName)
    {
        $this->directoryName = $directoryName;
    }

    public function getRelativePathTo(string $file): string
    {
        $file = $this->normalizePath($file);

        if (
            '' === $this->directoryName
            || !str_starts_with(strtolower($file), strtolower($this->directoryName.\DIRECTORY_SEPARATOR))
        ) {
            return $file;
        }

        return substr($file, \strlen($this->directoryName) + 1);
    }

    private function normalizePath(string $path): string
    {
        return str_replace(['\\', '/'], \DIRECTORY_SEPARATOR, $path);
    }
}
