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

use Fidry\CpuCoreCounter\FileReader\FileReader;
use Fidry\CpuCoreCounter\FileReader\NativeFileReader;
use function count;
use function explode;
use function preg_match;
use function sprintf;
use const PHP_EOL;

/**
 * The number of physical processors, from the "physical id" and "core id"
 * lines of the cpuinfo file which is available on Linux systems and Windows
 * systems with a Linux sub-system. Unlike LscpuPhysicalFinder, it does not
 * need `proc_open` or util-linux.
 *
 * A core is identified by its (physical id, core id) pair: the core IDs are
 * only unique within a physical package, and the logical processors of a core
 * with SMT share the same pair.
 *
 * x86 prints these lines in each "processor" block, but ARM, RISC-V,
 * PowerPC or s390x, for example, do not. If a processor lacks either line,
 * the count is unknown.
 *
 * @see https://github.com/torvalds/linux/blob/v6.10/arch/x86/kernel/cpu/proc.c#L17-L29
 * @see https://docs.kernel.org/admin-guide/cputopology.html
 */
final class CpuInfoPhysicalFinder implements CpuCoreFinder
{
    private const CPU_INFO_PATH = '/proc/cpuinfo';

    private const PROCESSOR_LINE_REGEX = '/^processor\s*:/m';

    private const PHYSICAL_ID_LINE_REGEX = '/^physical id\s*:\s*(?<id>\d+)$/m';

    private const CORE_ID_LINE_REGEX = '/^core id\s*:\s*(?<id>\d+)$/m';

    /**
     * @var FileReader
     */
    private $fileReader;

    public function __construct(?FileReader $fileReader = null)
    {
        $this->fileReader = $fileReader ?? new NativeFileReader();
    }

    public function diagnose(): string
    {
        $cpuInfo = $this->fileReader->read(self::CPU_INFO_PATH);

        if (null === $cpuInfo) {
            return sprintf(
                'Could not read the file "%s".',
                self::CPU_INFO_PATH
            );
        }

        return sprintf(
            'Found the file "%s" with the content:%s%s%sWill return "%s".',
            self::CPU_INFO_PATH,
            PHP_EOL,
            $cpuInfo,
            PHP_EOL,
            self::countCpuCores($cpuInfo) ?? 'null'
        );
    }

    /**
     * @return positive-int|null
     */
    public function find(): ?int
    {
        $cpuInfo = $this->fileReader->read(self::CPU_INFO_PATH);

        return null === $cpuInfo ? null : self::countCpuCores($cpuInfo);
    }

    public function toString(): string
    {
        return 'CpuInfoPhysicalFinder';
    }

    /**
     * @internal
     *
     * @return positive-int|null
     */
    public static function countCpuCores(string $cpuInfo): ?int
    {
        $cores = [];

        // Each logical processor is described in its own block.
        foreach (explode("\n\n", $cpuInfo) as $block) {
            if (1 !== preg_match(self::PROCESSOR_LINE_REGEX, $block)) {
                continue;
            }

            if (
                1 !== preg_match(self::PHYSICAL_ID_LINE_REGEX, $block, $physicalId)
                || 1 !== preg_match(self::CORE_ID_LINE_REGEX, $block, $coreId)
            ) {
                return null;
            }

            $cores[$physicalId['id'].':'.$coreId['id']] = true;
        }

        $count = count($cores);

        return 0 === $count ? null : $count;
    }
}
