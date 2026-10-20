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

/**
 * Find the number of logical CPU cores for FreeSBD, Solaris and the likes.
 *
 * @see https://twitter.com/freebsdfrau/status/1052016199452700678?s=20&t=M2pHkRqmmna-UF68lfL2hw
 */
final class NProcessorFinder extends ProcOpenBasedFinder
{
    protected function getCommand(): string
    {
        return 'getconf NPROCESSORS_ONLN';
    }

    public function toString(): string
    {
        return 'NProcessorFinder';
    }
}
