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

namespace Fidry\CpuCoreCounter;

use RuntimeException;

final class NumberOfCpuCoreNotFound extends RuntimeException
{
    public static function create(): self
    {
        return new self(
            'Could not find the number of CPU cores available.'
        );
    }
}
