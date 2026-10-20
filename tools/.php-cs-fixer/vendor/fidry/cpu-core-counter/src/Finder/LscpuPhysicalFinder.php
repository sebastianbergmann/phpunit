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

use function count;
use function explode;
use function is_array;
use function preg_grep;
use const PHP_EOL;

/**
 * The number of physical processors.
 *
 * Known limitation: on CPUs that mix several core types (e.g. ARM
 * big.LITTLE or Apple Silicon), lscpu numbers the cores of each CPU type
 * starting from zero, so this finder undercounts. For example, an 8-core
 * Cortex-A510/A710/A715/X3 reports only 3 distinct core IDs.
 *
 * @see https://stackoverflow.com/a/23378780/5846754
 * @see https://github.com/util-linux/util-linux/blob/53cd4fb62b027bc25437c06a1e3a002574c00972/sys-utils/lscpu.c#L1545-L1553
 * @see https://github.com/util-linux/util-linux/blob/53cd4fb62b027bc25437c06a1e3a002574c00972/sys-utils/lscpu.c#L302-L321
 * @see https://github.com/util-linux/util-linux/blob/53cd4fb62b027bc25437c06a1e3a002574c00972/sys-utils/lscpu-topology.c#L140-L227
 * @see https://github.com/util-linux/util-linux/blob/53cd4fb62b027bc25437c06a1e3a002574c00972/tests/expected/lscpu/lscpu-arm-A510-A710-A715-X3
 */
final class LscpuPhysicalFinder extends ProcOpenBasedFinder
{
    public function toString(): string
    {
        return 'LscpuPhysicalFinder';
    }

    public function getCommand(): string
    {
        return 'lscpu -p';
    }

    protected function countCpuCores(string $process): ?int
    {
        $lines = explode(PHP_EOL, $process);
        /** @var string[]|false $actualLines */
        $actualLines = preg_grep('/^\d+,/', $lines);

        if (!is_array($actualLines)) {
            return null;
        }

        $cores = [];
        foreach ($actualLines as $line) {
            $core = explode(',', $line)[1];

            // Unknown core: lscpu prints an empty value, or "-" with --physical.
            if ('' === $core || '-' === $core) {
                continue;
            }

            $cores[$core] = true;
        }

        $count = count($cores);

        return 0 === $count ? null : $count;
    }
}
