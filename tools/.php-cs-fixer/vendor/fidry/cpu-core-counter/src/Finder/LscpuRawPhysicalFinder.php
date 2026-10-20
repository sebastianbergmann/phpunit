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

use function explode;
use function filter_var;
use function is_int;
use function preg_match;
use function strtolower;
use const FILTER_VALIDATE_INT;
use const PHP_OS_FAMILY;

/**
 * The number of physical cores, read from the default output of lscpu. Unlike
 * LscpuPhysicalFinder, it works with the BSD lscpu, which accepts no option.
 *
 * It sums "Core(s) per socket" × "Socket(s)" (or per cluster × "Cluster(s)")
 * of each CPU type. If a CPU type has an unknown or missing count, e.g. with
 * the books and drawers of s390, the count is unknown.
 *
 * @see https://github.com/NanXiao/lscpu/blob/master/lscpu.c
 * @see https://github.com/util-linux/util-linux/blob/53cd4fb62b027bc25437c06a1e3a002574c00972/tests/expected/lscpu/lscpu-arm-A510-A710-A715-X3
 */
final class LscpuRawPhysicalFinder extends ProcOpenBasedFinder
{
    private const POSITIVE_INT = ['options' => ['min_range' => 1]];

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
        $count = 0;
        $pendingUnit = null;
        $pendingCores = null;

        foreach (explode("\n", $process) as $line) {
            if (1 === preg_match('/^Core\(s\) per (socket|cluster):\s*(.*?)\s*$/', $line, $matches)) {
                if (null !== $pendingUnit) {
                    return null;
                }

                $pendingUnit = $matches[1];
                $pendingCores = $matches[2];

                continue;
            }

            if (null === $pendingUnit
                || 1 !== preg_match('/^(Socket|Cluster)\(s\):\s*(.*?)\s*$/', $line, $matches)
                || strtolower($matches[1]) !== $pendingUnit
            ) {
                continue;
            }

            $cores = filter_var($pendingCores, FILTER_VALIDATE_INT, self::POSITIVE_INT);
            $units = filter_var($matches[2], FILTER_VALIDATE_INT, self::POSITIVE_INT);

            if (!is_int($cores) || !is_int($units)) {
                return null;
            }

            $count += $cores * $units;
            $pendingUnit = null;
        }

        return null === $pendingUnit && $count > 0 ? $count : null;
    }

    public function toString(): string
    {
        return 'LscpuRawPhysicalFinder';
    }
}
