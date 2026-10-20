<?php

/*
 * This file is part of the Fidry CPUCounter Config package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fidry\CpuCoreCounter\FileReader;

use function file_get_contents;
use function function_exists;
use function is_file;

/**
 * @author Anders Jenbo <anders@jenbo.dk> (@AJenbo)
 */
final class NativeFileReader implements FileReader
{
    public function read(string $path): ?string
    {
        // The functions may be disabled, e.g. with disable_functions.
        if (
            !function_exists('is_file')
            || !function_exists('file_get_contents')
        ) {
            return null;
        }

        // The files may be missing or out of reach, e.g. with open_basedir.
        if (!@is_file($path)) {
            return null;
        }

        $contents = @file_get_contents($path);

        return false === $contents
            ? null
            : $contents;
    }
}
