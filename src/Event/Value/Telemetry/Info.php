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

use function sprintf;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Info
{
    private Snapshot $current;
    private Duration $durationSinceStart;
    private MemoryUsage $memorySinceStart;
    private Duration $durationSincePrevious;
    private MemoryUsage $memorySincePrevious;
    private CpuTime $userCpuTimeSinceStart;
    private CpuTime $systemCpuTimeSinceStart;
    private CpuTime $totalCpuTimeSinceStart;
    private CpuTime $userCpuTimeSincePrevious;
    private CpuTime $systemCpuTimeSincePrevious;
    private CpuTime $totalCpuTimeSincePrevious;

    /**
     * @var non-negative-int
     */
    private int $processId;

    /**
     * @param non-negative-int $processId
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function __construct(Snapshot $current, Duration $durationSinceStart, MemoryUsage $memorySinceStart, Duration $durationSincePrevious, MemoryUsage $memorySincePrevious, CpuTime $userCpuTimeSinceStart, CpuTime $systemCpuTimeSinceStart, CpuTime $totalCpuTimeSinceStart, CpuTime $userCpuTimeSincePrevious, CpuTime $systemCpuTimeSincePrevious, CpuTime $totalCpuTimeSincePrevious, int $processId)
    {
        $this->current                    = $current;
        $this->durationSinceStart         = $durationSinceStart;
        $this->memorySinceStart           = $memorySinceStart;
        $this->durationSincePrevious      = $durationSincePrevious;
        $this->memorySincePrevious        = $memorySincePrevious;
        $this->userCpuTimeSinceStart      = $userCpuTimeSinceStart;
        $this->systemCpuTimeSinceStart    = $systemCpuTimeSinceStart;
        $this->totalCpuTimeSinceStart     = $totalCpuTimeSinceStart;
        $this->userCpuTimeSincePrevious   = $userCpuTimeSincePrevious;
        $this->systemCpuTimeSincePrevious = $systemCpuTimeSincePrevious;
        $this->totalCpuTimeSincePrevious  = $totalCpuTimeSincePrevious;
        $this->processId                  = $processId;
    }

    public function time(): HRTime
    {
        return $this->current->time();
    }

    public function memoryUsage(): MemoryUsage
    {
        return $this->current->memoryUsage();
    }

    public function peakMemoryUsage(): MemoryUsage
    {
        return $this->current->peakMemoryUsage();
    }

    public function durationSinceStart(): Duration
    {
        return $this->durationSinceStart;
    }

    public function memoryUsageSinceStart(): MemoryUsage
    {
        return $this->memorySinceStart;
    }

    public function durationSincePrevious(): Duration
    {
        return $this->durationSincePrevious;
    }

    public function memoryUsageSincePrevious(): MemoryUsage
    {
        return $this->memorySincePrevious;
    }

    public function garbageCollectorStatus(): GarbageCollectorStatus
    {
        return $this->current->garbageCollectorStatus();
    }

    public function userCpuTime(): CpuTime
    {
        return $this->current->userCpuTime();
    }

    public function systemCpuTime(): CpuTime
    {
        return $this->current->systemCpuTime();
    }

    public function totalCpuTime(): CpuTime
    {
        return $this->current->totalCpuTime();
    }

    public function userCpuTimeSinceStart(): CpuTime
    {
        return $this->userCpuTimeSinceStart;
    }

    public function systemCpuTimeSinceStart(): CpuTime
    {
        return $this->systemCpuTimeSinceStart;
    }

    public function totalCpuTimeSinceStart(): CpuTime
    {
        return $this->totalCpuTimeSinceStart;
    }

    public function userCpuTimeSincePrevious(): CpuTime
    {
        return $this->userCpuTimeSincePrevious;
    }

    public function systemCpuTimeSincePrevious(): CpuTime
    {
        return $this->systemCpuTimeSincePrevious;
    }

    public function totalCpuTimeSincePrevious(): CpuTime
    {
        return $this->totalCpuTimeSincePrevious;
    }

    /**
     * The ID of the process in which the event was emitted, or 0 when it
     * could not be determined.
     *
     * An event is not always processed in the process that emitted it: the
     * events of a test that runs in process isolation, or in a worker process
     * of a parallel test run, are emitted in that process and replayed in the
     * main process afterwards. A subscriber that has to act in the process
     * that runs the test — and that is registered in both processes, or in
     * the main process only — compares this ID with getmypid() to tell the
     * events of its own process from the replayed ones.
     *
     * @return non-negative-int
     */
    public function processId(): int
    {
        return $this->processId;
    }

    public function asString(): string
    {
        return sprintf(
            '[%s / %s] [%d bytes]',
            $this->durationSinceStart()->asString(),
            $this->durationSincePrevious()->asString(),
            $this->peakMemoryUsage()->bytes(),
        );
    }
}
