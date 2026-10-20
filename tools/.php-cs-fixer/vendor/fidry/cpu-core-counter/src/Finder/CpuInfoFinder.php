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
use function preg_match_all;
use function sprintf;
use const PHP_EOL;

/**
 * Find the number of CPU cores looking up at the cpuinfo file which is available
 * on Linux systems and Windows systems with a Linux sub-system.
 *
 * @see https://github.com/paratestphp/paratest/blob/c163539818fd96308ca8dc60f46088461e366ed4/src/Runners/PHPUnit/Options.php#L903-L909
 * @see https://unix.stackexchange.com/questions/146051/number-of-processors-in-proc-cpuinfo
 */
final class CpuInfoFinder implements CpuCoreFinder
{
    private const CPU_INFO_PATH = '/proc/cpuinfo';

    // Matches "processor : 0" and the s390x form "processor 0: ...".
    private const PROCESSOR_LINE_REGEX = '/^processor\s*\d*\s*:/m';

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
        return 'CpuInfoFinder';
    }

    /**
     * @internal
     *
     * @return positive-int|null
     */
    public static function countCpuCores(string $cpuInfo): ?int
    {
        $processorCount = preg_match_all(self::PROCESSOR_LINE_REGEX, $cpuInfo);

        return false !== $processorCount && $processorCount > 0 ? $processorCount : null;
    }
}
