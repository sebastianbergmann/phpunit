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

use function preg_match;
use const PHP_OS_FAMILY;

/**
 * The number of logical cores, read from the default output of lscpu. Unlike
 * LscpuLogicalFinder, it works with the BSD lscpu, which accepts no option.
 *
 * @see https://github.com/NanXiao/lscpu/blob/master/lscpu.c
 * @see https://github.com/util-linux/util-linux/blob/53cd4fb62b027bc25437c06a1e3a002574c00972/tests/expected/lscpu/lscpu-s390-lpar
 */
final class LscpuRawLogicalFinder extends ProcOpenBasedFinder
{
    public function getCommand(): string
    {
        // util-linux translates the labels. On Windows, the command runs in
        // cmd.exe, which does not understand the "VAR=value command" syntax.
        return 'Windows' === PHP_OS_FAMILY
            ? 'set "LC_ALL=C" && lscpu'
            : 'LC_ALL=C lscpu';
    }

    protected function countCpuCores(string $process): ?int
    {
        // BSD lscpu. OpenBSD also prints the "Total CPU(s)" found, which
        // includes the inactive ones.
        foreach (['Active', 'Total'] as $label) {
            if (1 === preg_match('/^'.$label.' CPU\(s\):\s*(\d+)\s*$/m', $process, $matches)) {
                $count = (int) $matches[1];

                return $count > 0 ? $count : null;
            }
        }

        // util-linux. Its "CPU(s)" also counts the offline CPUs.
        if (1 === preg_match('/^On-line CPU\(s\) list:\s*(\S+)\s*$/m', $process, $matches)) {
            return CpuList::count($matches[1]);
        }

        return null;
    }

    public function toString(): string
    {
        return 'LscpuRawLogicalFinder';
    }
}
