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
use function max;
use function min;
use function preg_match;

/**
 * @internal
 *
 * @see https://man7.org/linux/man-pages/man7/cpuset.7.html (FORMATS)
 */
final class CpuList
{
    private const CPU_RANGE_REGEX = '/^(?<first>\d+)(?:-(?<last>\d+))?$/D';

    /**
     * @param string $cpuList E.g. "0-1,4" for the CPUs 0, 1 and 4.
     *
     * @return positive-int|null
     */
    public static function count(string $cpuList): ?int
    {
        $ranges = self::parse($cpuList);

        if (null === $ranges) {
            return null;
        }

        $count = 0;

        foreach ($ranges as [$first, $last]) {
            $count += $last - $first + 1;
        }

        return $count > 0 ? $count : null;
    }

    /**
     * Counts the CPUs present in both lists.
     *
     * @return positive-int|null
     */
    public static function countIntersection(string $cpuList, string $otherCpuList): ?int
    {
        $ranges = self::parse($cpuList);
        $otherRanges = self::parse($otherCpuList);

        if (null === $ranges || null === $otherRanges) {
            return null;
        }

        $count = 0;

        foreach ($ranges as [$first, $last]) {
            foreach ($otherRanges as [$otherFirst, $otherLast]) {
                $lastMin = min($last, $otherLast);
                $maxFirst = max($first, $otherFirst);

                $count += max(
                    0,
                    $lastMin - $maxFirst + 1
                );
            }
        }

        return $count > 0 ? $count : null;
    }

    /**
     * @return list<array{int, int}>|null The first and last CPU of each range.
     */
    private static function parse(string $cpuList): ?array
    {
        $ranges = [];

        foreach (explode(',', $cpuList) as $item) {
            if (1 !== preg_match(self::CPU_RANGE_REGEX, $item, $range)) {
                return null;
            }

            $first = (int) $range['first'];
            $last = isset($range['last']) ? (int) $range['last'] : $first;

            if ($last < $first) {
                return null;
            }

            $ranges[] = [$first, $last];
        }

        return $ranges;
    }

    private function __construct()
    {
    }
}
