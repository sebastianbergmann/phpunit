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
use function implode;
use function preg_match;
use function sprintf;
use function trim;
use const PHP_EOL;

/**
 * Find the number of CPU cores in the CPU affinity of the process, from the
 * "Cpus_allowed_list" line of /proc/self/status. This file is available on
 * Linux systems.
 *
 * The CPU affinity is the list of CPUs the kernel allows the process to run
 * on. By default, it contains every CPU. It is smaller when the process is
 * pinned to some CPUs, e.g. with `docker run --cpuset-cpus`, the Kubernetes
 * static CPU manager, `taskset` or systemd `AllowedCPUs=`. Most other finders
 * ignore it and count every CPU of the host. This is the count `nproc`
 * reports, but it does not need `proc_open`.
 *
 * The kernel fills the CPU affinity from the possible CPUs, which include the
 * offline ones. A hypervisor that leaves room to hot-add CPUs, e.g. Hyper-V,
 * may make it far larger than the CPUs the VM has. Like sched_getaffinity(2),
 * the finder therefore only counts the allowed CPUs that are also listed in
 * /sys/devices/system/cpu/online.
 *
 * @see https://man7.org/linux/man-pages/man2/sched_setaffinity.2.html
 * @see https://docs.kernel.org/admin-guide/cputopology.html
 * @see https://docs.kernel.org/filesystems/proc.html
 * @see https://man7.org/linux/man-pages/man7/cpuset.7.html (FORMATS)
 */
final class CpuAffinityFinder implements CpuCoreFinder
{
    private const STATUS_PATH = '/proc/self/status';

    private const ONLINE_PATH = '/sys/devices/system/cpu/online';

    // E.g. "Cpus_allowed_list:	0-1,4" for the CPUs 0, 1 and 4.
    private const CPUS_ALLOWED_LIST_REGEX = '/^Cpus_allowed_list:\s*(\S+)\s*$/m';

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
        $lines = [];

        foreach ([self::STATUS_PATH, self::ONLINE_PATH] as $path) {
            $content = $this->fileReader->read($path);

            if (null === $content) {
                $lines[] = sprintf('Could not read the file "%s".', $path);
            } else {
                $lines[] = sprintf('Found the file "%s" with the content:', $path);
                $lines[] = trim($content);
            }
        }

        $lines[] = sprintf(
            'Will return "%s".',
            $this->find() ?? 'null'
        );

        return implode(PHP_EOL, $lines);
    }

    /**
     * @return positive-int|null
     */
    public function find(): ?int
    {
        $status = $this->fileReader->read(self::STATUS_PATH);

        if (null === $status) {
            return null;
        }

        $online = trim((string) $this->fileReader->read(self::ONLINE_PATH));

        if (
            '' === $online
            || 1 !== preg_match(self::CPUS_ALLOWED_LIST_REGEX, $status, $matches)
        ) {
            return null;
        }

        $cpuAllowedList = $matches[1];

        return CpuList::countIntersection($cpuAllowedList, $online);
    }

    public function toString(): string
    {
        return 'CpuAffinityFinder';
    }
}
