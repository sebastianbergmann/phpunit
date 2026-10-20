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

interface FileReader
{
    /**
     * @return string|null The contents of the file or null if it could not be read.
     */
    public function read(string $path): ?string;
}
