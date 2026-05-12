<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Telemetry;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Snapshot
{
    private HRTime $time;
    private MemoryUsage $memoryUsage;
    private MemoryUsage $peakMemoryUsage;
    private GarbageCollectorStatus $garbageCollectorStatus;
    private CpuTime $userCpuTime;
    private CpuTime $systemCpuTime;
    private CpuTime $totalCpuTime;

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function __construct(HRTime $time, MemoryUsage $memoryUsage, MemoryUsage $peakMemoryUsage, GarbageCollectorStatus $garbageCollectorStatus, CpuTime $userCpuTime, CpuTime $systemCpuTime, CpuTime $totalCpuTime)
    {
        $this->time                   = $time;
        $this->memoryUsage            = $memoryUsage;
        $this->peakMemoryUsage        = $peakMemoryUsage;
        $this->garbageCollectorStatus = $garbageCollectorStatus;
        $this->userCpuTime            = $userCpuTime;
        $this->systemCpuTime          = $systemCpuTime;
        $this->totalCpuTime           = $totalCpuTime;
    }

    public function time(): HRTime
    {
        return $this->time;
    }

    public function memoryUsage(): MemoryUsage
    {
        return $this->memoryUsage;
    }

    public function peakMemoryUsage(): MemoryUsage
    {
        return $this->peakMemoryUsage;
    }

    public function garbageCollectorStatus(): GarbageCollectorStatus
    {
        return $this->garbageCollectorStatus;
    }

    public function userCpuTime(): CpuTime
    {
        return $this->userCpuTime;
    }

    public function systemCpuTime(): CpuTime
    {
        return $this->systemCpuTime;
    }

    public function totalCpuTime(): CpuTime
    {
        return $this->totalCpuTime;
    }
}
