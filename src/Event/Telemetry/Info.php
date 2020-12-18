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

use DateInterval;
use DateTimeImmutable;

final class Info
{
    private Snapshot $current;

    private DateInterval $timeSinceStart;

    private MemoryUsage $memorySinceStart;

    private DateInterval $timeSincePrevious;

    private MemoryUsage $memorySincePrevious;

    public function __construct(
        Snapshot $current,
        DateInterval $timeSinceStart,
        MemoryUsage $memorySinceStart,
        DateInterval $timeSincePrevious,
        MemoryUsage $memorySincePrevious
    ) {
        $this->current             = $current;
        $this->timeSinceStart      = $timeSinceStart;
        $this->memorySinceStart    = $memorySinceStart;
        $this->timeSincePrevious   = $timeSincePrevious;
        $this->memorySincePrevious = $memorySincePrevious;
    }

    public function time(): DateTimeImmutable
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

    public function timeSinceStart(): DateInterval
    {
        return $this->timeSinceStart;
    }

    public function memoryUsageSinceStart(): MemoryUsage
    {
        return $this->memorySinceStart;
    }

    public function timeSincePrevious(): DateInterval
    {
        return $this->timeSincePrevious;
    }

    public function memoryUsageSincePrevious(): MemoryUsage
    {
        return $this->memorySincePrevious;
    }
}
