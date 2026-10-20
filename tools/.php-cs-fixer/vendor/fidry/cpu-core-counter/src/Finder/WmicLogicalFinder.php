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
 * Find the number of logical CPU cores for Windows.
 *
 * @see https://github.com/paratestphp/paratest/blob/c163539818fd96308ca8dc60f46088461e366ed4/src/Runners/PHPUnit/Options.php#L912-L916
 */
final class WmicLogicalFinder extends ProcOpenBasedFinder
{
    protected function getCommand(): string
    {
        return 'wmic cpu get NumberOfLogicalProcessors';
    }

    public function toString(): string
    {
        return 'WmicLogicalFinder';
    }

    protected function countCpuCores(string $process): ?int
    {
        return $this->sumCpuCoresPerLine($process);
    }
}
