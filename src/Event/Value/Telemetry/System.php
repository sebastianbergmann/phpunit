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
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class System
{
    private StopWatch $stopWatch;
    private MemoryMeter $memoryMeter;
    private GarbageCollectorStatusProvider $garbageCollectorStatusProvider;
    private CpuTimeMeter $cpuTimeMeter;

    /**
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function __construct(StopWatch $stopWatch, MemoryMeter $memoryMeter, GarbageCollectorStatusProvider $garbageCollectorStatusProvider, CpuTimeMeter $cpuTimeMeter)
    {
        $this->stopWatch                      = $stopWatch;
        $this->memoryMeter                    = $memoryMeter;
        $this->garbageCollectorStatusProvider = $garbageCollectorStatusProvider;
        $this->cpuTimeMeter                   = $cpuTimeMeter;
    }

    public function snapshot(): Snapshot
    {
        $userCpuTime   = $this->cpuTimeMeter->userCpuTime();
        $systemCpuTime = $this->cpuTimeMeter->systemCpuTime();

        return new Snapshot(
            $this->stopWatch->current(),
            $this->memoryMeter->memoryUsage(),
            $this->memoryMeter->peakMemoryUsage(),
            $this->garbageCollectorStatusProvider->status(),
            $userCpuTime,
            $systemCpuTime,
            $userCpuTime->add($systemCpuTime),
        );
    }
}
