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

namespace Fidry\CpuCoreCounter;

use Fidry\CpuCoreCounter\Finder\CpuCoreFinder;
use Fidry\CpuCoreCounter\Finder\EnvVariableFinder;
use Fidry\CpuCoreCounter\Finder\FinderRegistry;
use InvalidArgumentException;
use function function_exists;
use function implode;
use function max;
use function sprintf;
use function sys_getloadavg;
use const PHP_EOL;

final class CpuCoreCounter
{
    /**
     * @var list<CpuCoreFinder>
     */
    private $finders;

    /**
     * @var CpuCoreFinder
     */
    private $countLimitFinder;

    /**
     * @var positive-int|null
     */
    private $count;

    /**
     * @param list<CpuCoreFinder>|null $finders
     * @param CpuCoreFinder|null       $countLimitFinder Finds the count limit to use when none is given
     *                                                   to getAvailableForParallelisation().
     */
    public function __construct(
        ?array $finders = null,
        ?CpuCoreFinder $countLimitFinder = null
    ) {
        $this->finders = $finders ?? FinderRegistry::getDefaultLogicalFinders();
        $this->countLimitFinder = $countLimitFinder ?? FinderRegistry::getDefaultCountLimitFinder();
    }

    /**
     * @param positive-int|0    $reservedCpus      Number of CPUs to reserve. This is useful when you want
     *                                             to reserve some CPUs for other processes. If the main
     *                                             process is going to be busy still, you may want to set
     *                                             this value to 1.
     * @param non-zero-int|null $countLimit        The maximum number of CPUs to return. If not provided, it
     *                                             uses the limit found by the count limit finder: by
     *                                             default the CPU quota of the cgroup, e.g. with
     *                                             `docker run --cpus=2`, or KUBERNETES_CPU_LIMIT
     *                                             if there is none. If negative, the limit will be
     *                                             the total number of cores found minus the absolute value.
     *                                             For instance if the system has 10 cores and countLimit=-2,
     *                                             then the effective limit considered will be 8.
     * @param float|null        $loadLimit         Element of [0., 1.]. Percentage representing the
     *                                             amount of cores that should be used among the available
     *                                             resources. For instance, if set to 0.7, it will use 70%
     *                                             of the available cores, i.e. if the system has 11 cores,
     *                                             1 core is reserved and 5 are busy, it will use 70%
     *                                             of (11-1-5)=5 cores, so 3 cores (3.5 rounded down).
     *                                             Set this parameter to null to skip this check. Beware
     *                                             that 1 does not mean "no limit", but 100% of the
     *                                             _available_ resources, i.e. with the previous example,
     *                                             it will return 5 cores. The system load average
     *                                             tells how busy the system is (see $systemLoadAverage).
     * @param float|null        $systemLoadAverage The system load average. It is only used when
     *                                             $loadLimit is not null, to limit the available cores
     *                                             based on the _available_ resources. For instance, if
     *                                             there are 10 cores but 3 are busy, then only 7 cores
     *                                             will be considered for further calculation. If set to
     *                                             `null`, it will use `sys_getloadavg()` to check the
     *                                             load of the system in the past minute, or 0 if
     *                                             it is unavailable (e.g. on Windows). You can
     *                                             otherwise pass an arbitrary value. Should be a
     *                                             positive float. Inside a virtual machine, the
     *                                             load average excludes the host's load. Inside
     *                                             a container, it may be the host's load.
     *
     * @see https://php.net/manual/en/function.sys-getloadavg.php
     */
    public function getAvailableForParallelisation(
        int $reservedCpus = 0,
        ?int $countLimit = null,
        ?float $loadLimit = null,
        ?float $systemLoadAverage = 0.
    ): ParallelisationResult {
        self::checkCountLimit($countLimit);
        self::checkLoadLimit($loadLimit);
        self::checkSystemLoadAverage($systemLoadAverage);

        $totalCoreCount = $this->getCountWithFallback(1);
        $availableCores = max(1, $totalCoreCount - $reservedCpus);

        // Adjust available CPUs based on current load
        if (null !== $loadLimit) {
            $correctedSystemLoadAverage = null === $systemLoadAverage
                ? self::getSystemLoadAverage()
                : $systemLoadAverage;

            $availableCores = max(
                1,
                $loadLimit * ($availableCores - $correctedSystemLoadAverage)
            );
        }

        if (null === $countLimit) {
            $correctedCountLimit = $this->countLimitFinder->find();
        } else {
            $correctedCountLimit = $countLimit > 0
                ? $countLimit
                : max(1, $totalCoreCount + $countLimit);
        }

        if (null !== $correctedCountLimit && $availableCores > $correctedCountLimit) {
            $availableCores = $correctedCountLimit;
        }

        return new ParallelisationResult(
            $reservedCpus,
            $countLimit,
            $loadLimit,
            $systemLoadAverage,
            $correctedCountLimit,
            $correctedSystemLoadAverage ?? $systemLoadAverage,
            $totalCoreCount,
            (int) $availableCores
        );
    }

