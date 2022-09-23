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
 */
final class System
{
    private readonly StopWatch $stopWatch;
    private readonly MemoryMeter $memoryMeter;

    public function __construct(StopWatch $stopWatch, MemoryMeter $memoryMeter)
    {
        $this->stopWatch   = $stopWatch;
        $this->memoryMeter = $memoryMeter;
    }

    public function snapshot(): Snapshot
    {
        return new Snapshot(
            $this->stopWatch->current(),
            $this->memoryMeter->memoryUsage(),
            $this->memoryMeter->peakMemoryUsage()
        );
    }
}
