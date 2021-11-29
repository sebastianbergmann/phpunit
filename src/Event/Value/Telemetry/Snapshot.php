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
 * @psalm-immutable
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class Snapshot
{
    private HRTime $time;
    private MemoryUsage $memoryUsage;
    private MemoryUsage $peakMemoryUsage;

    public function __construct(HRTime $time, MemoryUsage $memoryUsage, MemoryUsage $peakMemoryUsage)
    {
        $this->time            = $time;
        $this->memoryUsage     = $memoryUsage;
        $this->peakMemoryUsage = $peakMemoryUsage;
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
}
