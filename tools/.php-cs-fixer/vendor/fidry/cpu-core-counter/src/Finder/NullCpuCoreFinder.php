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

namespace Fidry\CpuCoreCounter\Finder;

final class NullCpuCoreFinder implements CpuCoreFinder
{
    public function diagnose(): string
    {
        return 'Will return "null".';
    }

    public function find(): ?int
    {
        return null;
    }

    public function toString(): string
    {
        return 'NullCpuCoreFinder';
    }
}
