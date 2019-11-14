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

use DateTimeImmutable;

final class Snapshot
{
    private DateTimeImmutable $time;

    private MemoryUsage $memoryUsage;

    private MemoryUsage $peakMemoryUsage;

    public function __construct(DateTimeImmutable $time, MemoryUsage $memoryUsage, MemoryUsage $peakMemoryUsage)
    {
        $this->time            = $time;
        $this->memoryUsage     = $memoryUsage;
        $this->peakMemoryUsage = $peakMemoryUsage;
    }

    public function time(): DateTimeImmutable
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
