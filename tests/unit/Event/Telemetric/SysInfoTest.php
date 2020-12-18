<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Telemetric;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Event\Telemetric\SysInfo
 */
final class SysInfoTest extends TestCase
{
    public function testSnapshotReturnsSnapshot(): void
    {
        $time = new DateTimeImmutable('now');

        $clock = new class($time) implements Clock {
            private DateTimeImmutable $time;

            public function __construct(DateTimeImmutable $time)
            {
                $this->time = $time;
            }

            public function now(): DateTimeImmutable
            {
                return $this->time;
            }
        };

        $memoryUsage     = MemoryUsage::fromBytes(2000);
        $peakMemoryUsage = MemoryUsage::fromBytes(3000);

        $memoryMeter = new class($memoryUsage, $peakMemoryUsage) implements MemoryMeter {
            private MemoryUsage $memoryUsage;

            private MemoryUsage $peakMemoryUsage;

            public function __construct(MemoryUsage $memoryUsage, MemoryUsage $peakMemoryUsage)
            {
                $this->memoryUsage     = $memoryUsage;
                $this->peakMemoryUsage = $peakMemoryUsage;
            }

            public function usage(): MemoryUsage
            {
                return $this->memoryUsage;
            }

            public function peak(): MemoryUsage
            {
                return $this->peakMemoryUsage;
            }
        };

        $sysInfo = new SysInfo(
            $clock,
            $memoryMeter
        );

        $snapshot = $sysInfo->snapshot();

        $this->assertSame($time, $snapshot->time());
        $this->assertSame($memoryUsage, $snapshot->memoryUsage());
        $this->assertSame($peakMemoryUsage, $snapshot->peakMemoryUsage());
    }
}