    /**
     * The count does not account for CPU quotas or limits, e.g. the CPU quota
     * of the cgroup set with `docker run --cpus=2` or KUBERNETES_CPU_LIMIT.
     * To know how many CPUs to use for parallel processes, use
     * getAvailableForParallelisation() instead.
     *
     * @throws NumberOfCpuCoreNotFound
     *
     * @return positive-int
     */
    public function getCount(): int
    {
        // Memoize result
        if (null === $this->count) {
            $this->count = $this->findCount();
        }

        return $this->count;
    }

    /**
     * @param positive-int $fallback
     *
     * @return positive-int
     */
    public function getCountWithFallback(int $fallback): int
    {
        try {
            return $this->getCount();
        } catch (NumberOfCpuCoreNotFound $exception) {
            return $fallback;
        }
    }

    /**
     * This method is mostly for debugging purposes.
     */
    public function trace(): string
    {
        $output = [];

        foreach ($this->finders as $finder) {
            $output[] = sprintf(
                'Executing the finder "%s":',
                $finder->toString()
            );
            $output[] = $finder->diagnose();

            $cores = $finder->find();

            if (null !== $cores) {
                $output[] = 'Result found: '.$cores;

                break;
            }

            $output[] = '–––';
        }

        return implode(PHP_EOL, $output);
    }

    /**
     * @throws NumberOfCpuCoreNotFound
     *
     * @return positive-int
     */
    private function findCount(): int
    {
        foreach ($this->finders as $finder) {
            $cores = $finder->find();

            if (null !== $cores) {
                return $cores;
            }
        }

        throw NumberOfCpuCoreNotFound::create();
    }

    /**
     * @throws NumberOfCpuCoreNotFound
     *
     * @return array{CpuCoreFinder, positive-int}
     */
    public function getFinderAndCores(): array
    {
        foreach ($this->finders as $finder) {
            $cores = $finder->find();

            if (null !== $cores) {
                return [$finder, $cores];
            }
        }

        throw NumberOfCpuCoreNotFound::create();
    }

    /**
     * @deprecated Since 1.4.0. The limit finder is injected in the constructor instead.
     *
     * @return positive-int|null
     */
    public static function getKubernetesLimit(): ?int
    {
        $finder = new EnvVariableFinder('KUBERNETES_CPU_LIMIT');

        return $finder->find();
    }

    private static function checkCountLimit(?int $countLimit): void
    {
        if (0 === $countLimit) {
            throw new InvalidArgumentException(
                'The count limit must be a non zero integer. Got "0".'
            );
        }
    }

    private static function checkLoadLimit(?float $loadLimit): void
    {
        if (null === $loadLimit) {
            return;
        }

        if ($loadLimit < 0. || $loadLimit > 1.) {
            throw new InvalidArgumentException(
                sprintf(
                    'The load limit must be in the range [0., 1.], got "%s".',
                    $loadLimit
                )
            );
        }
    }

    private static function checkSystemLoadAverage(?float $systemLoadAverage): void
    {
        if (null !== $systemLoadAverage && $systemLoadAverage < 0.) {
            throw new InvalidArgumentException(
                sprintf(
                    'The system load average must be a positive float, got "%s".',
                    $systemLoadAverage
                )
            );
        }
    }

    /**
     * sys_getloadavg() is not available on Windows, and it can be disabled.
     */
    private static function getSystemLoadAverage(): float
    {
        if (!function_exists('sys_getloadavg')) {
            return 0.;
        }

        return sys_getloadavg()[0] ?? 0.;
    }
}
