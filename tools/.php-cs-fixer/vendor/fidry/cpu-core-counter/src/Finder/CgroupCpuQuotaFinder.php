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
use function array_slice;
use function count;
use function explode;
use function floor;
use function implode;
use function in_array;
use function max;
use function min;
use function preg_match;
use function sprintf;
use function trim;
use const PHP_EOL;

/**
 * Find the number of CPU cores allowed by the CPU quota of the cgroup the
 * process runs in, e.g. with `docker run --cpus=2` or a Kubernetes
 * `limits.cpu`. The quota leaves every CPU of the host visible, so the other
 * finders report the host's count.
 *
 * This is a limit, not a count: see FinderRegistry::getDefaultCountLimitFinder().
 *
 * Supports cgroup v1 and v2. A quota set on a parent cgroup applies too, so
 * the lowest quota found from the root down to the process' cgroup wins. A
 * fractional quota is rounded down.
 *
 * Inside a virtual machine, only the VM's own cgroups are visible, so a CPU
 * limit set by the host is not detected. Likewise, inside a container, a quota
 * set outside the container's cgroup namespace is not detected, e.g. with
 * Proxmox VE LXC containers.
 *
 * @author Anders Jenbo <anders@jenbo.dk> (@AJenbo)
 * @author Ondřej Mirtes <ondrej@mirtes.cz> (@ondrejmirtes)
 *
 * @see https://docs.kernel.org/admin-guide/cgroup-v2.html#cpu-interface-files
 * @see https://docs.kernel.org/scheduler/sched-bwc.html
 */
final class CgroupCpuQuotaFinder implements CpuCoreFinder
{
    private const CGROUP_PATH = '/proc/self/cgroup';

    private const V1_CONTROLLER_DIRECTORIES = [
        'cpu',
        'cpu,cpuacct',
        'cpuacct,cpu',
    ];

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
        $cgroup = $this->fileReader->read(self::CGROUP_PATH);

        if (null === $cgroup) {
            return sprintf(
                'Could not read the file "%s".',
                self::CGROUP_PATH
            );
        }

        $quotas = $this->findQuotas($cgroup);

        $lines = [
            sprintf(
                'Found the file "%s" with the content:',
                self::CGROUP_PATH
            ),
            trim($cgroup),
        ];

        if (0 === count($quotas)) {
            $lines[] = 'No CPU quota found.';
        } else {
            $lines[] = 'Found the CPU quotas:';

            foreach ($quotas as $path => $quota) {
                $lines[] = sprintf('- %s: %s', $path, $quota);
            }
        }

        $lines[] = sprintf(
            'Will return "%s".',
            self::toCores($quotas) ?? 'null'
        );

        return implode(PHP_EOL, $lines);
    }

    public function find(): ?int
    {
        $cgroup = $this->fileReader->read(self::CGROUP_PATH);

        return null === $cgroup
            ? null
            : self::toCores($this->findQuotas($cgroup));
    }

    public function toString(): string
    {
        return 'CgroupCpuQuotaFinder';
    }

    /**
     * @return array<string, float> Number of CPUs keyed by the path of the file it was read from.
     */
    private function findQuotas(string $cgroup): array
    {
        $quotas = [];

        foreach (explode("\n", $cgroup) as $line) {
            // hierarchy-ID:controller-list:cgroup-path
            $parts = explode(':', $line, 3);

            if (3 !== count($parts)) {
                continue;
            }

            [$hierarchyId, $controllers, $cgroupPath] = $parts;

            if ('0' === $hierarchyId) {
                $quotas += $this->findV2Quotas($cgroupPath);
            } elseif (in_array('cpu', explode(',', $controllers), true)) {
                $quotas += $this->findV1Quotas($cgroupPath);
            }
        }

        return $quotas;
    }

    /**
     * @return array<string, float>
     */
    private function findV2Quotas(string $cgroupPath): array
    {
        $quotas = [];

        foreach (self::getSelfAndAncestors($cgroupPath) as $path) {
            $file = '/sys/fs/cgroup'.$path.'/cpu.max';
            $cpuMax = $this->fileReader->read($file);

            // An unlimited cgroup contains "max <period>".
            if (null !== $cpuMax
                && 1 === preg_match('/(\d+) (\d+)/', $cpuMax, $matches)
                && $matches[2] > 0
            ) {
                $quotas[$file] = $matches[1] / $matches[2];
            }
        }

        return $quotas;
    }

    /**
     * @return array<string, float>
     */
    private function findV1Quotas(string $cgroupPath): array
    {
        $quotas = [];

        foreach (self::getSelfAndAncestors($cgroupPath) as $path) {
            foreach (self::V1_CONTROLLER_DIRECTORIES as $controllerDirectory) {
                $directory = '/sys/fs/cgroup/'.$controllerDirectory.$path;
                $file = $directory.'/cpu.cfs_quota_us';

                // An unlimited cgroup has the quota "-1".
                $quota = $this->readPositiveInt($file);
                $period = $this->readPositiveInt($directory.'/cpu.cfs_period_us');

                if (null !== $quota && null !== $period) {
                    $quotas[$file] = $quota / $period;
                }
            }
        }

        return $quotas;
    }

    /**
     * Starts at the root: inside a container, the cgroup filesystem is often
     * mounted at the container's cgroup, while /proc/self/cgroup still shows
     * the path on the host.
     *
     * @return list<string> E.g. ["", "/a", "/a/b"] for "/a/b".
     */
    private static function getSelfAndAncestors(string $cgroupPath): array
    {
        $segments = [];

        foreach (explode('/', $cgroupPath) as $segment) {
            if ('' !== $segment) {
                $segments[] = $segment;
            }
        }

        $paths = [''];

        for ($i = 1; $i <= count($segments); ++$i) {
            $paths[] = '/'.implode('/', array_slice($segments, 0, $i));
        }

        return $paths;
    }

    /**
     * @param array<string, float> $quotas
     *
     * @return positive-int|null
     */
    private static function toCores(array $quotas): ?int
    {
        return 0 === count($quotas)
            ? null
            : max(1, (int) floor(min($quotas)));
    }

    private function readPositiveInt(string $path): ?int
    {
        $value = (int) $this->fileReader->read($path);

        return $value > 0 ? $value : null;
    }
}
